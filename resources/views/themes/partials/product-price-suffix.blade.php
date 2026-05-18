@if ($product->individual_sale)
    / ud.
@elseif (!empty($product->weight_sale))
    @if (!empty($product->weight_unit_label))
        / {{ $product->weight_unit_label }}
    @else
        / kg.
    @endif
@endif
