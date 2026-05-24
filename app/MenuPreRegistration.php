<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MenuPreRegistration extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_CLAIMED = 'claimed';
    public const STATUS_PURGED = 'purged';

    protected $fillable = [
        'restaurant_name',
        'menu_json',
        'public_slug',
        'claim_token_hash',
        'status',
        'media_manifest',
        'source_meta',
        'claimed_user_id',
        'claimed_at',
        'expires_at',
    ];

    protected $casts = [
        'menu_json' => 'array',
        'media_manifest' => 'array',
        'source_meta' => 'array',
        'claimed_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function claimedUser()
    {
        return $this->belongsTo(User::class, 'claimed_user_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }

    public function storageDirectory(): string
    {
        return (string) $this->id;
    }

    public static function hashClaimToken(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }
}
