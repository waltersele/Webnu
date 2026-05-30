<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Concerns\BuildsCompanyStudioPayload;
use App\Http\Controllers\Controller;
use App\Product;
use App\Services\CompanySlugService;
use App\Services\CompanyThemeService;
use App\Services\PublicPathRegistry;
use App\Services\PublicUrlRedirectService;
use App\Services\LogoColorAnalyzer;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;

class CompaniesController extends Controller
{
    use BuildsCompanyStudioPayload;

    public function index()
    {
        $user = auth()->user();
        $query = Company::where('user_id', $user->id);

        if ($user->isSalesRep() && ! $user->isSuperAdmin()) {
            $query->where(function ($q) {
                $q->whereNull('sales_rep_user_id')
                    ->orWhereNotNull('sales_converted_at');
            });
        }

        $companies = $query->withCount('sections')->latest('updated_at')->get();

        $plans = app(UserPlanService::class);
        $maxCompanies = $plans->maxCompanies($user);
        $canCreateCompany = $plans->canCreateCompany($user);

        return view('admin.companies.index', compact('companies', 'maxCompanies', 'canCreateCompany'));
    }

    public function edit(UserPlanService $plans, Company $company)
    {
        $this->authorize('view', $company);

        $payload = $this->studioPayload($company, $plans);

        return view('admin.companies.edit', array_merge(['company' => $company], $payload));
    }

    public function store(Request $request, UserPlanService $plans, CompanySlugService $slugs)
    {
        $plans->assertCanCreateCompany($request->user());

        $this->validate($request, [
            'name' => 'required',
        ]);

        $userId = auth()->id();
        $ownerSlug = optional($request->user())->resolveSlug();

        $slug = $slugs->generateFromName(
            $request->get('name'),
            $request->get('city'),
            null,
            $ownerSlug
        );
        $defaultTemplate = 'lumiere';
        if (! $plans->canUseTemplate($request->user(), $defaultTemplate)) {
            $freeKeys = $plans->freeTemplateKeys();
            $defaultTemplate = $freeKeys[0] ?? 'basic';
        }

        $company = Company::create([
            'name' => $request->get('name'),
            'slug' => $slug,
            'template' => $defaultTemplate,
            'menu_type' => 1,
            'enabled' => true,
            'reservation' => false,
            'user_id' => $userId,
        ]);

        Cookie::queue(Cookie::forever('selected_company', $company->id));
        View::share('selected_company', $company->id);
        View::share('available_companies', Company::where('user_id', $userId)->orderBy('name')->get());

        return redirect()->route('admin.companies.edit', $company);
    }

    public function update(Request $request, CompanySlugService $slugs, CompanyThemeService $themes, UserPlanService $plans, Company $company)
    {
        $this->authorize('update', $company);

        $step = $request->get('studio_step', 'identity');

        if ($step === 'design') {
            $newTemplate = $request->get('template', $company->template ?: 'basic');
            if ($newTemplate !== ($company->template ?: 'basic')) {
                $plans->assertCanUseTemplate($request->user(), $newTemplate);
            }

            $company->fill([
                'template' => $newTemplate,
                'theme_settings' => $themes->normalizeFromRequest($request),
            ]);
            $company->save();

            return redirect()
                ->to(route('admin.sections.index') . '#tab-personalizacion')
                ->with('flash', 'Diseño actualizado correctamente');
        }

        if ($step === 'favorites') {
            $company->menu_favorites_enabled = $request->boolean('menu_favorites_enabled');
            $company->save();

            return redirect()
                ->to(route('admin.sections.index') . '#tab-personalizacion')
                ->with('flash', 'Lista de favoritos actualizada.');
        }

        $this->validate($request, [
            'name' => 'required',
            'slug' => 'nullable|string|max:64',
        ]);

        $newTemplate = $request->get('template', $company->template ?: 'basic');
        if ($newTemplate !== ($company->template ?: 'basic')) {
            $plans->assertCanUseTemplate($request->user(), $newTemplate);
        }

        if ($request->filled('slug')) {
            if ($company->isPublicSlugLocked()) {
                throw ValidationException::withMessages([
                    'slug' => ['La URL está bloqueada tras publicar. Contacta con soporte si necesitas cambiarla.'],
                ]);
            }

            $customSlug = $slugs->normalize($request->get('slug'));
            $slugError = $slugs->validateCustomSlug($customSlug, $company->id);
            if ($slugError) {
                throw ValidationException::withMessages(['slug' => [$slugError]]);
            }

            $previousPath = $company->publicPath();
            $company->slug = $customSlug;

            $pathError = app(PublicPathRegistry::class)->validateCompanySlug($customSlug, $company);
            if ($pathError) {
                throw ValidationException::withMessages(['slug' => [$pathError]]);
            }

            $newPath = $company->publicPath();
            if ($previousPath !== $newPath) {
                app(PublicUrlRedirectService::class)->record($previousPath, $newPath, $company->id);
            }
        }

        $company->fill([
            'name' => $request->get('name'),
            'chef_name' => $request->get('chef_name'),
            'address' => $request->get('address'),
            'postal_code' => $request->get('postal_code'),
            'city' => $request->get('city'),
            'province' => $request->get('province'),
            'country' => $request->get('country'),
            'phone' => $request->get('phone'),
            'mobile_phone' => $request->get('mobile_phone'),
            'email' => $request->get('email'),
            'web' => $request->get('web'),
            'reservation' => $request->get('reservation') != null,
            'whatsapp' => $request->get('whatsapp'),
            'facebook' => $request->get('facebook'),
            'instagram' => $request->get('instagram'),
            'comments' => $request->get('comments'),
            'schedule' => $request->get('schedule'),
            'suggest_translation_upgrade' => $request->boolean('suggest_translation_upgrade'),
            'template' => $newTemplate,
            'theme_settings' => $themes->normalizeFromRequest($request),
            'enabled' => $request->get('enabled') != null,
        ]);
        $company->save();

        if (!in_array($step, ['identity', 'contact', 'publish'], true)) {
            $step = 'identity';
        }

        return redirect()
            ->route('admin.companies.edit', ['company' => $company, 'step' => $step])
            ->with('flash', 'Negocio actualizado correctamente');
    }

