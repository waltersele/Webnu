<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuItem extends Model
{
    protected $fillable = [
        'menu_id',
        'menu_section_id',
        'product_id',
        'label',
        'image',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function section()
    {
        return $this->belongsTo(MenuSection::class, 'menu_section_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function displayName(): string
    {
        if (! empty($this->label)) {
            return $this->label;
        }

        return optional($this->product)->name ?? '';
    }

    public function imageUrl(): ?string
    {
        if (! empty($this->image)) {
            return url('img/' . ltrim($this->image, '/'));
        }

        $product = $this->product;
        if ($product && ! empty($product->image)) {
            return url('img/' . ltrim($product->image, '/'));
        }

        return null;
    }

    public function displayImage(): ?string
    {
        return $this->imageUrl();
    }

    public function displayPrice(): ?string
    {
        $product = $this->product;
        if (! $product) {
            return null;
        }

        if ($product->price_unit) {
            return number_format((float) $product->price_unit, 2, ',', '.') . ' €';
        }

        if ($product->price_portion) {
            return number_format((float) $product->price_portion, 2, ',', '.') . ' €';
        }

        return null;
    }
}
