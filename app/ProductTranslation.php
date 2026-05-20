<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductTranslation extends Model
{
    public const SOURCE_MANUAL = 'manual';
    public const SOURCE_AI = 'ai';
    public const SOURCE_AI_EDITED = 'ai_edited';

    protected $fillable = ['product_id', 'locale', 'name', 'description', 'source'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
