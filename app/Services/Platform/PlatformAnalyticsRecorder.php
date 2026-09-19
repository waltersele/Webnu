<?php

namespace App\Services\Platform;

use App\PlatformAnalyticsDaily;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class PlatformAnalyticsRecorder
{
    public const METRIC_LANDING_PAGEVIEW = 'landing_pageview';
    public const METRIC_LANDING_UNIQUE = 'landing_unique';
    public const METRIC_LANDING_CTA = 'landing_cta';
    public const METRIC_SIGNUP = 'signup';
    public const METRIC_MENU_VIEW = 'menu_view';

    public function tablesReady(): bool
    {
        try {
            return Schema::hasTable('platform_analytics_dailies');
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function increment(string $metric, string $dimension = '', int $amount = 1, ?\DateTimeInterface $day = null): void
    {
        if (! $this->tablesReady() || $amount < 1) {
            return;
        }

        $metric = $this->sanitizeMetric($metric);
        $dimension = $this->sanitizeDimension($dimension);
        $dayKey = ($day ?? now())->format('Y-m-d');

        $affected = PlatformAnalyticsDaily::query()
            ->where('day', $dayKey)
            ->where('metric', $metric)
            ->where('dimension', $dimension)
            ->increment('count', $amount);

        if ($affected === 0) {
            try {
                PlatformAnalyticsDaily::create([
                    'day' => $dayKey,
                    'metric' => $metric,
                    'dimension' => $dimension,
                    'count' => $amount,
                ]);
            } catch (\Throwable $e) {
                PlatformAnalyticsDaily::query()
                    ->where('day', $dayKey)
                    ->where('metric', $metric)
                    ->where('dimension', $dimension)
                    ->increment('count', $amount);
            }
        }
    }

    /**
     * @param  array{path?: string, referrer?: string, cta?: string, event?: string}  $payload
     */
    public function recordLandingBeacon(array $payload, string $ip, string $userAgent): void
    {
        if (! $this->tablesReady()) {
            return;
        }

        $event = $this->sanitizeMetric((string) ($payload['event'] ?? 'pageview'));
        $path = $this->sanitizePath((string) ($payload['path'] ?? '/'));
        $referrerHost = $this->referrerHost((string) ($payload['referrer'] ?? ''));
        $cta = $this->sanitizeDimension((string) ($payload['cta'] ?? ''));

        if ($event === 'cta' && $cta !== '') {
            $this->increment(self::METRIC_LANDING_CTA, $cta);
        } else {
            $this->increment(self::METRIC_LANDING_PAGEVIEW, $path);
            if ($referrerHost !== '') {
                $this->increment('landing_referrer', $referrerHost);
            }
        }

        $this->recordUniqueVisitor($ip, $userAgent);
    }

    public function recordSignup(string $source = 'email'): void
    {
        $this->increment(self::METRIC_SIGNUP, $this->sanitizeDimension($source));
    }

    public function recordMenuView(int $companyId, bool $fromQr = false): void
    {
        $this->increment(self::METRIC_MENU_VIEW, 'all');
        $this->increment(self::METRIC_MENU_VIEW, 'company:' . $companyId);
        if ($fromQr) {
            $this->increment('menu_view_qr', 'all');
        }
    }

    protected function recordUniqueVisitor(string $ip, string $userAgent): void
    {
        if (! Schema::hasTable('platform_analytics_visitors')) {
            return;
        }

        $dayKey = now()->format('Y-m-d');
        $hash = hash('sha256', $dayKey . '|' . $ip . '|' . Str::lower(Str::substr($userAgent, 0, 180)));

        try {
            $inserted = DB::table('platform_analytics_visitors')->insertOrIgnore([
                'day' => $dayKey,
                'visitor_hash' => $hash,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            return;
        }

        if ($inserted) {
            $this->increment(self::METRIC_LANDING_UNIQUE, '');
        }
    }

    protected function sanitizeMetric(string $metric): string
    {
        $metric = Str::lower(trim($metric));
        $metric = preg_replace('/[^a-z0-9_\-]/', '', $metric) ?: 'event';

        return Str::substr($metric, 0, 64);
    }

    protected function sanitizeDimension(string $dimension): string
    {
        $dimension = trim($dimension);
        $dimension = Str::substr($dimension, 0, 191);

        return $dimension;
    }

    protected function sanitizePath(string $path): string
    {
        $path = trim($path);
        if ($path === '' || $path[0] !== '/') {
            $path = '/' . ltrim($path, '/');
        }

        $path = parse_url($path, PHP_URL_PATH) ?: '/';
        $path = Str::substr($path, 0, 191);

        // No indexar admin/API
        if (Str::startsWith($path, ['/admin', '/api', '/stripe', '/stats', '/analytics'])) {
            return '/';
        }

        return $path === '' ? '/' : $path;
    }

    protected function referrerHost(string $referrer): string
    {
        $referrer = trim($referrer);
        if ($referrer === '') {
            return '';
        }

        $host = parse_url($referrer, PHP_URL_HOST);
        if (! is_string($host) || $host === '') {
            return '';
        }

        $host = Str::lower($host);
        $appHost = Str::lower((string) parse_url((string) config('app.url'), PHP_URL_HOST));
        if ($appHost !== '' && ($host === $appHost || Str::endsWith($host, '.' . $appHost))) {
            return '';
        }

        return Str::substr($host, 0, 191);
    }
}
