<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

trait PreparesLandingPage
{
    /** @return list<string> */
    protected function landingSupportedLocales(): array
    {
        return array_keys(config('landing.locales', []));
    }

    protected function resolveLandingLocale(Request $request): string
    {
        $supported = $this->landingSupportedLocales();
        $fallback = config('landing.fallback_locale', 'en');

        $query = $request->query('lang');
        if (is_string($query) && in_array($query, $supported, true)) {
            return $query;
        }

        $cookieName = config('landing.cookie_name', 'webnu_landing_lang');
        $cookie = $request->cookie($cookieName);
        if (is_string($cookie) && in_array($cookie, $supported, true)) {
            return $cookie;
        }

        $accept = $request->header('Accept-Language');
        if (! is_string($accept) || trim($accept) === '') {
            return config('landing.default', 'es');
        }

        $preferred = $request->getPreferredLanguage($supported);

        if (is_string($preferred) && in_array($preferred, $supported, true)) {
            return $preferred;
        }

        return $fallback;
    }

    /** @return list<array<string, mixed>> */
    protected function buildTemplateShowcase(): array
    {
        $templates = config('company_templates.templates', []);
        $demoUrls = config('landing.template_demo_urls', []);
        $showcaseKeys = config('landing.template_showcase_keys', ['pasion', 'nocturne', 'otaku']);
        $items = [];

        foreach ($showcaseKeys as $key) {
            $meta = $templates[$key] ?? null;
            if (! is_array($meta) || empty($meta['label'])) {
                continue;
            }
            $demoPath = $demoUrls[$key] ?? null;
            $label = __("landing.templates.showcase.{$key}.label");
            $description = __("landing.templates.showcase.{$key}.description");
            if ($label === "landing.templates.showcase.{$key}.label") {
                $label = $meta['label'];
            }
            if ($description === "landing.templates.showcase.{$key}.description") {
                $description = $meta['description'] ?? '';
            }
            $items[] = [
                'key' => $key,
                'label' => $label,
                'description' => $description,
                'group' => $meta['group'] ?? 'modern',
                'recommended' => ! empty($meta['recommended']),
                'preview' => asset($meta['preview_image'] ?? 'img/admin/templates/pasion.svg'),
                'demo_url' => $demoPath ? url($demoPath) : null,
            ];
        }

        return $items;
    }

    protected function landingTemplateCatalogCount(): int
    {
        $templates = config('company_templates.templates', []);

        return count(array_filter($templates, function ($meta) {
            return is_array($meta) && ! empty($meta['label']);
        }));
    }

    /** @return list<string> */
    protected function landingPricingTierOrder(): array
    {
        $order = config('landing.pricing_order', ['free', 'pro', 'plus']);
        $tiers = config('plans.tiers', []);

        return array_values(array_filter($order, function ($tierId) use ($tiers) {
            return is_string($tierId) && isset($tiers[$tierId]);
        }));
    }

    /** @return list<array<string, mixed>> */
    protected function buildLandingPricingPlans(): array
    {
        $highlight = config('landing.pricing_highlight', 'plus');
        $tiers = config('plans.tiers', []);
        $plans = [];

        foreach ($this->landingPricingTierOrder() as $tierId) {
            $langKey = "landing.pricing.{$tierId}";
            $tier = $tiers[$tierId];
            $name = __("{$langKey}.name");
            if ($name === "{$langKey}.name") {
                $name = $tier['label'] ?? $tierId;
            }

            $badge = __("{$langKey}.badge");
            if ($badge === "{$langKey}.badge") {
                $badge = null;
            }

            if ($tierId === 'franchise') {
                $email = config('landing.franchise_contact_email', 'hola@webnu.es');
                $ctaUrl = 'mailto:' . $email . '?subject=' . rawurlencode('Webnu Franquicias');
            } else {
                $ctaUrl = route('register');
            }

            $plans[] = [
                'id' => $tierId,
                'highlight' => $tierId === $highlight,
                'name' => $name,
                'tagline' => __("{$langKey}.tagline"),
                'price' => __("{$langKey}.price"),
                'period' => __("{$langKey}.period"),
                'badge' => $tierId === $highlight ? $badge : null,
                'cta' => __("{$langKey}.cta"),
                'features' => __("{$langKey}.features"),
                'cta_url' => $ctaUrl,
            ];
        }

        return $plans;
    }

