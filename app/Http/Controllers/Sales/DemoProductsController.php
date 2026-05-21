<?php

namespace App\Http\Controllers\Sales;

use App\Company;
use App\Http\Controllers\Controller;
use App\Product;
use App\Services\Sales\SalesLeadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DemoProductsController extends Controller
{
    public function index(Company $company, SalesLeadService $leads)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);
        $this->authorize('update', $visit);

        $products = Product::query()
            ->whereHas('section', function ($query) use ($visit) {
                $query->where('company_id', $visit->id);
            })
            ->with('section')
            ->orderBy('section_id')
            ->orderBy('order')
            ->get();

        return view('sales.demo-products.index', [
            'visit' => $visit,
            'products' => $products,
            'photoSlotsRemaining' => $leads->demoPhotoSlotsRemaining($visit),
            'maxPhotos' => \App\PlatformSetting::salesDemoMaxPhotoProducts(),
        ]);
    }

    public function update(Request $request, Company $company, int $product, SalesLeadService $leads)
    {
        $visit = $leads->findActiveLeadFor(auth()->user(), $company->id);
        $this->authorize('update', $visit);

        $productModel = Product::query()
            ->whereHas('section', function ($query) use ($visit) {
                $query->where('company_id', $visit->id);
            })
            ->where('id', $product)
            ->firstOrFail();

        $this->authorize('update', $productModel);

        $request->validate([
            'photo' => 'nullable|image|max:5120',
            'clear_photo' => 'nullable|boolean',
        ]);

        if ($request->boolean('clear_photo')) {
            if ($productModel->image) {
                Storage::delete($productModel->image);
            }
            $productModel->image = null;
            $productModel->sales_demo_highlight = false;
            $productModel->save();

            return back()->with('flash', 'Foto eliminada.');
        }

        if ($request->hasFile('photo')) {
            if (! $productModel->sales_demo_highlight && $leads->demoPhotoSlotsRemaining($visit) <= 0) {
                return back()->withErrors([
                    'photo' => 'Solo puedes añadir fotos a ' . \App\PlatformSetting::salesDemoMaxPhotoProducts() . ' platos en la demo.',
                ]);
            }

            if ($productModel->image) {
                Storage::delete($productModel->image);
            }

            $productModel->image = $request->file('photo')->store('productos');
            $productModel->sales_demo_highlight = true;
            $productModel->save();

            return back()->with('flash', 'Foto actualizada para la demo.');
        }

        return back();
    }
}
