<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuScanJob extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_REVIEW = 'review';
    public const STATUS_IMPORTED = 'imported';
    public const STATUS_FAILED = 'failed';

    /** Estados que consumen cupo de escaneo (IA procesó la carta correctamente). */
    public static function billableStatuses(): array
    {
        return [self::STATUS_REVIEW, self::STATUS_IMPORTED];
    }

    protected $fillable = [
        'company_id',
        'user_id',
        'status',
        'provider',
        'fallback_used',
        'source_files',
        'parsed_menu',
        'error_message',
    ];

    protected $casts = [
        'fallback_used' => 'boolean',
        'source_files' => 'array',
        'parsed_menu' => 'array',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function storageDirectory(): string
    {
        return config('menu_scan.storage_path', 'menu-scans') . '/' . $this->id;
    }
}
