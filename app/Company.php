<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** @property-read \App\User|null $user */

class Company extends Model
{
    protected $fillable = ['name', 'chef_name', 'slug', 'logo', 'background_header', 'address', 'postal_code', 'city', 'province', 'country', 'phone', 'mobile_phone', 'email', 'web', 'whatsapp', 'facebook', 'instagram', 'comments', 'schedule', 'template', 'theme_settings', 'menu_type', 'menu_type_2_pdf', 'enabled', 'reservation', 'user_id', 'sales_rep_user_id', 'sales_converted_at', 'default_locale', 'enabled_locales', 'suggest_translation_upgrade', 'daily_spotlight', 'daily_spotlight_price', 'created_at', 'updated_at'];

    protected $attributes = [
        'reservation' => false,
        'default_locale' => 'es',
    ];

    protected $casts = [
        'theme_settings' => 'array',
        'enabled_locales' => 'array',
        'enabled' => 'boolean',
        'reservation' => 'boolean',
        'suggest_translation_upgrade' => 'boolean',
        'sales_converted_at' => 'datetime',
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
        return trim((string) $this->daily_spotlight) !== '';
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
}


