<?php

namespace App\Http\Controllers\Admin;

use App\Allergen;
use App\Company;
use App\Http\Controllers\Concerns\BuildsCompanyStudioPayload;
use App\Http\Controllers\Controller;
use App\Product;
use App\Section;
use App\Services\AllergenCatalogService;
use App\Services\MenuService;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class SectionsController extends Controller
{
    use BuildsCompanyStudioPayload;

    public function index(MenuService $menuService, UserPlanService $plans)
    {
        $company = $this->selectedCompany();

        if (!$company) {
            return redirect()->route('admin.companies.index')
                ->with('flash', 'Crea un negocio para gestionar tu carta.');
        }

        $sections = $menuService->sectionsForCompany($company);
        app(AllergenCatalogService::class)->sync();
        $allergens = Allergen::orderBy('name')->get();

        $studio = $this->studioPayload($company, $plans);

        return view('admin.sections.index', array_merge(
            compact('sections', 'allergens', 'company'),
            $studio
        ));
    }

    public function create()
    {
        return view('admin.sections.create');
    }

    public function store(Request $request)
    {
        $company = $this->selectedCompanyOrFail();

        $this->validate($request, [
            'name' => 'required',
        ]);

        $sectionOrder = (int) Section::where('company_id', $company->id)->max('order') + 1;

        Section::create([
            'name' => $request->get('name'),
            'enabled' => $request->get('section_enabled') != null,
            'order' => $sectionOrder,
            'company_id' => $company->id,
        ]);

        return redirect()->route('admin.sections.index')->with('flash', 'Sección creada correctamente');
    }

    public function update(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'sectionid' => 'required|integer',
        ]);

        $section = Section::with('company')->findOrFail($request->get('sectionid'));
        $this->authorize('update', $section);

        $section->name = $request->get('name');
        $section->enabled = $request->get('section_enabled') != null;
        $section->save();

        return redirect()->route('admin.sections.index')->with('flash', 'Sección actualizada correctamente');
    }

    public function delete(Request $request)
    {
        $request->validate([
            'sectionid' => 'required|integer',
        ]);

        $section = Section::with('company')->findOrFail($request->get('sectionid'));
        $this->authorize('delete', $section);
        $section->delete();

        return redirect()->route('admin.sections.index');
    }

    public function order_section(Request $request)
    {
        $company = $this->selectedCompanyOrFail();

        $request->validate([
            'new_section_order' => 'required|string',
        ]);

        $sectionIds = explode(',', $request->get('new_section_order'));

        foreach ($sectionIds as $key => $sectionId) {
            $section = Section::with('company')->find($sectionId);

            if (!$section || $section->company_id !== $company->id) {
                return response()->json(['success' => false]);
            }

            $section->order = $key;
            $section->save();
        }

        return response()->json(['success' => true]);
    }

    public function update_menu_type(Request $request, UserPlanService $plans)
    {
        $request->validate([
            'menu_type' => 'required|in:menu_type_custom,menu_type_pdf',
            'company_id' => 'required|integer',
        ]);

        $company = Company::where('user_id', auth()->id())
            ->where('id', $request->get('company_id'))
            ->firstOrFail();

        if ($request->get('menu_type') === 'menu_type_pdf') {
            $plans->assertCanUsePdfMenu($request->user());
        }

        $company->menu_type = $request->get('menu_type') === 'menu_type_custom' ? 1 : 2;
        $company->save();

        return response()->json(['success' => true]);
    }

    public function update_pdf_menu(Request $request, UserPlanService $plans)
    {
        $request->validate([
            'company_id' => 'required|integer',
            'pdf_menu_file' => [
                'required',
                'file',
                'max:10240',
                function ($attribute, $value, $fail) {
                    if (! $value instanceof \Illuminate\Http\UploadedFile) {
                        $fail('Selecciona un archivo PDF.');

                        return;
                    }

                    if (! $value->isValid()) {
                        $fail('No se pudo subir el archivo. Comprueba el tamaño (máx. 10 MB) e inténtalo de nuevo.');

                        return;
                    }

                    $ext = strtolower((string) $value->getClientOriginalExtension());
                    $mime = (string) $value->getMimeType();
                    $allowedMimes = ['application/pdf', 'application/x-pdf', 'application/octet-stream'];

                    if ($ext !== 'pdf' && ! in_array($mime, $allowedMimes, true)) {
                        $fail('El archivo debe ser un PDF.');
                    }
                },
            ],
        ], [
            'pdf_menu_file.required' => 'Selecciona un PDF antes de guardar.',
            'pdf_menu_file.max' => 'El PDF no puede superar 10 MB.',
        ]);

        $company = Company::where('user_id', auth()->id())
            ->where('id', $request->get('company_id'))
            ->firstOrFail();

        if (! $plans->canUsePdfMenu($request->user())) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'pdf_menu_file' => 'La carta en PDF está disponible desde el plan Pro (' . $plans->proPriceLabel() . ').',
            ]);
        }

        $file = $request->file('pdf_menu_file');
        $oldRelative = $company->menu_type_2_pdf;
        $stored = $file->store('pdf');

        $company->menu_type_2_pdf = $stored;
        $company->menu_type = 2;
        $company->save();

        if ($oldRelative && $oldRelative !== $stored) {
            $oldPath = public_path('img/' . ltrim($oldRelative, '/'));
            if (is_file($oldPath)) {
                @unlink($oldPath);
            }
        }

        return redirect()
            ->to(route('admin.sections.index') . '#tab-personalizacion')
            ->with('flash', 'Carta PDF actualizada correctamente');
    }

    protected function selectedCompany()
    {
        $companyId = Cookie::get('selected_company');

        if (!$companyId) {
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
