<?php

namespace App\Http\Middleware;

use Closure;

class VerifyDigitalSignageApp
{
    public function handle($request, Closure $next)
    {
        $expectedKey = config('digital_signage.app_key');

        if (empty($expectedKey)) {
            return $next($request);
        }

        $providedKey = $request->header('X-Digital-Signage-Key');

        if (!hash_equals((string) $expectedKey, (string) $providedKey)) {
            return response()->json([
                'message' => 'Clave de aplicación de digital signage no válida.',
            ], 401);
        }

        return $next($request);
    }
}
