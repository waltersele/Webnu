<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SalesHandoff extends Model
{
    public const STATUS_SENT = 'sent';

    public const STATUS_CONVERTED = 'converted';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'sales_rep_user_id',
        'company_id',
        'prospect_email',
        'prospect_name',
        'plan_key',
        'trial_days',
        'restaurant_user_id',
        'status',
        'sent_at',
        'converted_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'converted_at' => 'datetime',
        'trial_days' => 'integer',
    ];

    public function salesRep()
    {
        return $this->belongsTo(User::class, 'sales_rep_user_id');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function restaurantUser()
    {
        return $this->belongsTo(User::class, 'restaurant_user_id');
    }
}
