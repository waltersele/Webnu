<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\PlatformAnalyticsService;
use Illuminate\Http\Request;

class PlatformAnalyticsController extends Controller
{
    public function index(Request $request, PlatformAnalyticsService $analytics)
    {
        $this->authorize('platform.access');

        $days = (int) $request->query('days', 30);

        return view('admin.platform.analytics', [
            'analytics' => $analytics->dashboard($days),
        ]);
    }
}
