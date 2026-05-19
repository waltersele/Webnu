<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\CustomResetPasswordNotification;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    use Notifiable, HasRoles, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'api_token', 'stripe_id', 'card_brand', 'card_last_four', 'trial_ends_at',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'api_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    //Sobrescribimos el metodo de envio de email para que coja formato a nuestro gusto
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function isSuperAdmin(): bool
    {
        $emails = config('platform.super_admin_emails', []);
        if (in_array($this->email, $emails, true)) {
            return true;
        }

        return $this->hasRole('super-admin');
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->onGenericTrial()) {
            return true;
        }

        $names = array_values(config('platform.subscription_names', []));
        foreach ($names as $name) {
            if ($this->subscribed($name)) {
                return true;
            }
        }

        return $this->subscriptions()
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>', now());
            })
            ->exists();
    }

    public function primarySubscription()
    {
        $names = array_values(config('platform.subscription_names', []));
        foreach ($names as $name) {
            $subscription = $this->subscription($name);
            if ($subscription) {
                return $subscription;
            }
        }

        return $this->subscriptions()->latest('id')->first();
    }

    public function scopeWithBillingSummary($query)
    {
        return $query->withCount('companies')->with('subscriptions');
    }
}
