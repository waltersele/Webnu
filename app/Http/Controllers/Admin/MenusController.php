<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Menu;
use App\MenuItem;
use App\MenuSection;
use App\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MenusController extends Controller
{
    public function index()
    {
        $company = $this->selectedCompanyOrFail();

        $menus = $company->menus()
            ->withCount('items')
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        return view('admin.menus.index', [
            'company' => $company,
            'menus' => $menus,
        ]);
    }

    public function store(Request $request)
    {
        $company = $this->selectedCompanyOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:120',
        ]);

        $position = (int) Menu::where('company_id', $company->id)->max('position');

        $menu = Menu::create([
            'company_id' => $company->id,
            'name' => $data['name'],
            'slug' => $this->uniqueMenuSlug($company->id, $data['name']),
            'position' => $position + 1,
            'enabled' => true,
        ]);

        MenuSection::create([
            'menu_id' => $menu->id,
            'name' => '',
            'position' => 0,
        ]);

        return redirect()->route('admin.menus.edit', $menu)
            ->with('flash', 'Menú creado. Configura precio e ítems.');
    }

    public function edit(Menu $menu)
    {
        $this->authorizeMenu($menu);

        $menu->load([
            'sections' => function ($q) {
                $q->orderBy('position');
            },
            'sections.items' => function ($q) {
                $q->orderBy('position');
            },
            'sections.items.product',
        ]);

        $company = $menu->company;
        $sections = Section::where('company_id', $company->id)
            ->with(['products' => function ($q) {
                $q->where('enabled', true)->orderBy('order');
            }])
            ->orderBy('order')
            ->get();

        return view('admin.menus.edit', [
            'menu' => $menu,
            'company' => $company,
            'sections' => $sections,
        ]);
    }

    public function update(Menu $menu, Request $request)
    {
        $this->authorizeMenu($menu);

        $data = $request->validate([
            'name' => 'required|string|max:120',
            'subtitle' => 'nullable|string|max:140',
            'price' => 'nullable|numeric|min:0|max:99999.99',
            'includes' => 'nullable|string|max:200',
            'notes' => 'nullable|string|max:2000',
            'enabled' => 'nullable',
            'sections' => 'nullable|array',
            'sections.*.name' => 'required|string|max:80',
            'sections.*.position' => 'nullable|integer|min:0',
            'items' => 'nullable|array',
            'items.*.section_client_id' => 'required|string|max:32',
            'items.*.product_id' => 'nullable|integer|exists:products,id',
            'items.*.label' => 'nullable|string|max:200',
            'items.*.image' => 'nullable|string|max:500',
            'items.*.position' => 'nullable|integer|min:0',
        ]);

        $newSlug = $menu->slug;
        if (trim((string) $menu->name) !== trim((string) $data['name'])) {
            $newSlug = $this->uniqueMenuSlug($menu->company_id, $data['name'], $menu->id);
        } elseif (empty($menu->slug)) {
            $newSlug = $this->uniqueMenuSlug($menu->company_id, $data['name'], $menu->id);
        }

        $menu->update([
            'name' => $data['name'],
            'subtitle' => $data['subtitle'] ?? null,
            'price' => $data['price'] ?? null,
            'includes' => $data['includes'] ?? null,
            'notes' => $data['notes'] ?? null,
            'enabled' => $request->boolean('enabled'),
            'slug' => $newSlug,
        ]);

        $this->syncSectionsAndItems(
            $menu,
            $data['sections'] ?? [],
            $data['items'] ?? []
        );

        return redirect()->route('admin.menus.edit', $menu)
            ->with('flash', 'Menú guardado correctamente.');
    }

    public function destroy(Menu $menu)
    {
        $this->authorizeMenu($menu);

        $menu->delete();

        return redirect()->route('admin.menus.index')
            ->with('flash', 'Menú eliminado.');
    }

    public function reorder(Request $request)
    {
        $company = $this->selectedCompanyOrFail();

        $data = $request->validate([
            'order' => 'required|array',
            'order.*' => 'integer',
        ]);

        foreach ($data['order'] as $position => $menuId) {
            Menu::where('id', $menuId)
                ->where('company_id', $company->id)
                ->update(['position' => $position + 1]);
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Activa/desactiva el modo "combinar todos los menús en la misma carta"
     * para la company seleccionada.
     */
    public function updateCombine(Request $request)
    {
        $company = $this->selectedCompanyOrFail();

        $combine = $request->boolean('combine_menus');
        $company->forceFill(['combine_menus' => $combine])->save();

        $msg = $combine
            ? 'Modo combinado activado: el cliente verá todos los menús en una sola carta con pestañas.'
            : 'Modo combinado desactivado: cada menú se accede individualmente.';

        return redirect()->route('admin.menus.index')->with('flash', $msg);
    }

    public function uploadItemImage(Menu $menu, Request $request)
    {
        $this->authorizeMenu($menu);

        $request->validate([
            'image' => 'required|image|max:6144',
        ]);

        $path = $request->file('image')->store('menu-items/' . $menu->id);

        return response()->json([
            'success' => true,
            'image_url' => asset('img/' . $path),
            'path' => $path,
        ]);
    }

    /**
     * Sync sections (create/update/delete) y luego recrea todos los items
     * mapeando section_client_id (numeric existente o 'new-*') a IDs reales.
     *
     * @param array<int|string, array<string, mixed>> $sectionsInput
     * @param array<int, array<string, mixed>> $itemsInput
     */
    protected function syncSectionsAndItems(Menu $menu, array $sectionsInput, array $itemsInput): void
    {
        $existingSections = $menu->sections()->get()->keyBy('id');
        $clientToReal = [];
        $keptIds = [];
        $position = 0;

        foreach ($sectionsInput as $clientId => $payload) {
            $clientIdStr = (string) $clientId;
            $name = trim((string) ($payload['name'] ?? ''));
            if ($name === '') {
                continue;
            }
            $pos = isset($payload['position']) ? (int) $payload['position'] : $position;

            if (ctype_digit($clientIdStr) && $existingSections->has((int) $clientIdStr)) {
                $section = $existingSections->get((int) $clientIdStr);
                $section->update([
                    'name' => $name,
                    'position' => $pos,
                ]);
                $clientToReal[$clientIdStr] = $section->id;
                $keptIds[] = $section->id;
            } else {
                $section = MenuSection::create([
                    'menu_id' => $menu->id,
                    'name' => $name,
                    'position' => $pos,
                ]);
                $clientToReal[$clientIdStr] = $section->id;
                $keptIds[] = $section->id;
            }

            $position++;
        }

        $existingSections->keys()
            ->diff($keptIds)
            ->each(function ($id) {
                MenuSection::where('id', $id)->delete();
            });

        $menu->items()->delete();

        foreach ($itemsInput as $idx => $payload) {
            $clientId = (string) ($payload['section_client_id'] ?? '');
            $sectionId = $clientToReal[$clientId] ?? null;
            if (! $sectionId) {
                continue;
            }

            $productId = $payload['product_id'] ?? null;
            $label = isset($payload['label']) ? trim((string) $payload['label']) : null;
            $image = isset($payload['image']) ? trim((string) $payload['image']) : null;

            if (! $productId && ! $label && ! $image) {
                continue;
            }

            MenuItem::create([
                'menu_id' => $menu->id,
                'menu_section_id' => $sectionId,
                'product_id' => $productId ?: null,
                'label' => $label ?: null,
                'image' => $image ?: null,
                'position' => (int) ($payload['position'] ?? $idx),
            ]);
        }
    }

    /**
     * Genera un slug único para un menú dentro de una company.
     */
    protected function uniqueMenuSlug(int $companyId, string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'menu-' . ($ignoreId ?: Str::random(6));
        }
        $base = substr($base, 0, 110);

        $candidate = $base;
        $i = 2;
        while (Menu::where('company_id', $companyId)
            ->where('slug', $candidate)
            ->when($ignoreId, function ($q) use ($ignoreId) { $q->where('id', '!=', $ignoreId); })
            ->exists()
        ) {
            $candidate = $base . '-' . $i;
            $i++;
            if ($i > 200) {
                $candidate = $base . '-' . Str::random(4);
                break;
            }
        }

        return $candidate;
    }

    protected function authorizeMenu(Menu $menu): void
    {
        $userId = (int) auth()->id();
        if ((int) $menu->company->user_id !== $userId) {
            abort(403);
        }
    }

    protected function selectedCompany(): ?Company
    {
        $companyId = Cookie::get('selected_company');

        if (! $companyId) {
            $companyId = optional(auth()->user())->companies()->orderBy('updated_at', 'desc')->value('id');
        }

        if (! $companyId) {
            return null;
        }

        return Company::where('user_id', auth()->id())
            ->where('id', $companyId)
            ->first();
    }

    protected function selectedCompanyOrFail(): Company
    {
        return $this->selectedCompany() ?? abort(403);
    }
}