    public function delete(Request $request)
    {
        $request->validate([
            'companyid' => 'required|integer',
        ]);

        $company = Company::where('user_id', auth()->id())
            ->where('id', $request->get('companyid'))
            ->firstOrFail();

        $this->authorize('delete', $company);

        if ($company->logo) {
            Storage::delete($company->logo);
        }

        $deletedCompanyId = $company->id;
        $company->delete();

        if ((int) Cookie::get('selected_company') === (int) $deletedCompanyId) {
            $nextCompany = Company::where('user_id', auth()->id())->orderBy('name')->first();

            if ($nextCompany) {
                Cookie::queue(Cookie::forever('selected_company', $nextCompany->id));
            } else {
                Cookie::queue(Cookie::forget('selected_company'));
            }
        }

        return redirect()->route('admin.companies.index')->with('flash', 'Negocio eliminado correctamente');
    }

    public function storelogo(Company $company, LogoColorAnalyzer $logoColorAnalyzer)
    {
        $this->authorize('update', $company);

        $file = $this->validatedBrandImage('logo');

        $old = $company->logo;
        $company->logo = $file->store('negocios');

        $absolutePath = public_path('img/' . $company->logo);
        $analysis = $logoColorAnalyzer->analyze($absolutePath);
        $company->logo_luminance    = $analysis['luminance'];
        $company->logo_has_solid_bg = $analysis['has_solid_bg'];
        $company->logo_dominant_hex = $analysis['dominant_hex'];
        $company->logo_chip_variant = $analysis['chip_variant'];

        $company->save();

        if ($old && $old !== $company->logo) {
            Storage::delete($old);
        }

        return response()->json([
            'success' => true,
            'url' => '/img/' . $company->logo,
            'chip_variant' => $company->logo_chip_variant,
        ]);
    }

    public function deletelogo(Company $company)
    {
        $this->authorize('update', $company);

        if ($company->logo) {
            Storage::delete($company->logo);
            $company->logo = null;
            $company->logo_luminance = null;
            $company->logo_has_solid_bg = null;
            $company->logo_dominant_hex = null;
            $company->logo_chip_variant = null;
            $company->save();
        }

        return response()->json(['success' => true]);
    }

    public function storeheader(Company $company)
    {
        $this->authorize('update', $company);

        $file = $this->validatedBrandImage('background_header');

        $old = $company->background_header;
        $company->background_header = $file->store('negocios');
        $company->save();

        if ($old && $old !== $company->background_header) {
            Storage::delete($old);
        }

        return response()->json([
            'success' => true,
            'url' => '/img/' . $company->background_header,
        ]);
    }

