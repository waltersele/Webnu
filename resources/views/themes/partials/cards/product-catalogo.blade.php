@php
    $tagLabel = null;
    $tagClass = 'wn-catalog-tag--neutral';
    if (!empty($product->highlight)) {
        $map = [
            'featured' => ['label' => 'Destacado', 'class' => 'wn-catalog-tag--fresh'],
            'new' => ['label' => 'Nuevo', 'class' => 'wn-catalog-tag--warm'],
            'bestseller' => ['label' => 'Popular', 'class' => 'wn-catalog-tag--raw'],
        ];
        $tagLabel = $map[$product->highlight]['label'] ?? 'Especial';
        $tagClass = $map[$product->highlight]['class'] ?? 'wn-catalog-tag--neutral';
    }
@endphp

<article class="wn-card-catalogo wn-modern-card--interactive">
    <div class="wn-card-catalogo__media">
        @include('themes.partials.menu-product-media', ['product' => $product])
    </div>
    <div class="wn-card-catalogo__body">
        <div class="wn-card-catalogo__head">
            <h3 class="wn-card-catalogo__title">{{ $product->name }}</h3>
            @if($tagLabel)
                <span class="wn-catalog-tag {{ $tagClass }}">{{ $tagLabel }}</span>
            @endif
        </div>
        @if($product->description)
            <p class="wn-card-catalogo__desc">{{ $product->description }}</p>
        @endif
        @include('themes.partials.product-prices', ['product' => $product])
    </div>
    <button type="button" class="wn-card-catalogo__detail" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver detalle">
        @include('themes.partials.icons.svg-chevron-right')
    </button>
</article>

@include('themes.partials.modern-product-detail-modal', ['product' => $product])
