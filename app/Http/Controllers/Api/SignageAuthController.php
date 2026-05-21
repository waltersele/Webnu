<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserPlanService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SignageAuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        if (!$user->api_token) {
            $user->api_token = Str::random(80);
            $user->save();
        }

        $plans = app(UserPlanService::class);

        return response()->json([
            'token' => $user->api_token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'entitlements' => $plans->signageEntitlements($user),
        ]);
    }

    public function me(Request $request)
    {
        return $this->account($request);
    }

    public function account(Request $request)
    {
        $user = $request->user();
        $plans = app(UserPlanService::class);

        return response()->json(array_merge(
            [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ],
            $plans->signageEntitlements($user)
        ));
    }
}
