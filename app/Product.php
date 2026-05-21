<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'image',
        'video',
        'price_unit',
        'price_portion',
        'individual_sale',
        'weight_sale',
        'weight_unit_label',
        'highlight',
        'sales_demo_highlight',
        'order',
        'enabled',
        'section_id',
    ];

    protected $casts = [
        'sales_demo_highlight' => 'boolean',
        'highlight' => 'boolean',
        'enabled' => 'boolean',
        'individual_sale' => 'boolean',
        'weight_sale' => 'boolean',
    ];

    public function section()
    {
        return $this->belongsTo('App\Section');
    }

    public function allergens()
    {
        return $this->belongsToMany('App\Allergen', 'product_allergen');
    }

    public function translations()
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? url('img/' . ltrim($this->image, '/')) : null;
    }

    public function getVideoUrlAttribute()
    {
        return $this->video ? url('img/' . ltrim($this->video, '/')) : null;
    }

    public function hasMedia()
    {
        return !empty($this->image) || !empty($this->video);
    }

    public function highlightMeta(): ?array
    {
        if (!$this->highlight) {
            return null;
        }

        return config('product_highlights.options.' . $this->highlight);
    }
}
