<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['name', 'order', 'enabled', 'company_id'];

    protected $hidden = ['company_id', 'created_at', 'updated_at'];

    public function company()
    {
        return $this->belongsTo('App\Company');
    }

    public function products()
    {
        return $this->hasMany('App\Product');
    }

    public function translations()
    {
        return $this->hasMany(SectionTranslation::class);
    }
}
