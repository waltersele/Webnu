<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PlatformAnalyticsDaily extends Model
{
    protected $table = 'platform_analytics_dailies';

    protected $fillable = [
        'day',
        'metric',
        'dimension',
        'count',
    ];

    protected $casts = [
        'day' => 'date',
        'count' => 'integer',
    ];
}
