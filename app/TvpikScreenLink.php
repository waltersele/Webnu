<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TvpikScreenLink extends Model
{
    protected $fillable = [
        'user_id',
        'company_id',
        'tvpik_screen_id',
        'tvpik_screen_name',
        'tvpik_gallery_id',
        'template_key',
        'published_url',
        'sync_version',
        'last_synced_at',
        'last_error',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function templateMeta(): ?array
    {
        $templates = config('tvpik_templates.templates', []);

        return $templates[$this->template_key] ?? null;
    }
}
