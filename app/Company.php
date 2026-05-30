<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** @property-read \App\User|null $user */

class Company extends Model
{
    protected $fillable = ['name', 'chef_name', 'slug', 'public_url_format', 'public_slug_locked_at', 'logo', 'logo_luminance', 'logo_has_solid_bg', 'logo_dominant_hex', 'logo_chip_variant', 'background_header', 'address', 'postal_code', 'city', 'province', 'country', 'phone', 'mobile_phone', 'email', 'web', 'whatsapp', 'facebook', 'instagram', 'comments', 'schedule', 'template', 'theme_settings', 'menu_type', 'menu_type_2_pdf', 'combine_menus', 'menu_favorites_enabled', 'enabled', 'reservation', 'user_id', 'sales_rep_user_id', 'sales_converted_at', 'default_locale', 'enabled_locales', 'suggest_translation_upgrade', 'daily_spotlight', 'daily_spotlight_price', 'daily_highlights', 'created_at', 'updated_at'];

    protected $attributes = [
        'reservation' => false,
        'default_locale' => 'es',
        'menu_favorites_enabled' => true,
    ];

    protected $casts = [
        'theme_settings' => 'array',
        'enabled_locales' => 'array',
        'daily_highlights' => 'array',
        'enabled' => 'boolean',
        'combine_menus' => 'boolean',
        'menu_favorites_enabled' => 'boolean',
        'reservation' => 'boolean',
        'suggest_translation_upgrade' => 'boolean',
        'sales_converted_at' => 'datetime',
        'public_slug_locked_at' => 'datetime',
        'logo_luminance' => 'float',
        'logo_has_solid_bg' => 'boolean',
    ];

    public function themeColor(string $key): string
    {
        $template = $this->template ?: 'basic';
        $defaults = config('company_templates.defaults.' . $template, config('company_templates.defaults.basic', []));
        $settings = is_array($this->theme_settings) ? $this->theme_settings : [];

        if (!empty($settings[$key])) {
            return $settings[$key];
        }

        return $defaults[$key] ?? '#0074d9';
    }

    public function resolvedThemeSettings(): array
    {
        $template = $this->template ?: 'basic';
        $defaults = config('company_templates.defaults.' . $template, config('company_templates.defaults.basic', []));
        $fontDefaults = config('company_templates.font_defaults', []);
        $saved = is_array($this->theme_settings) ? $this->theme_settings : [];

        return array_merge($defaults, $fontDefaults, array_filter($saved, function ($value) {
            return $value !== null && $value !== '';
        }));
    }

    public function themeFontFamily(string $role): string
    {
        $settings = $this->resolvedThemeSettings();
        $key = $settings[$role] ?? config('company_templates.font_defaults.' . $role, 'inter');
        $fonts = config('company_templates.fonts', []);
        $meta = $fonts[$key] ?? $fonts['inter'] ?? ['family' => 'Inter', 'category' => 'sans-serif'];

        return "'" . ($meta['family'] ?? 'Inter') . "', " . ($meta['category'] ?? 'sans-serif');
    }

    public function sections()
    {
        return $this->hasMany(Section::class);
    }

    public function section()
    {
        return $this->sections();
    }

    public function menus()
    {
        return $this->hasMany(Menu::class)->orderBy('position');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_user_id');
    }

    public function salesHandoffs()
    {
        return $this->hasMany(SalesHandoff::class);
    }

    public function isActiveSalesLead(): bool
    {
        return $this->sales_rep_user_id !== null && $this->sales_converted_at === null;
    }

    public function scopeActiveSalesLeads($query)
    {
        return $query
            ->whereNotNull('sales_rep_user_id')
            ->whereNull('sales_converted_at');
    }

