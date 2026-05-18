<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** @property-read \App\User|null $user */

class Company extends Model
{
    protected $fillable = ['name', 'chef_name', 'slug', 'logo', 'background_header', 'address', 'postal_code', 'city', 'province', 'country', 'phone', 'mobile_phone', 'email', 'web', 'whatsapp', 'facebook', 'instagram', 'comments', 'schedule', 'template', 'theme_settings', 'menu_type', 'menu_type_2_pdf', 'enabled', 'reservation', 'user_id', 'created_at', 'updated_at'];

    protected $casts = [
        'theme_settings' => 'array',
        'enabled' => 'boolean',
        'reservation' => 'boolean',
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
        $saved = is_array($this->theme_settings) ? $this->theme_settings : [];

        return array_merge($defaults, array_filter($saved, function ($value) {
            return $value !== null && $value !== '';
        }));
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
}


