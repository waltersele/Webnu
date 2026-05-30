<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * OAuth outbound: TVPik redirige aquí; el usuario autoriza y vuelve con code=api_token.
 */
class TvpikConnectController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $redirectUri = (string) $request->query('redirect_uri', '');
        $state = (string) $request->query('state', '');

        if ($redirectUri === '' || $state === '') {
            abort(400, 'Faltan redirect_uri o state.');
        }

        if (! $this->isAllowedRedirect($redirectUri)) {
            abort(400, 'redirect_uri no permitido.');
        }

        if (Auth::check()) {
            return $this->authorizeAndRedirect(Auth::user(), $redirectUri, $state);
        }

        return view('integrations.tvpik-connect', [
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            'redirect_uri' => ['required', 'string'],
            'state' => ['required', 'string'],
        ]);

        if (! $this->isAllowedRedirect($data['redirect_uri'])) {
            abort(400, 'redirect_uri no permitido.');
        }

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Credenciales incorrectas.']);
        }

        Auth::login($user);

        return $this->authorizeAndRedirect($user, $data['redirect_uri'], $data['state']);
    }

    protected function authorizeAndRedirect(User $user, string $redirectUri, string $state): RedirectResponse
    {
        $token = $this->ensureSignageApiToken($user);

        $separator = str_contains($redirectUri, '?') ? '&' : '?';

        return redirect($redirectUri . $separator . http_build_query([
            'code' => $token,
            'state' => $state,
        ]));
    }

    protected function ensureSignageApiToken(User $user): string
    {
        if (! $user->api_token) {
            $user->api_token = Str::random(80);
            $user->save();
        }

        return (string) $user->api_token;
    }

    protected function isAllowedRedirect(string $uri): bool
    {
        $uri = trim($uri);
        $allowed = config('services.tvpik_oauth.allowed_redirect_uris', []);

        if (! empty($allowed)) {
            return in_array($uri, $allowed, true);
        }

        if (app()->environment('local')) {
            return str_contains($uri, '/integrations/webnu/callback');
        }

        return false;
    }
}
