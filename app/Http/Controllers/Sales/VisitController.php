<?php

namespace App\Http\Controllers\Sales;

use App\Company;
use App\Http\Controllers\Controller;
use App\Services\Sales\SalesLeadService;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function store(Request $request, SalesLeadService $leads)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'city' => 'nullable|string|max:120',
        ]);

        $company = $leads->createLead(
            $request->user(),
            $data['name'],
            $data['city'] ?? null
        );

        return redirect()->route('sales.menu-scan.create', $company->id);
    }

    public function show(Company $company, SalesLeadService $leads)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);
        $this->authorize('update', $visit);

        $productCount = $visit->sections()->withCount('products')->get()->sum('products_count');
        $photoSlotsRemaining = $leads->demoPhotoSlotsRemaining($visit);
        $hasMenu = $productCount > 0;
        $progressStep = $hasMenu ? 2 : 1;

        return view('sales.visit.show', [
            'visit' => $visit,
            'productCount' => $productCount,
            'photoSlotsRemaining' => $photoSlotsRemaining,
            'hasMenu' => $hasMenu,
            'progressStep' => $progressStep,
            'menuUrl' => $visit->publicUrl(['sales_demo' => 1]),
            'importUrl' => route('sales.menu-scan.create', $visit->id),
        ]);
    }

    public function carta(Company $company, SalesLeadService $leads)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);

        return redirect($visit->publicUrl(['sales_demo' => 1]));
    }
}
