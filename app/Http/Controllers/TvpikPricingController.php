<?php

namespace App\Http\Controllers;

use App\Services\Billing\TvpikScreenPricing;
use Illuminate\Http\Request;

class TvpikPricingController extends Controller
{
    public function quote(Request $request, TvpikScreenPricing $pricing)
    {
        $planTier = $request->input('tier', 'pro');
        if (! in_array($planTier, ['pro', 'plus'], true)) {
            $planTier = 'pro';
        }

        $cycle = $request->input('cycle', 'monthly');
        if (! in_array($cycle, ['monthly', 'yearly'], true)) {
            $cycle = 'monthly';
        }

        $screens = $request->has('screens')
            ? (int) $request->input('screens')
            : 0;

        return response()->json($pricing->quote($planTier, $screens, $cycle));
    }
}
