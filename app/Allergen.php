<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Allergen extends Model
{
    protected $fillable = ['name', 'image'];

    public function products()
    {
        return $this->belongsToMany('App\Product', 'product_allergen');
    }

    public function iconUrl(): string
    {
        $relative = ltrim((string) $this->image, '/');

        if ($relative === '') {
            return '';
        }

        $candidates = [$relative];

        if (preg_match('/\.png$/i', $relative)) {
            $candidates[] = preg_replace('/\.png$/i', '.svg', $relative);
        }

        foreach ($candidates as $path) {
            if (is_file(public_path('img/' . $path))) {
                return asset('img/' . $path);
            }
        }

        return asset('img/' . $relative);
    }
}
