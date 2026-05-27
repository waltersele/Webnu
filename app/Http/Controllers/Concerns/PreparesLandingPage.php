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
        $showcaseKeys = config('landing.template_showcase_keys', ['basic', 'nocturne', 'otaku']);
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
                'preview' => asset($meta['preview_image'] ?? 'img/admin/templates/basic.svg'),
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
        $kindImages = [
            'hero'  => asset('img/productos/brasa-solomillo.jpg'),
            'tapas' => asset('img/productos/brasa-burrata.jpg'),
            'daily' => asset('img/productos/brasa-burrata.jpg'),
            'video' => asset('img/productos/brasa-brownie.jpg'),
            'menu'  => asset('img/productos/cocktail-negroni.jpg'),
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
            $slide['image'] = $kindImages[$kind] ?? reset($kindImages);

            if ($kind === 'tapas' && ! empty($slide['items']) && is_array($slide['items'])) {
                foreach ($slide['items'] as $idx => $item) {
                    if (! isset($item['image'])) {
                        $slide['items'][$idx]['image'] = $tapasImages[$idx] ?? $tapasImages[0];
                    }
                }
            }

            if ($kind === 'video') {
                $slide['video_poster'] = $slide['image'];
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
