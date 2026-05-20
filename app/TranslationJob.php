<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TranslationJob extends Model
{
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_DONE = 'done';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'company_id',
        'user_id',
        'target_locale',
        'status',
        'provider',
        'items_total',
        'items_done',
        'error_message',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
