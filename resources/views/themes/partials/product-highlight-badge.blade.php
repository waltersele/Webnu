@if (!empty($product->highlight))
    @include('admin.sections.partials.product-highlight-badge', ['highlight' => $product->highlight])
@endif
