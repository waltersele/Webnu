<?php

namespace App\Http\Controllers;

use App\Services\Platform\PlatformAnalyticsRecorder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnalyticsBeaconController extends Controller
{
    public function store(Request $request, PlatformAnalyticsRecorder $recorder): Response
    {
        $payload = $request->all();
        if ($payload === [] && $request->getContent()) {
            $decoded = json_decode($request->getContent(), true);
            if (is_array($decoded)) {
                $payload = $decoded;
            }
        }

        $event = (string) ($payload['event'] ?? $payload['e'] ?? 'pageview');
        $path = (string) ($payload['path'] ?? $payload['p'] ?? '/');
        $referrer = (string) ($payload['referrer'] ?? $payload['r'] ?? '');
        $cta = (string) ($payload['cta'] ?? $payload['c'] ?? '');

        if (strlen($path) > 300 || strlen($referrer) > 500 || strlen($cta) > 80) {
            return response('payload too large', 413);
        }

        $recorder->recordLandingBeacon([
            'event' => $event,
            'path' => $path,
            'referrer' => $referrer,
            'cta' => $cta,
        ], (string) $request->ip(), (string) $request->userAgent());

        return response('ok', 204);
    }
}
