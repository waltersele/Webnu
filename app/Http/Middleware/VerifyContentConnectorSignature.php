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
                'message' => 'Content connector not configured (CONTENT_CONNECTOR_SECRET).',
            ], 503);
        }

        $header = config('blog.connector.signature_header', 'X-Connector-Signature');
        $prefix = config('blog.connector.signature_prefix', 'sha256=');
        $provided = (string) $request->header($header);
        $expected = $prefix . hash_hmac('sha256', $request->getContent(), (string) $secret);

        if ($provided === '' || ! hash_equals($expected, $provided)) {
            return response()->json([
                'message' => 'Invalid signature.',
            ], 401);
        }

        return $next($request);
    }
}
