<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\PlatformMetricsService;

class PlatformDashboardController extends Controller
{
    public function index(PlatformMetricsService $metrics)
    {
        $this->authorize('platform.access');

        return view('admin.platform.dashboard', [
            'metrics' => $metrics->summary(),
        ]);
    }
}
