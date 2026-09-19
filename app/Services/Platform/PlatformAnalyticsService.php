<?php

namespace App\Services\Platform;

use App\Company;
use App\PlatformAnalyticsDaily;
use App\SalesHandoff;
use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlatformAnalyticsService
{
    /** @var PlatformMetricsService */
    protected $metrics;

    /** @var PlatformAnalyticsRecorder */
    protected $recorder;

    public function __construct(PlatformMetricsService $metrics, PlatformAnalyticsRecorder $recorder)
    {
        $this->metrics = $metrics;
        $this->recorder = $recorder;
    }

    public function dashboard(int $days = 30): array
    {
        $days = max(7, min(90, $days));
        $from = now()->subDays($days - 1)->startOfDay();
        $to = now()->endOfDay();
        $prevFrom = (clone $from)->subDays($days);
        $prevTo = (clone $from)->subSecond();

        $billing = $this->metrics->summary();
        $ready = $this->recorder->tablesReady();

        $signupsPeriod = User::query()->whereBetween('created_at', [$from, $to])->count();
        $signupsPrev = User::query()->whereBetween('created_at', [$prevFrom, $prevTo])->count();
        $onboardingDone = User::query()
            ->whereBetween('created_at', [$from, $to])
            ->whereNotNull('onboarding_completed_at')
            ->count();

        $menuViewsLifetime = 0;
        $menuViewsQr = 0;
        $landingViews = 0;
        $landingUniques = 0;
        $ctaClicks = 0;
        $series = [];
        $topPaths = [];
        $topReferrers = [];
        $topCtas = [];
        $topCompanies = [];

        if ($ready) {
            $landingViews = $this->sumMetric(PlatformAnalyticsRecorder::METRIC_LANDING_PAGEVIEW, $from, $to);
            $landingUniques = $this->sumMetric(PlatformAnalyticsRecorder::METRIC_LANDING_UNIQUE, $from, $to);
            $ctaClicks = $this->sumMetric(PlatformAnalyticsRecorder::METRIC_LANDING_CTA, $from, $to);
            $menuViewsLifetime = $this->sumMetric(PlatformAnalyticsRecorder::METRIC_MENU_VIEW, $from, $to, 'all');
            $menuViewsQr = $this->sumMetric('menu_view_qr', $from, $to, 'all');

            $series = $this->buildSeries($from, $days);
            $topPaths = $this->topDimensions(PlatformAnalyticsRecorder::METRIC_LANDING_PAGEVIEW, $from, $to, 10);
            $topReferrers = $this->topDimensions('landing_referrer', $from, $to, 10);
            $topCtas = $this->topDimensions(PlatformAnalyticsRecorder::METRIC_LANDING_CTA, $from, $to, 10);
        }

        if (Schema::hasColumn('companies', 'menu_views')) {
            $topCompanies = Company::query()
                ->where(function ($q) {
                    $q->whereNull('sales_rep_user_id')->orWhereNotNull('sales_converted_at');
                })
                ->orderByDesc('menu_views')
                ->limit(10)
                ->get(['id', 'name', 'slug', 'menu_views', 'user_id']);
        }

        $handoffsMonth = 0;
        $handoffsConverted = 0;
        if (Schema::hasTable('sales_handoffs')) {
            $handoffsMonth = SalesHandoff::query()
                ->where('created_at', '>=', now()->startOfMonth())
                ->count();
            $handoffsConverted = SalesHandoff::query()
                ->where('created_at', '>=', now()->startOfMonth())
                ->where('status', 'converted')
                ->count();
        }

        $signupSources = $ready
            ? $this->topDimensions(PlatformAnalyticsRecorder::METRIC_SIGNUP, $from, $to, 5)
            : collect();

        return [
            'days' => $days,
            'from' => $from,
            'to' => $to,
            'tables_ready' => $ready,
            'billing' => $billing,
            'kpis' => [
                'landing_views' => $landingViews,
                'landing_uniques' => $landingUniques,
                'cta_clicks' => $ctaClicks,
                'signups' => $signupsPeriod,
                'signups_prev' => $signupsPrev,
                'signups_delta_pct' => $this->deltaPct($signupsPeriod, $signupsPrev),
                'onboarding_completed' => $onboardingDone,
                'onboarding_rate_pct' => $signupsPeriod > 0
                    ? round(($onboardingDone / $signupsPeriod) * 100, 1)
                    : 0.0,
                'menu_views' => $menuViewsLifetime,
                'menu_views_qr' => $menuViewsQr,
                'menu_views_lifetime' => (int) (Schema::hasColumn('companies', 'menu_views')
                    ? Company::query()->sum('menu_views')
                    : 0),
                'handoffs_month' => $handoffsMonth,
                'handoffs_converted' => $handoffsConverted,
                'handoff_rate_pct' => $handoffsMonth > 0
                    ? round(($handoffsConverted / $handoffsMonth) * 100, 1)
                    : 0.0,
            ],
            'series' => $series,
            'top_paths' => $topPaths,
            'top_referrers' => $topReferrers,
            'top_ctas' => $topCtas,
            'top_companies' => $topCompanies,
            'signup_sources' => $signupSources,
            'funnel' => $this->funnel($from, $to, $landingUniques ?: $landingViews, $signupsPeriod, $onboardingDone, $billing),
        ];
    }

    protected function funnel(
        Carbon $from,
        Carbon $to,
        int $landing,
        int $signups,
        int $onboarding,
        array $billing
    ): array {
        $paid = (int) ($billing['active_subscriptions'] ?? 0);
        $trialing = (int) ($billing['trialing'] ?? 0);

        return [
            ['label' => 'Visitas landing (periodo)', 'value' => $landing],
            ['label' => 'Altas nuevas', 'value' => $signups],
            ['label' => 'Onboarding completado', 'value' => $onboarding],
            ['label' => 'En trial (stock)', 'value' => $trialing],
            ['label' => 'Suscripciones activas (stock)', 'value' => $paid],
        ];
    }

    protected function sumMetric(string $metric, Carbon $from, Carbon $to, ?string $dimension = null): int
    {
        $q = PlatformAnalyticsDaily::query()
            ->where('metric', $metric)
            ->whereBetween('day', [$from->toDateString(), $to->toDateString()]);

        if ($dimension !== null) {
            $q->where('dimension', $dimension);
        }

        return (int) $q->sum('count');
    }

    protected function topDimensions(string $metric, Carbon $from, Carbon $to, int $limit): Collection
    {
        return PlatformAnalyticsDaily::query()
            ->select('dimension', DB::raw('SUM(count) as total'))
            ->where('metric', $metric)
            ->whereBetween('day', [$from->toDateString(), $to->toDateString()])
            ->where('dimension', '!=', '')
            ->groupBy('dimension')
            ->orderByDesc('total')
            ->limit($limit)
            ->get();
    }

    protected function buildSeries(Carbon $from, int $days): array
    {
        $end = now()->startOfDay();
        $labels = [];
        $pageviews = [];
        $uniques = [];
        $signups = [];
        $menuViews = [];

        $rows = PlatformAnalyticsDaily::query()
            ->whereIn('metric', [
                PlatformAnalyticsRecorder::METRIC_LANDING_PAGEVIEW,
                PlatformAnalyticsRecorder::METRIC_LANDING_UNIQUE,
                PlatformAnalyticsRecorder::METRIC_MENU_VIEW,
            ])
            ->whereBetween('day', [$from->toDateString(), $end->toDateString()])
            ->get()
            ->groupBy(function ($row) {
                return $row->day->format('Y-m-d') . '|' . $row->metric . '|' . $row->dimension;
            });

        $signupByDay = User::query()
            ->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as c'))
            ->where('created_at', '>=', $from)
            ->groupBy('d')
            ->pluck('c', 'd');

        for ($i = 0; $i < $days; $i++) {
            $day = (clone $from)->addDays($i)->startOfDay();
            $key = $day->format('Y-m-d');
            $labels[] = $day->format('d/m');

            $pv = 0;
            foreach ($rows as $groupKey => $group) {
                if (strpos($groupKey, $key . '|' . PlatformAnalyticsRecorder::METRIC_LANDING_PAGEVIEW . '|') === 0) {
                    $pv += (int) $group->sum('count');
                }
            }
            $pageviews[] = $pv;

            $uqKey = $key . '|' . PlatformAnalyticsRecorder::METRIC_LANDING_UNIQUE . '|';
            $uniques[] = isset($rows[$uqKey]) ? (int) $rows[$uqKey]->sum('count') : 0;

            $mvKey = $key . '|' . PlatformAnalyticsRecorder::METRIC_MENU_VIEW . '|all';
            $menuViews[] = isset($rows[$mvKey]) ? (int) $rows[$mvKey]->sum('count') : 0;

            $signups[] = (int) ($signupByDay[$key] ?? 0);
        }

        return [
            'labels' => $labels,
            'pageviews' => $pageviews,
            'uniques' => $uniques,
            'signups' => $signups,
            'menu_views' => $menuViews,
        ];
    }

    protected function deltaPct(int $current, int $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}
