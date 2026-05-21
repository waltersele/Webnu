<?php

namespace App\Services;

use App\Allergen;
use App\Company;
use App\ProductTranslation;
use App\Product;
use App\Section;
use App\SectionTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MenuService
{
    /** @var Collection<int, Allergen>|null */
    protected $allergenPool;

    /** @var MenuLocaleService */
    protected $locales;

    public function __construct(MenuLocaleService $locales)
    {
        $this->locales = $locales;
    }

    public function resolveMenuLocale(Request $request, Company $company): string
    {
        return $this->locales->resolveMenuLocale($request, $company);
    }

    public function sectionsForCompany(Company $company, ?string $locale = null): Collection
    {
        $locale = $locale ?: $company->defaultLocale();

        $sections = Section::with(['products.allergens', 'translations', 'products.translations'])
            ->orderBy('order')
            ->where('company_id', $company->id)
            ->get();

        $sections = $sections->map(function ($section) use ($locale, $company) {
            $section = $this->applySectionLocale($section, $locale, $company->defaultLocale());
            $section->setRelation(
                'products',
                $section->products->sortBy('order')->map(function ($product) use ($locale, $company) {
                    return $this->applyProductLocale($product, $locale, $company->defaultLocale());
                })->values()
            );

            return $section;
        });

        if ($this->shouldUseSampleMenu($sections, $company)) {
            $sections = $this->buildPreviewSampleSections($company);
        }

        return $this->enrichMenuPresentation($sections, $company);
    }

    protected function shouldUseSampleMenu(Collection $sections, Company $company): bool
    {
        if (! request()->boolean('studio_preview') && ! request()->boolean('sales_demo')) {
            return false;
        }

        if (request()->boolean('sales_demo')) {
            return false;
        }

        if (str_starts_with((string) $company->slug, 'demo')) {
            return false;
        }

        $productCount = $sections->sum(function ($section) {
            return $section->products->count();
        });

        return $productCount === 0;
    }

    protected function buildPreviewSampleSections(Company $company): Collection
    {
        $sample = config('menu_demo.sample_menu', []);
        $sections = collect();
        $sectionOrder = 0;

        foreach ($sample as $sectionData) {
            $sectionOrder++;
            $section = new Section([
                'name' => $sectionData['name'] ?? 'Carta',
                'order' => $sectionOrder,
                'enabled' => true,
                'company_id' => $company->id,
            ]);
            $section->id = -$sectionOrder;
            $section->exists = false;

            $products = collect();
            $productOrder = 0;

            foreach ($sectionData['products'] ?? [] as $productData) {
                $productOrder++;
                $product = new Product([
                    'name' => $productData['name'] ?? 'Plato de ejemplo',
                    'description' => $productData['description'] ?? '',
                    'price_unit' => $productData['price_unit'] ?? '',
                    'image' => $productData['image'] ?? null,
                    'video' => $productData['video'] ?? null,
                    'highlight' => $productData['highlight'] ?? null,
                    'order' => $productOrder,
                    'enabled' => true,
                    'section_id' => $section->id,
                ]);
                $product->id = -($sectionOrder * 100 + $productOrder);
                $product->exists = false;
                $product->setRelation('allergens', collect());
                $product->setRelation('translations', collect());
                $products->push($product);
            }

            $section->setRelation('products', $products);
            $section->setRelation('translations', collect());
            $sections->push($section);
        }

        return $sections;
    }

    protected function applySectionLocale(Section $section, string $locale, string $defaultLocale): Section
    {
        if ($locale === $defaultLocale) {
            return $section;
        }

        $translation = $section->translations->firstWhere('locale', $locale);
        if ($translation && $translation->name) {
            $section->name = $translation->name;
        }

        return $section;
    }

    protected function applyProductLocale($product, string $locale, string $defaultLocale)
    {
        if ($locale === $defaultLocale) {
            return $product;
        }

        $translation = $product->translations->firstWhere('locale', $locale);
        if ($translation) {
            if ($translation->name) {
                $product->name = $translation->name;
            }
            if ($translation->description !== null && $translation->description !== '') {
                $product->description = $translation->description;
            }
        }

        return $product;
    }

    public function dailyHighlightsForCompany(Company $company, ?string $locale = null): array
    {
        $text = trim((string) $company->daily_spotlight);
        if ($text === '') {
            return [];
        }

        $price = trim((string) $company->daily_spotlight_price);

        return [[
            'type' => 'spotlight',
            'label' => 'Especial de hoy',
            'text' => $text,
            'price' => $price !== '' ? $price : null,
        ]];
    }

    public function themeViewName(Company $company): string
    {
        $templates = array_keys(config('company_templates.templates', [
            'basic' => [],
            'pasion' => [],
            'oriental' => [],
            'visual' => [],
            'lumiere' => [],
            'bistro' => [],
            'otaku' => [],
            'japo' => [],
            'fastfood' => [],
            'pizza' => [],
            'mar' => [],
            'elegance' => [],
            'asador' => [],
        ]));

        if (in_array($company->template, $templates, true)) {
            return 'themes.' . $company->template;
        }

        $company->template = 'basic';

        return 'themes.basic';
    }

    public function applyStudioPreview(Company $company, Request $request): Company
    {
        if (! $request->boolean('studio_preview') && ! $request->boolean('sales_demo')) {
            return $company;
        }

        if (! auth()->check()) {
            return $company;
        }

        $user = auth()->user();
        $canPreview = (int) $user->id === (int) $company->user_id
            || ($company->isActiveSalesLead() && (int) $company->sales_rep_user_id === (int) $user->id);

        if (! $canPreview) {
            return $company;
        }

        $templates = array_keys(config('company_templates.templates', []));

        if ($request->filled('preview_template') && in_array($request->get('preview_template'), $templates, true)) {
            $company->template = $request->get('preview_template');
        }

        $overrides = [];
        foreach (array_keys(config('company_templates.color_keys', [])) as $key) {
            $value = $request->query('theme_' . $key);
            if (is_string($value) && preg_match('/^#[0-9A-Fa-f]{6}$/', $value)) {
                $overrides[$key] = strtolower($value);
            }
        }

        foreach (array_keys(config('company_templates.font_keys', [])) as $fontKey) {
            $value = $request->query('theme_' . $fontKey);
            $allowed = array_keys(config('company_templates.fonts', []));
            if (is_string($value) && in_array($value, $allowed, true)) {
                $overrides[$fontKey] = $value;
            }
        }

        if ($overrides !== []) {
            $company->theme_settings = array_merge(
                $company->resolvedThemeSettings(),
                $overrides
            );
        }

        return $company;
    }

    protected function enrichMenuPresentation(Collection $sections, Company $company): Collection
    {
        if (str_starts_with($company->slug, 'demo')) {
            return $sections;
        }

        $sampleImages = config('menu_demo.sample_images', []);
        $fillImages = (bool) config('menu_demo.fill_missing_images', true);
        $fillAllergens = (bool) config('menu_demo.fill_missing_allergens', true);
        $sampleVideos = config('menu_demo.sample_videos', []);
        $fillVideos = (bool) config('menu_demo.fill_missing_videos', true);
        $videoEvery = max(1, (int) config('menu_demo.video_every_n_products', 2));
        $imageIndex = 0;
        $videoIndex = 0;
        $productIndex = 0;
        $allergenIndex = 0;
        $demoAllergens = $fillAllergens ? $this->demoAllergens() : collect();

        foreach ($sections as $section) {
            foreach ($section->products as $product) {
                $assignDemoVideo = $fillVideos
                    && empty($product->video)
                    && $sampleVideos !== []
                    && ($productIndex % $videoEvery === 0);

                if ($assignDemoVideo) {
                    $videoPath = $sampleVideos[$videoIndex % count($sampleVideos)];
                    $videoIndex++;
                    if (is_file(public_path('img/' . $videoPath))) {
                        $product->setAttribute('display_video', $videoPath);
                    }
                }

                $hasDemoVideo = !empty($product->getAttribute('display_video'));

                if ($fillImages && empty($product->image) && empty($product->video) && !$hasDemoVideo && $sampleImages !== []) {
                    $path = $sampleImages[$imageIndex % count($sampleImages)];
                    $imageIndex++;
                    if (is_file(public_path('img/' . $path))) {
                        $product->setAttribute('display_image', $path);
                    }
                }

                if ($assignDemoVideo && empty($product->display_image) && empty($product->image) && $sampleImages !== []) {
                    $poster = $sampleImages[$imageIndex % count($sampleImages)];
                    $imageIndex++;
                    if (is_file(public_path('img/' . $poster))) {
                        $product->setAttribute('display_image', $poster);
                    }
                }

                $productIndex++;

                if ($fillAllergens && $product->allergens->isEmpty() && $demoAllergens->isNotEmpty()) {
                    $picked = collect();
                    $total = $demoAllergens->count();
                    for ($i = 0; $i < min(3, $total); $i++) {
                        $picked->push($demoAllergens[($allergenIndex + $i) % $total]);
                    }
                    $allergenIndex += 2;
                    $product->setRelation('allergens', $picked->unique('id')->values());
                }
            }
        }

        return $sections;
    }

    protected function demoAllergens(): Collection
    {
        if ($this->allergenPool === null) {
            $this->allergenPool = Allergen::orderBy('id')->get();
        }

        return $this->allergenPool;
    }
}