    public function scopeCountsTowardPlanLimit($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('sales_rep_user_id')
                ->orWhereNotNull('sales_converted_at');
        });
    }

    public function canBeManagedBy(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        if ((int) $this->user_id === (int) $user->id) {
            return true;
        }

        return $this->isActiveSalesLead()
            && (int) $this->sales_rep_user_id === (int) $user->id;
    }

    public function hasDailySpotlight(): bool
    {
        return count($this->resolvedDailyHighlights()) > 0;
    }

    /**
     * @return array<int, array{type: string, label: string, text: string, price: ?string}>
     */
    public function resolvedDailyHighlights(): array
    {
        $stored = $this->daily_highlights;
        if (is_array($stored) && count($stored) > 0) {
            $items = [];
            foreach ($stored as $item) {
                if (! is_array($item)) {
                    continue;
                }
                $text = trim((string) ($item['text'] ?? ''));
                if ($text === '') {
                    continue;
                }
                $type = ($item['type'] ?? 'spotlight') === 'menu_del_dia' ? 'menu_del_dia' : 'spotlight';
                $defaultLabel = $type === 'menu_del_dia' ? 'Menú del día' : 'Especial de hoy';
                $label = trim((string) ($item['label'] ?? ''));
                $price = trim((string) ($item['price'] ?? ''));

                $items[] = [
                    'type' => $type,
                    'label' => $label !== '' ? $label : $defaultLabel,
                    'text' => $text,
                    'price' => $price !== '' ? $price : null,
                ];
            }

            return $items;
        }

        $legacy = trim((string) $this->daily_spotlight);
        if ($legacy === '') {
            return [];
        }

        $price = trim((string) $this->daily_spotlight_price);

        return [[
            'type' => 'spotlight',
            'label' => 'Especial de hoy',
            'text' => $legacy,
            'price' => $price !== '' ? $price : null,
        ]];
    }

    public function defaultLocale(): string
    {
        $locale = $this->default_locale ?: config('menu_locales.default', 'es');

        return array_key_exists($locale, config('menu_locales.supported', [])) ? $locale : 'es';
    }

    /** @return string[] */
    public function enabledLocales(): array
    {
        $default = $this->defaultLocale();
        $extra = is_array($this->enabled_locales) ? $this->enabled_locales : [];
        $supported = array_keys(config('menu_locales.supported', []));

        $locales = array_values(array_unique(array_filter(array_merge(
            [$default],
            $extra
        ), function ($locale) use ($supported, $default) {
            return in_array($locale, $supported, true) && $locale !== $default;
        })));

        return array_merge([$default], $locales);
    }

    /** Locales visibles en carta pública (solo si hay más de uno). */
    public function publicLocales(): array
    {
        return $this->enabledLocales();
    }

    public function hasMultipleLocales(): bool
    {
        return count($this->publicLocales()) > 1;
    }

    public function usesSimplePublicUrl(): bool
    {
        return $this->public_url_format === 'simple';
    }

    public function isPublicSlugLocked(): bool
    {
        return $this->public_slug_locked_at !== null;
    }

    public function lockPublicSlug(): void
    {
        if ($this->public_slug_locked_at === null) {
            $this->public_slug_locked_at = now();
            $this->save();
        }
    }

    public function menuFavoritesEnabled(): bool
    {
        return (bool) ($this->menu_favorites_enabled ?? true);
    }

    /**
     * URL pública de la carta.
     * Read-only: no dispara persistencia.
     */
    public function publicUrl(array $extra = []): string
    {
        return route('public.company', array_merge(['companySlug' => $this->slug], $extra));
    }

    /** Path relativo para mostrar (ej: "carta/casa-maria/menu-de-verano"). */
    public function publicPath(): string
    {
        return app(\App\Services\PublicPathRegistry::class)->companyPath($this);
    }

    public function previousPublicPath(): string
    {
        $original = $this->getOriginal();
        $clone = clone $this;
        if (is_array($original)) {
            if (array_key_exists('slug', $original)) {
                $clone->slug = $original['slug'];
            }
            if (array_key_exists('public_url_format', $original)) {
                $clone->public_url_format = $original['public_url_format'];
            }
        }

        return app(\App\Services\PublicPathRegistry::class)->companyPath($clone);
    }

    /**
     * Token HMAC para previsualizar la carta aunque esté deshabilitada.
     * Se usa con ?preview_token=xxx en la URL pública.
     */
    public function previewToken(): string
    {
        return substr(hash_hmac('sha256', (string) $this->id, config('app.key')), 0, 32);
    }

    public function isValidPreviewToken(string $token): bool
    {
        return hash_equals($this->previewToken(), $token);
    }
}


