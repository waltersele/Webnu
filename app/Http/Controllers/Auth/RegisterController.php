<?php

namespace App\Http\Controllers\Auth;

use App\Company;
use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use App\Services\CompanySlugService;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function index(Request $request)
    {
        return view('auth.register-webnu', [
            'prefillEmail' => $request->query('email', old('email', '')),
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'business_name' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'plan' => 'free',
            'onboarding_step' => 1,
            'trial_ends_at' => now()->addDays((int) config('plans.trial_days', 30)),
        ]);

        $businessName = trim($data['business_name'] ?? '') ?: 'Mi restaurante';
        $slug = app(CompanySlugService::class)->generateFromName($businessName);

        $company = Company::create([
            'name' => $businessName,
            'slug' => $slug,
            'template' => 'lumiere',
            'menu_type' => 1,
            'enabled' => false,
            'reservation' => false,
            'user_id' => $user->id,
        ]);

        Cookie::queue(Cookie::forever('selected_company', $company->id));

        return $user;
    }

    protected function registered(Request $request, $user)
    {
        return redirect()->route('admin.onboarding');
    }
}
