<?php

namespace App\Services;

use App\Company;
use App\Product;
use App\ProductTranslation;
use App\Section;
use App\SectionTranslation;
use App\Services\MenuTranslation\GeminiMenuTranslationProvider;
use App\TranslationJob;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MenuTranslationService
{
    /** @var GeminiMenuTranslationProvider */
    protected $provider;

    public function __construct(GeminiMenuTranslationProvider $provider)
    {
        $this->provider = $provider;
    }

    public function updateCompanyLocales(Company $company, User $user, array $locales): Company
    {
        app(UserPlanService::class)->assertCanEnableLocales($user, count($locales));

        $default = $company->defaultLocale();
        $supported = array_keys(config('menu_locales.supported', []));

        $normalized = array_values(array_unique(array_filter($locales, function ($locale) use ($supported, $default) {
            return in_array($locale, $supported, true) && $locale !== $default;
        })));

        $company->enabled_locales = $normalized;
        $company->save();

        return $company;
    }

    public function saveSectionTranslation(Section $section, string $locale, string $name, string $source = SectionTranslation::SOURCE_MANUAL): SectionTranslation
    {
        $existing = SectionTranslation::where('section_id', $section->id)
            ->where('locale', $locale)
            ->first();

        if ($existing && in_array($existing->source, [SectionTranslation::SOURCE_MANUAL, SectionTranslation::SOURCE_AI_EDITED], true) && $source === SectionTranslation::SOURCE_AI) {
            return $existing;
        }

        $resolvedSource = $source;
        if ($existing && $existing->source === SectionTranslation::SOURCE_AI && $source === SectionTranslation::SOURCE_MANUAL) {
            $resolvedSource = SectionTranslation::SOURCE_AI_EDITED;
        }

        return SectionTranslation::updateOrCreate(
            ['section_id' => $section->id, 'locale' => $locale],
            ['name' => trim($name), 'source' => $resolvedSource]
        );
    }

    public function saveProductTranslation(Product $product, string $locale, string $name, ?string $description, string $source = ProductTranslation::SOURCE_MANUAL): ProductTranslation
    {
        $existing = ProductTranslation::where('product_id', $product->id)
            ->where('locale', $locale)
            ->first();

        if ($existing && in_array($existing->source, [ProductTranslation::SOURCE_MANUAL, ProductTranslation::SOURCE_AI_EDITED], true) && $source === ProductTranslation::SOURCE_AI) {
            return $existing;
        }

        $resolvedSource = $source;
        if ($existing && $existing->source === ProductTranslation::SOURCE_AI && $source === ProductTranslation::SOURCE_MANUAL) {
            $resolvedSource = ProductTranslation::SOURCE_AI_EDITED;
        }

        return ProductTranslation::updateOrCreate(
            ['product_id' => $product->id, 'locale' => $locale],
            [
                'name' => trim($name),
                'description' => $description !== null ? trim($description) : null,
                'source' => $resolvedSource,
            ]
        );
    }

    public function translateCompany(Company $company, User $user, string $targetLocale, bool $overwriteAiOnly = true): TranslationJob
    {
        app(UserPlanService::class)->assertCanUseTranslation($user);

        $default = $company->defaultLocale();
        if ($targetLocale === $default) {
            throw ValidationException::withMessages([
                'locale' => 'El idioma base no necesita traducción.',
            ]);
        }

        if (! in_array($targetLocale, $company->enabledLocales(), true)) {
            throw ValidationException::withMessages([
                'locale' => 'Activa este idioma antes de traducir.',
            ]);
        }

        $sections = Section::with('products')
            ->where('company_id', $company->id)
            ->orderBy('order')
            ->get();

        $items = $this->collectTranslatableItems($sections);
        $job = TranslationJob::create([
            'company_id' => $company->id,
            'user_id' => $user->id,
            'target_locale' => $targetLocale,
            'status' => TranslationJob::STATUS_PROCESSING,
            'items_total' => count($items),
            'items_done' => 0,
        ]);

        try {
            $chunks = array_chunk($items, 25);
            $done = 0;

            foreach ($chunks as $chunk) {
                if (! $overwriteAiOnly) {
                    $chunk = $this->filterSkippableItems($chunk, $targetLocale);
                } elseif ($overwriteAiOnly) {
                    $chunk = $this->filterAiOverwriteItems($chunk, $targetLocale);
                }

                if ($chunk === []) {
                    continue;
                }

                $translated = $this->provider->translateBatch($chunk, $default, $targetLocale);
                if ($translated === null) {
                    throw new \RuntimeException('Gemini no pudo traducir la carta. Comprueba la API en Plataforma → Escaneo IA.');
                }

                $this->persistTranslatedItems($sections, $translated, $targetLocale);
                $done += count($chunk);
                $job->items_done = $done;
                $job->save();
            }

            $job->status = TranslationJob::STATUS_DONE;
            $job->provider = $this->provider->name();
            $job->save();
        } catch (\Throwable $e) {
            $job->status = TranslationJob::STATUS_FAILED;
            $job->error_message = $e->getMessage();
            $job->save();

            throw ValidationException::withMessages([
                'locale' => $e->getMessage(),
            ]);
        }

        return $job;
    }

    public function statsForCompany(Company $company): array
    {
        $default = $company->defaultLocale();
        $sections = Section::with('products')->where('company_id', $company->id)->get();
        $sectionCount = $sections->count();
        $productCount = $sections->sum(function ($section) {
            return $section->products->count();
        });
        $total = $sectionCount + $productCount;

        $stats = [];
        foreach ($company->enabledLocales() as $locale) {
            if ($locale === $default) {
                $stats[$locale] = ['total' => $total, 'done' => $total, 'percent' => 100];
                continue;
            }

            $sectionsDone = SectionTranslation::whereIn('section_id', $sections->pluck('id'))
                ->where('locale', $locale)
                ->count();
            $productsDone = ProductTranslation::whereIn('product_id', $sections->flatMap->products->pluck('id'))
                ->where('locale', $locale)
                ->count();
            $done = $sectionsDone + $productsDone;
            $stats[$locale] = [
                'total' => $total,
                'done' => $done,
                'percent' => $total > 0 ? (int) round(($done / $total) * 100) : 0,
            ];
        }

        return $stats;
    }

    /**
     * @param Collection<int, Section> $sections
     */
    protected function collectTranslatableItems(Collection $sections): array
    {
        $items = [];
        foreach ($sections as $section) {
            $items[] = [
                'type' => 'section',
                'id' => $section->id,
                'name' => $section->name,
            ];
            foreach ($section->products as $product) {
                $items[] = [
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => (string) ($product->description ?? ''),
                ];
            }
        }

        return $items;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    protected function filterAiOverwriteItems(array $items, string $locale): array
    {
        $sectionIds = [];
        $productIds = [];
        foreach ($items as $item) {
            if ($item['type'] === 'section') {
                $sectionIds[] = $item['id'];
            } else {
                $productIds[] = $item['id'];
            }
        }

        $protectedSections = SectionTranslation::whereIn('section_id', $sectionIds)
            ->where('locale', $locale)
            ->whereIn('source', [SectionTranslation::SOURCE_MANUAL, SectionTranslation::SOURCE_AI_EDITED])
            ->pluck('section_id')
            ->all();
        $protectedProducts = ProductTranslation::whereIn('product_id', $productIds)
            ->where('locale', $locale)
            ->whereIn('source', [ProductTranslation::SOURCE_MANUAL, ProductTranslation::SOURCE_AI_EDITED])
            ->pluck('product_id')
            ->all();

        return array_values(array_filter($items, function ($item) use ($protectedSections, $protectedProducts) {
            if ($item['type'] === 'section') {
                return ! in_array($item['id'], $protectedSections, true);
            }

            return ! in_array($item['id'], $protectedProducts, true);
        }));
    }

    /**
     * @param array<int, array<string, mixed>> $items
     * @return array<int, array<string, mixed>>
     */
    protected function filterSkippableItems(array $items, string $locale): array
    {
        return $this->filterAiOverwriteItems($items, $locale);
    }

    /**
     * @param Collection<int, Section> $sections
     * @param array<int, array<string, mixed>> $translated
     */
    protected function persistTranslatedItems(Collection $sections, array $translated, string $locale): void
    {
        $sectionsById = $sections->keyBy('id');
        $productsById = $sections->flatMap->products->keyBy('id');

        foreach ($translated as $row) {
            $type = $row['type'] ?? null;
            $id = (int) ($row['id'] ?? 0);
            $name = trim((string) ($row['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            if ($type === 'section' && $sectionsById->has($id)) {
                $this->saveSectionTranslation($sectionsById[$id], $locale, $name, SectionTranslation::SOURCE_AI);
            }

            if ($type === 'product' && $productsById->has($id)) {
                $this->saveProductTranslation(
                    $productsById[$id],
                    $locale,
                    $name,
                    (string) ($row['description'] ?? ''),
                    ProductTranslation::SOURCE_AI
                );
            }
        }
    }
}
