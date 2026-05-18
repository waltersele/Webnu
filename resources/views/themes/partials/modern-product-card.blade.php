@php

    $layout = $layout ?? 'horizontal';

    $cardClass = 'wn-modern-card wn-modern-card--' . $layout . ' wn-modern-card--interactive';

@endphp



<article class="{{ $cardClass }}">

    <div class="wn-modern-card__media">

        @include('themes.partials.menu-product-media', ['product' => $product])

        @if (!empty($product->highlight))

            <div class="wn-modern-card__badges">

                @include('themes.partials.product-highlight-badge', ['product' => $product])

            </div>

        @endif

    </div>

    <div class="wn-modern-card__body">

        <div class="wn-modern-card__head">

            <h3 class="wn-modern-card__title">

                {{ $product->name }}

                @if(empty($product->highlight))

                    @include('themes.partials.product-highlight-badge', ['product' => $product])

                @endif

            </h3>

            <button type="button" class="wn-modern-card__detail-btn" data-toggle="modal" data-target="#wnDish{{ $product->id }}" aria-label="Ver detalle">

                <i class="fas fa-chevron-right"></i>

            </button>

        </div>

        @if($product->description)

            <p class="wn-modern-card__desc">{{ $product->description }}</p>

        @endif

        @include('themes.partials.product-prices', ['product' => $product])

        @include('themes.partials.menu-product-allergens', ['product' => $product])

    </div>

</article>



@include('themes.partials.modern-product-detail-modal', ['product' => $product])

