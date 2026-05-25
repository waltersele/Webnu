<?php

namespace App;

use App\Services\UserPlanService;
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
        'name', 'slug', 'email', 'password', 'plan', 'trial_plan_key', 'tvpik_extra_screens', 'onboarding_step', 'onboarding_completed_at',
        'profile_wizard_dismissed_at',
        'api_token', 'tvpik_api_token', 'tvpik_connected_at', 'tvpik_org_id',
        'stripe_id', 'card_brand', 'card_last_four', 'trial_ends_at',
        'manual_plan_key', 'manual_plan_until', 'manual_plan_note',
        'phone', 'legal_name', 'tax_id', 'billing_address', 'billing_postal_code', 'billing_city', 'billing_country',
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
        'manual_plan_until' => 'datetime',
        'onboarding_completed_at' => 'datetime',
        'profile_wizard_dismissed_at' => 'datetime',
        'tvpik_connected_at' => 'datetime',
    ];

    public function setTvpikApiTokenAttribute(?string $value): void
    {
        $this->attributes['tvpik_api_token'] = $value ? encrypt($value) : null;
    }

    public function plainTvpikApiToken(): ?string
    {
        if (empty($this->attributes['tvpik_api_token'])) {
            return null;
        }

        try {
            return decrypt($this->attributes['tvpik_api_token']);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function isTvpikConnected(): bool
    {
        return $this->tvpik_connected_at !== null && $this->plainTvpikApiToken() !== null;
    }

    public function tvpikScreenLinks()
    {
        return $this->hasMany(TvpikScreenLink::class);
    }

    //Sobrescribimos el metodo de envio de email para que coja formato a nuestro gusto
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new CustomResetPasswordNotification($token));
    }

    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    public function resolveSlug(): string
    {
        if (!empty($this->slug)) {
            return $this->slug;
        }

        $newSlug = self::generateUniqueSlug($this->name ?: ($this->legal_name ?: ('user-' . ($this->id ?? 'tmp'))), $this->id);
        $this->slug = $newSlug;

        if ($this->exists && $this->id) {
            \Illuminate\Support\Facades\DB::table($this->getTable())
                ->where('id', $this->id)
                ->update(['slug' => $newSlug]);
        }

        return $newSlug;
    }

    public static function generateUniqueSlug(?string $source, ?int $ignoreId = null): string
    {
        $base = \Illuminate\Support\Str::slug((string) $source);
        if ($base === '') {
            $base = 'user-' . ($ignoreId ?: \Illuminate\Support\Str::random(6));
        }
        $base = substr($base, 0, 110);

        $candidate = $base;
        $i = 2;
        while (self::where('slug', $candidate)
            ->when($ignoreId, function ($q) use ($ignoreId) { $q->where('id', '!=', $ignoreId); })
            ->exists()) {
            $candidate = $base . '-' . $i++;
        }
        return $candidate;
    }

    public function isSuperAdmin(): bool
    {
        $emails = config('platform.super_admin_emails', []);
        if (in_array($this->email, $emails, true)) {
            return true;
        }

        return $this->hasRole('super-admin');
    }

    public function isSalesRep(): bool
    {
        return $this->hasRole('sales-rep');
    }

    public function salesHandoffs()
    {
        return $this->hasMany(SalesHandoff::class, 'sales_rep_user_id');
    }

    public function hasActiveSubscription(): bool
    {
        if ($this->onGenericTrial()) {
            return true;
        }

        $names = array_values(config('billing.subscription_names', []));
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
        $names = array_values(config('billing.subscription_names', []));
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

    public function hasCompletedOnboarding(): bool
    {
        return $this->onboarding_completed_at !== null;
    }

    public function planService(): UserPlanService
    {
        return app(UserPlanService::class);
    }

    public function planKey(): string
    {
        return $this->planService()->planKey($this);
    }
}
