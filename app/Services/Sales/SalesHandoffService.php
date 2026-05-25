<?php

namespace App\Services\Sales;

use App\Company;
use App\PlatformSetting;
use App\SalesHandoff;
use App\User;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SalesHandoffService
{
    public function sendAccess(
        User $salesRep,
        Company $company,
        string $prospectEmail,
        string $prospectName,
        ?string $planKey = null,
        ?int $trialDays = null
    ): SalesHandoff {
        if (! $company->isActiveSalesLead() || (int) $company->sales_rep_user_id !== (int) $salesRep->id) {
            throw ValidationException::withMessages([
                'email' => 'Esta visita ya no está disponible para cierre.',
            ]);
        }

        $email = strtolower(trim($prospectEmail));
        $existing = User::where('email', $email)->first();
        if ($existing) {
            throw ValidationException::withMessages([
                'email' => 'Ya existe una cuenta con este email. Usa otro correo o pide al restaurante que recupere su contraseña.',
            ]);
        }

        $planKey = $planKey ?: PlatformSetting::salesHandoffPlanKey();
        $trialDays = $trialDays ?: PlatformSetting::salesHandoffTrialDays();

        if (! array_key_exists($planKey, config('plans.tiers', []))) {
            throw ValidationException::withMessages([
                'plan_key' => 'Plan no válido.',
            ]);
        }

        return DB::transaction(function () use ($salesRep, $company, $email, $prospectName, $planKey, $trialDays) {
            $password = Str::random(32);

            $restaurant = User::create([
                'name' => $prospectName,
                'slug' => User::generateUniqueSlug($prospectName),
                'email' => $email,
                'password' => Hash::make($password),
                'plan' => 'free',
                'trial_ends_at' => now()->addDays($trialDays),
                'trial_plan_key' => $planKey,
                'onboarding_step' => 6,
                'onboarding_completed_at' => now(),
            ]);

            $company->user_id = $restaurant->id;
            $company->enabled = true;
            $company->sales_converted_at = now();
            $company->save();

            $handoff = SalesHandoff::create([
                'sales_rep_user_id' => $salesRep->id,
                'company_id' => $company->id,
                'prospect_email' => $email,
                'prospect_name' => $prospectName,
                'plan_key' => $planKey,
                'trial_days' => $trialDays,
                'restaurant_user_id' => $restaurant->id,
                'status' => SalesHandoff::STATUS_CONVERTED,
                'sent_at' => now(),
                'converted_at' => now(),
            ]);

            $token = Password::broker()->createToken($restaurant);
            $restaurant->sendPasswordResetNotification($token);

            Cookie::queue(Cookie::forever('selected_company', $company->id));

            return $handoff;
        });
    }
}
