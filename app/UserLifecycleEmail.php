<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserLifecycleEmail extends Model
{
    protected $fillable = [
        'user_id',
        'template_key',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
