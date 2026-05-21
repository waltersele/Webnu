<?php

namespace App\Services\Sales;

use App\Company;
use App\Services\Platform\PlatformMailConfigurator;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class SalesRepProvisioningService
{
    public function createOrInvite(string $name, string $email, bool $sendAccessEmail = true): User
    {
        $email = strtolower(trim($email));
        $existing = User::where('email', $email)->first();

        if ($existing) {
            if ($existing->isSalesRep()) {
                throw ValidationException::withMessages([
                    'email' => 'Este email ya tiene acceso comercial.',
                ]);
            }

            return $this->grantSalesRepRole($existing, $sendAccessEmail);
        }

        return DB::transaction(function () use ($name, $email, $sendAccessEmail) {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make(Str::random(32)),
                'plan' => 'free',
                'onboarding_step' => 6,
                'onboarding_completed_at' => now(),
            ]);

            $this->assignSalesRepRole($user);

            if ($sendAccessEmail) {
                $this->sendAccessEmail($user);
            }

            return $user;
        });
    }

    public function grantSalesRepRole(User $user, bool $sendAccessEmail = false): User
    {
        $this->assignSalesRepRole($user);

        if ($sendAccessEmail) {
            $this->sendAccessEmail($user);
        }

        return $user->fresh();
    }

    public function sendAccessEmail(User $user): void
    {
        if (! $user->isSalesRep()) {
            throw ValidationException::withMessages([
                'email' => 'El usuario no tiene rol comercial.',
            ]);
        }

        app(PlatformMailConfigurator::class)->apply();

        $token = Password::broker()->createToken($user);
        $user->sendPasswordResetNotification($token);
    }

    public function activeVisitsCount(User $rep): int
    {
        return (int) Company::query()
            ->where('sales_rep_user_id', $rep->id)
            ->whereNull('sales_converted_at')
            ->count();
    }

    protected function assignSalesRepRole(User $user): void
    {
        $role = Role::findByName('sales-rep', 'web');
        if (! $user->hasRole($role)) {
            $user->assignRole($role);
        }
    }
}
