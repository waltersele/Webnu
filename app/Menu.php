<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = [
        'company_id',
        'name',
        'slug',
        'price',
        'subtitle',
        'includes',
        'image',
        'position',
        'enabled',
        'notes',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'enabled' => 'boolean',
        'position' => 'integer',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('position');
    }

    public function sections()
    {
        return $this->hasMany(MenuSection::class)->orderBy('position');
    }

    public function imageUrl(): ?string
    {
        return $this->image ? url('img/' . ltrim($this->image, '/')) : null;
    }

    public function formattedPrice(): ?string
    {
        if ($this->price === null) {
            return null;
        }

        return number_format((float) $this->price, 2, ',', '.') . ' €';
    }

    /**
     * URL pública del menú. Resuelve el owner slug a través de la company.
     */
    public function publicUrl(): ?string
    {
        $company = $this->company ?: $this->company()->with('user')->first();
        if (! $company) {
            return null;
        }
        $ownerSlug = optional($company->user)->resolveSlug();
        if (! $ownerSlug || ! $company->slug || ! $this->slug) {
            return null;
        }

        return route('public.menu', [
            'ownerSlug' => $ownerSlug,
            'companySlug' => $company->slug,
            'menuSlug' => $this->slug,
        ]);
    }
}
