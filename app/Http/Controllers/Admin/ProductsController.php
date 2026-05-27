<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Allergen;
use App\Product;
use App\Section;
use App\Services\AllergenCatalogService;
use App\Services\ProductVideoOptimizer;
use App\Services\UserPlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Storage;

class ProductsController extends Controller
{
    public function index()
    {
    }

    public function edit(Product $product)
    {
        $product->load(['section.company', 'allergens']);
        $this->authorize('update', $product);

        app(AllergenCatalogService::class)->sync();
        $allergens = Allergen::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'allergens'));
    }

    public function store(Request $request, UserPlanService $plans)
    {
        if ($request->hasFile('product_add_video')) {
            $plans->assertCanUseVideos($request->user());
        }

        if ($request->product_add_image != null) {
            $plans->assertCanUseProductPhotos($request->user());
        }

        $rules = [
            'product_add_name' => 'required',
            'product_add_price_unit' => 'required',
            'product_add_section_id' => 'required|integer',
        ];
        $rules = array_merge($rules, $this->videoValidationRules('product_add_video'));
        $this->validate($request, $rules);

        $section = $this->findOwnedSection($request->get('product_add_section_id'));
        $company = $section->company;
        $plans->assertCanAddProduct($request->user(), $company);

        $productImagePath = null;
        if ($request->product_add_image != null) {
            $productImagePath = $request->product_add_image->store('productos');
        }

        $productVideoPath = null;
        if ($request->hasFile('product_add_video')) {
            $productVideoPath = app(ProductVideoOptimizer::class)->storeOptimized(
                $request->file('product_add_video')
            );
        }

        $sectionOrder = (int) Product::where('section_id', $section->id)->max('order') + 1;
        $weightSale = $request->get('product_add_weight_sale') != null;

        $product = Product::create([
            'name' => $request->get('product_add_name'),
            'description' => $request->get('product_add_description'),
            'image' => $productImagePath,
            'video' => $productVideoPath,
            'price_unit' => $request->get('product_add_price_unit'),
            'price_portion' => $request->get('product_add_price_portion'),
            'order' => $sectionOrder,
            'individual_sale' => $request->get('product_add_individual_sale') != null,
            'weight_sale' => $weightSale,
            'weight_unit_label' => $this->weightUnitLabelFromRequest($request, 'product_add', $weightSale),
            'highlight' => $this->highlightFromRequest($request, 'product_add'),
            'enabled' => $request->get('product_add_enabled') != null,
            'section_id' => $section->id,
        ]);

        $product->allergens()->sync($request->get('allergens', []));

        return redirect()->to(url()->previous() . '#section-' . $section->id)
            ->with('flash', 'Producto añadido correctamente');
    }

    public function update(Request $request, UserPlanService $plans)
    {
        if ($request->hasFile('product_modify_video')) {
            $plans->assertCanUseVideos($request->user());
        }

        $rules = [
            'product_modify_name' => 'required',
            'product_modify_price_unit' => 'required',
            'product_id' => 'required|integer',
        ];
        $rules = array_merge($rules, $this->videoValidationRules('product_modify_video'));
        $this->validate($request, $rules);

        $product = Product::with('section.company')->findOrFail($request->get('product_id'));
        $this->authorize('update', $product);

        $product->name = $request->get('product_modify_name');
        $product->description = $request->get('product_modify_description');
        $product->price_unit = $request->get('product_modify_price_unit');
        $product->price_portion = $request->get('product_modify_price_portion');
        $weightSale = $request->get('product_modify_weight_sale') != null;
        $product->individual_sale = $request->get('product_modify_individual_sale') != null;
        $product->weight_sale = $weightSale;
        $product->weight_unit_label = $this->weightUnitLabelFromRequest($request, 'product_modify', $weightSale);
        $product->highlight = $this->highlightFromRequest($request, 'product_modify');
        $product->enabled = $request->get('product_modify_enabled') != null;

        if ($request->product_modify_image) {
            $plans->assertCanUseProductPhotos($request->user());
            if ($product->image) {
                Storage::delete($product->image);
            }
            $product->image = $request->product_modify_image->store('productos');
        }

        if ($request->hasFile('product_modify_video')) {
            if ($product->video) {
                Storage::delete($product->video);
            }
            $product->video = app(ProductVideoOptimizer::class)->storeOptimized(
                $request->file('product_modify_video')
            );
        }

        $product->save();
        $product->allergens()->sync($request->get('allergens', []));

        $sectionId = $request->get('product_modify_section_id');

        return redirect()->to(route('admin.sections.index') . '#' . $sectionId)
            ->with('flash', 'Producto actualizado correctamente');
    }

    public function uploadImageInline(Request $request, Product $product, UserPlanService $plans)
    {
        $product->load('section.company');
        $this->authorize('update', $product);

        $plans->assertCanUseProductPhotos($request->user());

        $request->validate([
            'image' => 'required|image|max:8192',
        ]);

        if ($product->image) {
            Storage::delete($product->image);
        }

        $product->image = $request->file('image')->store('productos');
        $product->save();

        return response()->json([
            'success' => true,
            'image_url' => asset('img/' . $product->image),
        ]);
    }

    public function uploadVideoInline(Request $request, Product $product, UserPlanService $plans)
    {
        $product->load('section.company');
        $this->authorize('update', $product);

        $plans->assertCanUseVideos($request->user());

        $rules = $this->videoValidationRules('video');
        $rules['video'] = str_replace('nullable|', 'required|', $rules['video']);
        $request->validate($rules);

        if ($product->video) {
            Storage::delete($product->video);
        }

        $product->video = app(ProductVideoOptimizer::class)->storeOptimized(
            $request->file('video')
        );
        $product->save();

        return response()->json([
            'success' => true,
            'video_url' => asset('img/' . $product->video),
        ]);
    }

    public function delete_image_product(Product $product)
    {
        $product->load('section.company');
        $this->authorize('update', $product);

        if ($product->image) {
            Storage::delete($product->image);
            $product->image = null;
            $product->save();
        }

        return response()->json(['success' => true]);
    }

    public function delete_video_product(Product $product)
    {
        $product->load('section.company');
        $this->authorize('update', $product);

        if ($product->video) {
            Storage::delete($product->video);
            $product->video = null;
            $product->save();
        }

        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        $request->validate([
            'productid' => 'required|integer',
        ]);

        $product = Product::with('section.company')->findOrFail($request->get('productid'));
        $this->authorize('delete', $product);

        if ($product->image) {
            Storage::delete($product->image);
        }

        if ($product->video) {
            Storage::delete($product->video);
        }

        $sectionId = $product->section_id;
        $product->delete();

        return redirect()->to(url()->previous() . '#section-' . $sectionId)
            ->with('flash', 'Producto eliminado correctamente');
    }

    public function toggle_enabled(Request $request, Product $product)
    {
        $product->load('section.company');
        $this->authorize('update', $product);

        $product->enabled = $request->boolean('enabled');
        $product->save();

        return response()->json([
            'success' => true,
            'enabled' => (bool) $product->enabled,
        ]);
    }

    public function order_product(Request $request)
    {
        $request->validate([
            'section_id' => 'required|integer',
            'new_product_order' => 'required|string',
        ]);

        $section = $this->findOwnedSection($request->get('section_id'));
        $productIds = explode(',', $request->get('new_product_order'));

        foreach ($productIds as $key => $productId) {
            $product = Product::where('section_id', $section->id)->find($productId);

            if (!$product) {
                return response()->json(['success' => false]);
            }

            $product->order = $key;
            $product->save();
        }

        return response()->json(['success' => true]);
    }

    protected function videoValidationRules(string $field): array
    {
        $mimes = implode(',', config('product_media.allowed_video_mimes', ['mp4', 'webm', 'mov']));
        $maxKb = config('product_media.max_video_kb', 25600);

        return [
            $field => 'nullable|file|mimes:' . $mimes . '|max:' . $maxKb,
        ];
    }

    protected function weightUnitLabelFromRequest(Request $request, string $prefix, bool $weightSale): ?string
    {
        if (!$weightSale) {
            return null;
        }

        $label = trim((string) $request->get($prefix . '_weight_unit_label', ''));

        return $label !== '' ? mb_substr($label, 0, 64) : null;
    }

    protected function highlightFromRequest(Request $request, string $prefix): ?string
    {
        $value = $request->input($prefix . '_highlight');
        $allowed = array_keys(config('product_highlights.options', []));

        if ($value === null || $value === '') {
            return null;
        }

        return in_array($value, $allowed, true) ? $value : null;
    }

    protected function findOwnedSection($sectionId): Section
    {
        $companyId = Cookie::get('selected_company');

        return Section::where('id', $sectionId)
            ->where('company_id', $companyId)
            ->whereHas('company', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->firstOrFail();
    }
}
