@extends('tv.layout')

@section('tv_content')
@php
    $presenter = app(\App\Services\TvMenuPresenter::class);
    $recommendations = $featured->take(5);
    if ($recommendations->isEmpty()) {
        $recommendations = $highlights->take(5);
    }
    $ambientImages = collect();
    if (! empty($headerUrl)) {
        $ambientImages->push($headerUrl);
    }
    foreach ($highlights->filter(fn ($p) => $presenter->productImageUrl($p)) as $product) {
        $ambientImages->push($presenter->productImageUrl($product));
    }
    $ambientImages = $ambientImages->unique()->values();
@endphp

@if($recommendations->isEmpty())
    @include('tv.partials.empty', ['message' => 'Añade platos destacados o una foto de cabecera para Lounge.'])
@else
    <div class="wn-tv-lounge">
        <div class="wn-tv-lounge__ambient" @if($ambientImages->count() > 1) data-tv-rotate data-tv-rotate-fade data-tv-interval="{{ $rotateSeconds ?? 10 }}" @endif>
            @if($ambientImages->isEmpty())
                <div class="wn-tv-lounge__ambient-fallback" aria-hidden="true"></div>
            @else
                @foreach($ambientImages as $imgIndex => $img)
                    <img src="{{ $img }}" alt="" class="wn-tv-lounge__ambient-img{{ $imgIndex === 0 ? ' is-active' : '' }}" data-tv-slide loading="lazy">
                @endforeach
            @endif
            <div class="wn-tv-lounge__ambient-shade" aria-hidden="true"></div>
        </div>
        <aside class="wn-tv-lounge__panel">
            <h2 class="wn-tv-lounge__title">Recomendaciones</h2>
            <ul class="wn-tv-lounge__list">
                @foreach($recommendations as $product)
                    @php $priceLabel = $presenter->formatPrice($product); @endphp
                    <li class="wn-tv-lounge__item">
                        <span class="wn-tv-lounge__dot" aria-hidden="true"></span>
                        <span class="wn-tv-lounge__name">{{ $product->name }}</span>
                        @if($priceLabel)
                            <span class="wn-tv-lounge__price">{{ $priceLabel }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </aside>
    </div>
@endif
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initRotate();</script>
@endpush
