<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Product;
use App\Services\CompanySlugService;
use App\Services\CompanyThemeService;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;

class CompaniesController extends Controller
{
    public function index()
    {
        $query = Company::where('user_id', auth()->id());

        if (auth()->user()->isSalesRep() && ! auth()->user()->isSuperAdmin()) {
            $query->where(function ($q) {
                $q->whereNull('sales_rep_user_id')
                    ->orWhereNotNull('sales_converted_at');
            });
        }

        $companies = $query->latest('updated_at')->get();

        return view('admin.companies.index', compact('companies'));
    }

    public function edit(Company $company, UserPlanService $plans)
    {
        $this->authorize('view', $company);

        $user = auth()->user();
        $templateAccess = $plans->templateAccessForUser($user);
        $templates = config('company_templates.templates', []);
        $colorKeys = config('company_templates.color_keys', []);
        $fontKeys = config('company_templates.font_keys', []);
        $fonts = config('company_templates.fonts', []);
        $themeSettings = $company->resolvedThemeSettings();
        $themePresets = config('company_templates.presets', []);
        $previewUrl = route('see_menu', [
            'companySlug' => $company->slug,
            'studio_preview' => 1,
        ]);

        $templateLabels = collect($templates)->mapWithKeys(function ($meta, $key) {
            return [$key => $meta['label'] ?? $key];
        })->all();

        $hasMenuProducts = $company->sections()->whereHas('products')->exists();
        $previewUsesSamples = ! $hasMenuProducts;

        return view('admin.companies.edit', compact(
            'company',
            'templates',
            'templateAccess',
            'colorKeys',
            'fontKeys',
            'fonts',
            'themeSettings',
            'themePresets',
            'previewUrl',
            'templateLabels',
            'previewUsesSamples'
        ));
    }

    public function store(Request $request, UserPlanService $plans, CompanySlugService $slugs)
    {
        $plans->assertCanCreateCompany($request->user());

        $this->validate($request, [
            'name' => 'required',
        ]);

        $slug = $slugs->generateFromName(
            $request->get('name'),
            $request->get('city')
        );

        $userId = auth()->id();
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

    public function update(Company $company, Request $request, CompanySlugService $slugs, CompanyThemeService $themes, UserPlanService $plans)
    {
        $this->authorize('update', $company);

        $this->validate($request, [
            'name' => 'required',
            'slug' => 'nullable|string|max:64',
        ]);

        $newTemplate = $request->get('template', $company->template ?: 'basic');
        if ($newTemplate !== ($company->template ?: 'basic')) {
            $plans->assertCanUseTemplate($request->user(), $newTemplate);
        }

        if ($request->filled('slug')) {
            $customSlug = $slugs->normalize($request->get('slug'));
            $slugError = $slugs->validateCustomSlug($customSlug, $company->id);
            if ($slugError) {
                throw ValidationException::withMessages(['slug' => [$slugError]]);
            }
            $company->slug = $customSlug;
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

        $step = $request->get('studio_step', 'identity');
        if (!in_array($step, ['identity', 'contact', 'design', 'publish'], true)) {
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

    public function storelogo(Company $company)
    {
        $this->authorize('update', $company);

        $file = $this->validatedBrandImage('logo');

        $old = $company->logo;
        $company->logo = $file->store('negocios');
        $company->save();

        if ($old && $old !== $company->logo) {
            Storage::delete($old);
        }

        return response()->json([
            'success' => true,
            'url' => '/img/' . $company->logo,
        ]);
    }

    public function deletelogo(Company $company)
    {
        $this->authorize('update', $company);

        if ($company->logo) {
            Storage::delete($company->logo);
            $company->logo = null;
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

        return redirect()->route('admin.dashboard');
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

    public function updateDailyHighlights(Request $request, Company $company)
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