    public function deleteheader(Company $company)
    {
        $this->authorize('update', $company);

        if ($company->background_header) {
            Storage::delete($company->background_header);
            $company->background_header = null;
            $company->save();
        }

        return response()->json(['success' => true]);
    }

    public function changecompany(Request $request)
    {
        $request->validate([
            'company_selection' => 'required|integer',
        ]);

        $company = Company::where('user_id', auth()->id())
            ->where('id', $request->get('company_selection'))
            ->firstOrFail();

        Cookie::queue(Cookie::forever('selected_company', $company->id));

        $redirectAfter = $request->get('redirect_after');
        if ($redirectAfter && str_starts_with($redirectAfter, '/admin/')) {
            return redirect($redirectAfter);
        }

        return redirect()->route('admin.dashboard');
    }

    public function toggleEnabled(Request $request, Company $company)
    {
        $this->authorize('update', $company);

        $enabled = $request->boolean('enabled');
        $company->enabled = $enabled;
        $company->save();

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'ok' => true,
                'enabled' => (bool) $company->enabled,
                'company_id' => $company->id,
            ]);
        }

        return back()->with('flash', $enabled
            ? 'Carta «' . $company->name . '» publicada.'
            : 'Carta «' . $company->name . '» despublicada.');
    }

    protected function validatedBrandImage(string $field): UploadedFile
    {
        $file = request()->file($field);

        if (!$file) {
            throw ValidationException::withMessages([
                $field => ['No se recibió ningún archivo. Vuelve a seleccionar la imagen.'],
            ]);
        }

        if (!$file->isValid()) {
            $message = 'No se pudo subir la imagen.';
            switch ($file->getError()) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $message = 'La imagen es demasiado grande. Máximo 5 MB.';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = 'La subida se interrumpió. Vuelve a intentarlo.';
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                case UPLOAD_ERR_CANT_WRITE:
                    $message = 'Error del servidor al guardar la imagen. Contacta con soporte.';
                    break;
            }

            throw ValidationException::withMessages([
                $field => [$message],
            ]);
        }

        $this->validate(request(), [
            $field => 'required|file|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ]);

        return $file;
    }

    public function updateDailyHighlights(Request $request, UserPlanService $plans, Company $company)
    {
        $this->authorize('update', $company);

        if ($request->has('clear')) {
            $company->daily_highlights = null;
            $company->daily_spotlight = null;
            $company->daily_spotlight_price = null;
            $company->save();

            return redirect()
                ->route('admin.sections.index')
                ->with('flash', 'Destacados del día quitados de la carta.');
        }

        $validated = $request->validate([
            'highlights' => ['nullable', 'array', 'max:3'],
            'highlights.*.type' => ['required_with:highlights', 'string', 'in:spotlight,menu_del_dia'],
            'highlights.*.label' => ['nullable', 'string', 'max:80'],
            'highlights.*.text' => ['nullable', 'string', 'max:2000'],
            'highlights.*.price' => ['nullable', 'string', 'max:32'],
        ]);

        if (! empty($validated['highlights'])) {
            $plans->assertCanUseChefSuggestions(auth()->user());
        }

        $normalized = [];
        foreach ($validated['highlights'] ?? [] as $row) {
            $text = trim((string) ($row['text'] ?? ''));
            if ($text === '') {
                continue;
            }
            $type = ($row['type'] ?? 'spotlight') === 'menu_del_dia' ? 'menu_del_dia' : 'spotlight';
            $label = trim((string) ($row['label'] ?? ''));
            $price = trim(str_replace(',', '.', (string) ($row['price'] ?? '')));
            if ($price !== '' && ! preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
                throw ValidationException::withMessages([
                    'highlights' => ['Indica un precio válido (ej: 12 o 12,50).'],
                ]);
            }
            $normalized[] = [
                'type' => $type,
                'label' => $label,
                'text' => $text,
                'price' => $price !== '' ? $price : null,
            ];
        }

        $company->daily_highlights = count($normalized) > 0 ? $normalized : null;
        if (count($normalized) > 0) {
            $first = $normalized[0];
            $company->daily_spotlight = $first['text'];
            $company->daily_spotlight_price = $first['price'];
        } else {
            $company->daily_spotlight = null;
            $company->daily_spotlight_price = null;
        }
        $company->save();

        $message = $company->hasDailySpotlight()
            ? 'Destacados del día guardados.'
            : 'Destacados del día quitados de la carta.';

        return redirect()
            ->route('admin.sections.index')
            ->with('flash', $message);
    }
}
