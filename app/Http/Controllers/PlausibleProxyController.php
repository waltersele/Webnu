<?php

namespace App\Http\Controllers;

use App\Services\Platform\MeasurementSettingsService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class PlausibleProxyController extends Controller
{
    public function script(MeasurementSettingsService $measurement)
    {
        $upstream = $this->upstreamBase($measurement);

        if ($upstream === null) {
            return response()->json(['error' => 'Plausible upstream not configured'], 404);
        }

        try {
            $response = Http::timeout(5)
                ->withHeaders($this->forwardHeaders(request()))
                ->get(rtrim($upstream, '/') . '/js/script.js');
        } catch (\Throwable $e) {
            return response('Upstream unavailable', 502);
        }

        if (! $response->successful()) {
            return response('Upstream error', $response->status() ?: 502);
        }

        return response($response->body(), 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    public function event(Request $request, MeasurementSettingsService $measurement)
    {
        $upstream = $this->upstreamBase($measurement);

        if ($upstream === null) {
            return response()->json(['error' => 'Plausible upstream not configured'], 404);
        }

        $url = rtrim($upstream, '/') . '/api/event';
        $headers = array_merge($this->forwardHeaders($request), [
            'Content-Type' => $request->header('Content-Type', 'application/json'),
        ]);

        try {
            if ($request->isMethod('get')) {
                $response = Http::timeout(5)
                    ->withHeaders($headers)
                    ->get($url, $request->query());
            } else {
                $response = Http::timeout(5)
                    ->withHeaders($headers)
                    ->withBody($request->getContent(), $request->header('Content-Type', 'text/plain'))
                    ->post($url);
            }
        } catch (\Throwable $e) {
            return response()->json(['error' => 'Upstream unavailable'], 502);
        }

        return response($response->body(), $response->status(), [
            'Content-Type' => $response->header('Content-Type') ?: 'text/plain',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function upstreamBase(MeasurementSettingsService $measurement): ?string
    {
        $upstream = $measurement->plausibleUpstreamUrl();

        if ($upstream === null || ! Str::startsWith($upstream, ['http://', 'https://'])) {
            return null;
        }

        return $upstream;
    }

    /** @return array<string, string> */
    private function forwardHeaders(Request $request): array
    {
        $headers = [
            'User-Agent' => (string) $request->userAgent(),
            'Accept' => (string) $request->header('Accept', '*/*'),
        ];

        $xff = $request->header('X-Forwarded-For');
        $clientIp = $request->ip();

        if ($xff) {
            $headers['X-Forwarded-For'] = $xff . ($clientIp ? ', ' . $clientIp : '');
        } elseif ($clientIp) {
            $headers['X-Forwarded-For'] = $clientIp;
        }

        return $headers;
    }
}
