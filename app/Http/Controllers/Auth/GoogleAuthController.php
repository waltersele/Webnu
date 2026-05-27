<?php

namespace App\Http\Controllers\Auth;

use App\Company;
use App\Http\Controllers\Controller;
use App\PlatformSetting;
use App\Providers\RouteServiceProvider;
use App\Services\Platform\PlatformGoogleConfigurator;
use App\Services\CompanySlugService;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class GoogleAuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function redirect(): RedirectResponse
    {
        $this->ensureGoogleOAuthConfigured();

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        $this->ensureGoogleOAuthConfigured();

        $googleUser = Socialite::driver('google')->user();

        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if (! $user) {
            $displayName = $googleUser->getName() ?: Str::before($googleUser->getEmail(), '@');
            $user = User::create([
                'name' => $displayName,
                'slug' => User::generateUniqueSlug($displayName),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => Hash::make(Str::random(48)),
                'plan' => 'free',
                'onboarding_step' => 1,
                'trial_ends_at' => now()->addDays((int) config('plans.trial_days', 30)),
                'trial_plan_key' => config('plans.trial_tier', 'pro'),
                'email_verified_at' => now(),
            ]);

            $slug = app(CompanySlugService::class)->generateFromName($displayName, null, null, $user->slug);
            $company = Company::create([
                'name' => $displayName,
                'slug' => $slug,
                'template' => 'lumiere',
                'menu_type' => 1,
                'enabled' => false,
                'reservation' => false,
                'user_id' => $user->id,
            ]);
            Cookie::queue(Cookie::forever('selected_company', $company->id));
        } else {
            if (! $user->google_id) {
                $user->google_id = $googleUser->getId();
                $user->save();
            }
        }

        Auth::login($user, true);

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    protected function ensureGoogleOAuthConfigured(): void
    {
        app(PlatformGoogleConfigurator::class)->apply();

        if (! PlatformSetting::hasGoogleOAuth()) {
            abort(503, 'Inicio de sesión con Google no configurado. Configúralo en Admin → Plataforma → Configuración.');
        }
    }
}
