<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreAlta\PreAltaClaimRequest;
use App\MenuPreRegistration;
use App\Services\PreAlta\PreAltaClaimService;
use Illuminate\Http\Request;

class PreAltaClaimController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function show(string $token)
    {
        $registration = $this->resolvePendingByToken($token);
        if (! $registration) {
            return view('pre-alta.claim', [
                'error' => 'Este enlace no es válido, ya fue utilizado o ha caducado.',
                'registration' => null,
                'token' => null,
            ]);
        }

        return view('pre-alta.claim', [
            'error' => null,
            'registration' => $registration,
            'token' => $token,
        ]);
    }

    public function store(PreAltaClaimRequest $request, string $token, PreAltaClaimService $claimService)
    {
        $registration = $this->resolvePendingByToken($token);
        if (! $registration) {
            return redirect()
                ->route('pre-alta.claim.show', ['token' => $token])
                ->withErrors(['email' => 'Este enlace no es válido, ya fue utilizado o ha caducado.']);
        }

        try {
            $claimService->claim($token, $request->only('name', 'email', 'password'));
        } catch (\RuntimeException $e) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['email' => $e->getMessage()]);
        }

        return redirect()->route('admin.tvpik.index');
    }

    protected function resolvePendingByToken(string $token): ?MenuPreRegistration
    {
        if (strlen($token) < 32) {
            return null;
        }

        $hash = MenuPreRegistration::hashClaimToken($token);

        $registration = MenuPreRegistration::pending()
            ->where('claim_token_hash', $hash)
            ->first();

        if (! $registration || $registration->isExpired()) {
            return null;
        }

        return $registration;
    }
}
