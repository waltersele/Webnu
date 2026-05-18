<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/** @property-read \App\User|null $user */

class Company extends Model
{
    protected $fillable = ['name', 'chef_name', 'slug', 'logo', 'background_header', 'address', 'postal_code', 'city', 'province', 'country', 'phone', 'mobile_phone', 'email', 'web', 'whatsapp', 'facebook', 'instagram', 'comments', 'schedule', 'template', 'menu_type', 'menu_type_2_pdf', 'enabled', 'reservation', 'user_id', 'created_at', 'updated_at'];

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


