<?php

namespace App\Http\Middleware;

use Closure;

class VerifyPreAltaIngestKey
{
    public function handle($request, Closure $next)
    {
        $expectedKey = config('pre_alta.ingest_key');

        if (empty($expectedKey)) {
            return response()->json([
                'message' => 'Ingesta Pre-Alta no configurada (PRE_ALTA_INGEST_KEY).',
            ], 503);
        }

        $providedKey = $request->header('X-Pre-Alta-Key')
            ?: $request->header('X-Webnu-Demo-Key');

        if (! hash_equals((string) $expectedKey, (string) $providedKey)) {
            return response()->json([
                'message' => 'Clave de ingesta Pre-Alta no válida.',
            ], 401);
        }

        return $next($request);
    }
}