    /** @return array<string, mixed>|null */
    protected function buildLandingFranchisePlan(): ?array
    {
        if (! isset(config('plans.tiers', [])['franchise'])) {
            return null;
        }

        // Evita duplicado si franchise ya forma parte del grid principal.
        if (in_array('franchise', $this->landingPricingTierOrder(), true)) {
            return null;
        }

        $langKey = 'landing.pricing.franchise';
        $email = config('landing.franchise_contact_email', 'hola@webnu.es');

        return [
            'id' => 'franchise',
            'name' => __("{$langKey}.name"),
            'tagline' => __("{$langKey}.tagline"),
            'price' => __("{$langKey}.price"),
            'period' => __("{$langKey}.period"),
            'cta' => __("{$langKey}.cta"),
            'features' => __("{$langKey}.features"),
            'cta_url' => 'mailto:' . $email . '?subject=' . rawurlencode('Webnu Franquicias'),
        ];
    }

    /** @return array<string, mixed> */
    protected function landingViewData(Request $request): array
    {
        $locale = $this->resolveLandingLocale($request);
        App::setLocale($locale);

        $demoUrls = [
            url('/carta/demo'),
            url('/carta/demo-cocktails'),
            url('/carta/demo-fuego'),
        ];
        $demoPreviews = [
            'brasa-solomillo.jpg',
            'cocktail-negroni.jpg',
            'fuego-tonkotsu.jpg',
        ];
        $demoAccents = [
            'border-border-subtle bg-surface-container-lowest',
            'border-primary/30 bg-surface-container',
            'border-orange-400 bg-orange-950/10',
        ];

        $demoShowcases = [];
        foreach (__('landing.demos.items') as $i => $item) {
            $demoShowcases[] = array_merge($item, [
                'url' => $demoUrls[$i] ?? $demoUrls[0],
                'preview' => asset('img/productos/' . ($demoPreviews[$i] ?? $demoPreviews[0])),
                'accent' => $demoAccents[$i] ?? $demoAccents[0],
            ]);
        }

        // 5 plantillas TV curadas: hero, tapas, daily, video, menu.
        // Cada slide trae sus propias imágenes (la principal y, opcionalmente,
        // miniaturas para tapas), apuntando a assets existentes.
        $rawSlides = __('landing.tvpik.slides') ?: [];
        $tvpikTemplates = config('tvpik_templates.templates', []);
        $kindToTemplate = [
            'hero' => 'hero',
            'tapas' => 'tapas',
            'daily' => 'daily',
            'video' => 'video',
            'menu' => 'menu',
            'dual' => 'featured',
        ];
        $kindImages = [
            'hero'  => asset('img/productos/brasa-solomillo.jpg'),
            'tapas' => asset('img/productos/brasa-burrata.jpg'),
            'daily' => asset('img/productos/brasa-burrata.jpg'),
            'menu'  => asset('img/productos/cocktail-negroni.jpg'),
            'dual'  => asset('img/productos/brasa-solomillo.jpg'),
        ];
        $tapasImages = [
            asset('img/productos/brasa-burrata.jpg'),
            asset('img/productos/brasa-solomillo.jpg'),
            asset('img/productos/brasa-brownie.jpg'),
        ];
        $tvpikSlides = [];
        foreach ($rawSlides as $slide) {
            $kind = $slide['kind'] ?? 'hero';
            $slide['kind'] = $kind;
            $tplKey = $kindToTemplate[$kind] ?? 'hero';
            $tplMeta = is_array($tvpikTemplates[$tplKey] ?? null) ? $tvpikTemplates[$tplKey] : null;
            if ($tplMeta && ! empty($tplMeta['thumbnail'])) {
                $slide['template_preview'] = asset($tplMeta['thumbnail']);
            }

            if ($kind === 'video') {
                $videoFile = config('demo_media.videos.dessert.file', 'reel-dal-naan.mp4');
                $slide['video_url'] = asset('img/demo/' . $videoFile);
                $slide['video_poster'] = asset('img/productos/brasa-brownie.jpg');
                $slide['image'] = $slide['video_poster'];
            } else {
                $slide['image'] = $kindImages[$kind] ?? reset($kindImages);
            }

            if ($kind === 'tapas' && ! empty($slide['items']) && is_array($slide['items'])) {
                foreach ($slide['items'] as $idx => $item) {
                    if (! isset($item['image'])) {
                        $slide['items'][$idx]['image'] = $tapasImages[$idx] ?? $tapasImages[0];
                    }
                }
            }

            $tvpikSlides[] = $slide;
        }

        $steakFile = config('demo_media.videos.steak.file', 'reel-grill-chicken.mp4');
        $templateCount = $this->landingTemplateCatalogCount();

        $contactEmail = \App\PlatformSetting::contactPublicEmail();
        $landingFaq = collect(__('landing.faq.items'))->map(function (array $item) use ($contactEmail) {
            $item['a'] = str_replace(':email', $contactEmail, $item['a']);

            return $item;
        })->all();

        $user = $request->user();
        $userDisplayName = $user ? $this->landingUserDisplayName($user->name ?? '') : '';

        $customizePresets = __('landing.customize.presets');
        $colorSchemes = [
            0 => ['primary' => '#c2410c', 'bg' => '#fff7ed', 'surface' => '#ffffff', 'text' => '#1c1917', 'muted' => '#78716c'],
            1 => ['primary' => '#60a5fa', 'bg' => '#0a0e14', 'surface' => '#111827', 'text' => '#f1f5f9', 'muted' => '#94a3b8'],
            2 => ['primary' => '#a855f7', 'bg' => '#0f0f13', 'surface' => '#1a1a24', 'text' => '#e2e8f0', 'muted' => '#94a3b8'],
        ];
        $landingTemplatePicker = [];
        for ($i = 0; $i < 3; $i++) {
            $preset = is_array($customizePresets[$i] ?? null) ? $customizePresets[$i] : [];
            $demo = $demoShowcases[$i] ?? [];
            $landingTemplatePicker[] = array_merge($preset, $colorSchemes[$i] ?? [], [
                'preview' => $demo['preview'] ?? asset('img/productos/brasa-solomillo.jpg'),
                'category' => mb_strtoupper((string) ($demo['badge'] ?? $preset['template'] ?? '')),
                'title' => $demo['title'] ?? ($preset['business'] ?? ''),
                'desc' => $demo['desc'] ?? '',
                'tags' => array_slice($demo['tags'] ?? [], 0, 2),
            ]);
        }

        return [
            'locale' => $locale,
            'landingLocales' => config('landing.locales', []),
            'demoShowcases' => $demoShowcases,
            'tvpikSlides' => $tvpikSlides,
            'landingFeatures' => __('landing.features.items'),
            'landingTestimonials' => __('landing.testimonials.items'),
            'landingSteps' => __('landing.process.steps'),
            'landingFaq' => $landingFaq,
            'landingPricingPlans' => $this->buildLandingPricingPlans(),
            'landingFranchisePlan' => $this->buildLandingFranchisePlan(),
            'landingPricingTierOrder' => $this->landingPricingTierOrder(),
            'landingCustomizePresets' => __('landing.customize.presets'),
            'landingTemplatePicker' => $landingTemplatePicker,
            'landingReelVideo' => asset('img/demo/' . $steakFile),
            'demoCocktailsUrl' => url('/carta/demo-cocktails'),
            'templateCount' => $templateCount,
            'userDisplayName' => $userDisplayName,
            'settingsUrl' => route('admin.settings'),
            'panelUrl' => route('admin.dashboard'),
            'logoutUrl' => route('logout'),
        ];
    }

    protected function landingUserDisplayName(string $fullName): string
    {
        $fullName = trim($fullName);
        if ($fullName === '') {
            return '';
        }

        $parts = preg_split('/\s+/', $fullName);

        return $parts[0] ?? $fullName;
    }
}
