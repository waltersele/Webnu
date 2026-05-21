@extends('tv.layout')

@section('tv_content')
@php $presenter = app(\App\Services\TvMenuPresenter::class); @endphp
<div class="wn-tv-spotlight">
    <div class="wn-tv-spotlight__hero">
        @if($spotlight)
            <p class="wn-tv-spotlight__label">{{ $spotlight['label'] }}</p>
            <h2 class="wn-tv-spotlight__title">{{ $spotlight['text'] }}</h2>
            @if(!empty($spotlight['price']))
                <p class="wn-tv-spotlight__price">{{ $spotlight['price'] }} €</p>
            @endif
        @else
            <p class="wn-tv-spotlight__label">Especial de hoy</p>
            <h2 class="wn-tv-spotlight__title">Configura el especial en Webnu → Mi carta</h2>
            <p class="wn-tv-spotlight__hint">Bloque «Especial del día» en la parte superior de tu carta.</p>
        @endif
    </div>
    @if(!$highlights->isEmpty())
        <div class="wn-tv-spotlight__grid">
            @foreach($highlights->take(6) as $product)
                <article class="wn-tv-spotlight__card">
                    @if($img = $presenter->productImageUrl($product))
                        <img src="{{ $img }}" alt="" class="wn-tv-spotlight__card-img" loading="lazy">
                    @else
                        <div class="wn-tv-spotlight__card-placeholder" aria-hidden="true"></div>
                    @endif
                    <h3 class="wn-tv-spotlight__card-name">{{ $product->name }}</h3>
                    @if($price = $presenter->formatPrice($product))
                        <p class="wn-tv-spotlight__card-price">{{ $price }}</p>
                    @endif
                </article>
            @endforeach
        </div>
    @elseif(!$spotlight)
        @include('tv.partials.empty', ['message' => 'Añade el especial del día o platos con foto para esta plantilla.'])
    @endif
</div>
@endsection
