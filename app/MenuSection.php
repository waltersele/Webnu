<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuSection extends Model
{
    protected $fillable = [
        'menu_id',
        'name',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }

    public function items()
    {
        return $this->hasMany(MenuItem::class)->orderBy('position');
    }
}
