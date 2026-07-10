<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerifyContentConnectorSignature
{
    public function handle(Request $request, Closure $next)
    {
        $secret = config('blog.connector.secret');

        if (empty($secret)) {
            return response()->json([
                'message' => 'Content connector not configured. Añade el secreto en Admin → Plataforma → Configuración.',
            ], 503);
        }

        $header = config('blog.connector.signature_header', 'X-Connector-Signature');
        $provided = $this->normalizeSignature((string) $request->header($header));

        if ($provided === '' || ! $this->isValidHexSignature($provided)) {
            return response()->json([
                'message' => 'Invalid signature.',
            ], 401);
        }

        $expectedHex = hash_hmac('sha256', $request->getContent(), (string) $secret);

        if (! hash_equals($expectedHex, $provided)) {
            return response()->json([
                'message' => 'Invalid signature.',
            ], 401);
        }

        return $next($request);
    }

    private function normalizeSignature(string $provided): string
    {
        $provided = trim($provided);

        if (stripos($provided, 'sha256=') === 0) {
            return substr($provided, 7);
        }

        return $provided;
    }

    private function isValidHexSignature(string $signature): bool
    {
        return (bool) preg_match('/^[a-f0-9]{64}$/i', $signature);
    }
}
