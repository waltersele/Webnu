<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Services\Sales\SalesLeadService;

class DashboardController extends Controller
{
    public function index(SalesLeadService $leads)
    {
        $visits = $leads->activeLeadsFor(auth()->user())->map(function ($visit) use ($leads) {
            $visit->products_count = $leads->productCount($visit);

            return $visit;
        });

        return view('sales.dashboard', [
            'visits' => $visits,
        ]);
    }
}
