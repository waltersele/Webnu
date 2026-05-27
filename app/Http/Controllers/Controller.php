<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cookie;
use App\Company;
use Illuminate\Support\Facades\Auth;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private $user;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            if($this->user)
            {
                $plans = app(\App\Services\UserPlanService::class);
                View::share('planFeatures', array_merge(
                    $plans->featureFlags($this->user),
                    [
                        'plan_key' => $plans->planKey($this->user),
                        'plan_label' => $plans->tier($this->user)['label'] ?? 'Gratis',
                        'billing_url' => route('admin.settings'),
                    ]
                ));

                $userId = auth()->id();
                $companies = Company::where('user_id', $userId)->orderBy('name')->get();

                $selectedId = Cookie::get('selected_company');
                $selectedCompany = $selectedId && $companies->contains('id', (int) $selectedId)
                    ? $companies->firstWhere('id', (int) $selectedId)
                    : ($companies->isNotEmpty() ? $companies->first() : null);
                View::share('upgradeTriggers', app(\App\Services\UpgradeTriggerService::class)
                    ->contextFor($this->user, $selectedCompany, $request));
                View::share('available_companies', $companies);
                View::share('available_menus', $selectedCompany
                    ? $selectedCompany->menus()->orderBy('position')->get(['id', 'company_id', 'name', 'slug', 'enabled', 'position'])
                    : collect());
                View::share('planPresentation', $plans->planPresentation($this->user));

                if ($selectedId && $companies->contains('id', (int) $selectedId)) {
                    View::share('selected_company', (int) $selectedId);
                } elseif ($companies->isNotEmpty()) {
                    $firstId = (int) $companies->first()->id;
                    Cookie::queue(Cookie::forever('selected_company', $firstId));
                    View::share('selected_company', $firstId);
                }
            }

            return $next($request);
        });
    }

    public function user()
    {
        return Auth::user();
    }
}
