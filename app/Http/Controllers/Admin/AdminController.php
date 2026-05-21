<?php

namespace App\Http\Controllers\Admin;

use App\Company;
use App\Http\Controllers\Controller;
use App\Services\Admin\DashboardGuideService;
use App\Services\UserPlanService;
use Illuminate\Support\Facades\Cookie;

class AdminController extends Controller
{
    public function index(DashboardGuideService $guide, UserPlanService $plans)
    {
        if (auth()->user() && auth()->user()->isSuperAdmin()) {
            return redirect()->route('admin.platform.dashboard');
        }

        $user = auth()->user();
        $company = $this->resolveDashboardCompany($user);
        $dashboard = $guide->build($user, $company);
        $planPresentation = $plans->planPresentation($user);

        return view('admin.dashboard', [
            'dashboardCompany' => $company,
            'dashboard' => $dashboard,
            'planPresentation' => $planPresentation,
        ]);
    }

    protected function resolveDashboardCompany($user): ?Company
    {
        if (! $user) {
            return null;
        }

        $selectedId = Cookie::get('selected_company');
        if ($selectedId) {
            $company = Company::where('user_id', $user->id)
                ->where('id', (int) $selectedId)
                ->first();
            if ($company) {
                return $company;
            }
        }

        return Company::where('user_id', $user->id)->orderBy('updated_at', 'desc')->first();
    }
}
