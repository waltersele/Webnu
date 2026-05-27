<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PublicSlugRedirect extends Model
{
    protected $fillable = [
        'from_path',
        'to_path',
        'company_id',
        'user_id',
        'menu_id',
        'http_status',
    ];

    protected $casts = [
        'http_status' => 'integer',
    ];

    public static function normalizePath(string $path): string
    {
        $path = trim($path);
        $path = ltrim($path, '/');

        return strtolower($path);
    }
}
