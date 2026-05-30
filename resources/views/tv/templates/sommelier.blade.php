@extends('tv.layout')

@section('tv_content')
@php
    $presenter = app(\App\Services\TvMenuPresenter::class);
    $pages = collect();
    foreach ($sections as $section) {
        $products = $section->products->values();
        if ($products->isEmpty()) {
            continue;
        }
        foreach ($products->chunk(8) as $chunk) {
            $pages->push([
                'section' => $section,
                'products' => $chunk->values(),
            ]);
        }
    }
@endphp

@if($pages->isEmpty())
    @include('tv.partials.empty', ['message' => 'Publica platos en tu carta para mostrar la lista Sommelier.'])
@else
    <div class="wn-tv-sommelier" @if($pages->count() > 1) data-tv-rotate data-tv-rotate-fade data-tv-interval="{{ $rotateSeconds ?? 18 }}" @endif>
        @foreach($pages as $pageIndex => $page)
            <section class="wn-tv-sommelier__page{{ $pageIndex === 0 ? ' is-active' : '' }}" data-tv-slide>
                <h2 class="wn-tv-sommelier__section-title">{{ Str::upper($page['section']->name) }}</h2>
                <ul class="wn-tv-sommelier__list">
                    @foreach($page['products'] as $product)
                        @php $priceLabel = $presenter->formatPrice($product); @endphp
                        <li class="wn-tv-sommelier__item">
                            @if($thumb = $presenter->productImageUrl($product))
                                <img src="{{ $thumb }}" alt="" class="wn-tv-sommelier__thumb" loading="lazy">
                            @endif
                            <span class="wn-tv-sommelier__name">{{ $product->name }}</span>
                            @if($priceLabel)
                                <span class="wn-tv-sommelier__price">{{ $priceLabel }}</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @endforeach
        @if($pages->count() > 1)
            <div class="wn-tv-sommelier__dots" data-tv-carousel-dots aria-hidden="true"></div>
        @endif
    </div>
@endif
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initRotate();</script>
@endpush
