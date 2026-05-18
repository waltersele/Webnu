<?php

namespace App\Services;

use App\Allergen;
use App\Company;
use App\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class MenuService
{
    /** @var Collection<int, Allergen>|null */
    protected $allergenPool;

    public function sectionsForCompany(Company $company): Collection
    {
        $sections = Section::with(['products.allergens'])
            ->orderBy('order')
            ->where('company_id', $company->id)
            ->get();

        $sections = $sections->map(function ($section) {
            $section->setRelation(
                'products',
                $section->products->sortBy('order')->values()
            );

            return $section;
        });

        return $this->enrichMenuPresentation($sections);
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
        ]));

        if (in_array($company->template, $templates, true)) {
            return 'themes.' . $company->template;
        }

        $company->template = 'basic';

        return 'themes.basic';
    }

    public function applyStudioPreview(Company $company, Request $request): Company
    {
        if (!$request->boolean('studio_preview')) {
            return $company;
        }

        if (!auth()->check() || (int) auth()->id() !== (int) $company->user_id) {
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

        if ($overrides !== []) {
            $company->theme_settings = array_merge(
                $company->resolvedThemeSettings(),
                $overrides
            );
        }

        return $company;
    }

    protected function enrichMenuPresentation(Collection $sections): Collection
    {
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
