@extends('tv.layout')

@section('tv_content')
@php
    $presenter = app(\App\Services\TvMenuPresenter::class);
    $cards = $highlights->filter(fn ($p) => ! empty($p->image))->values();
    $groups = $cards->chunk(4)->values();
    $sectionTitle = $company->chef_name
        ? 'La selección de ' . $company->chef_name
        : 'Destacados de la casa';
@endphp

<div class="wn-tv-tapas">
    <header class="wn-tv-tapas__head">
        <span class="wn-tv-tapas__rule" aria-hidden="true"></span>
        <div class="wn-tv-tapas__brand">
            <div class="wn-tv-tapas__seal" aria-hidden="true">
                <svg viewBox="0 0 48 48" focusable="false">
                    <circle cx="24" cy="24" r="21" fill="none" stroke="currentColor" stroke-width="1"/>
                    <path d="M24 10l2.8 8.6h9l-7.3 5.3 2.8 8.6L24 27.2l-7.3 5.3 2.8-8.6-7.3-5.3h9L24 10z" fill="none" stroke="currentColor" stroke-width="1" stroke-linejoin="round"/>
                </svg>
            </div>
            @if($logoUrl)
                <img src="{{ $logoUrl }}" alt="" class="wn-tv-tapas__logo">
            @endif
            <p class="wn-tv-tapas__kicker">{{ $company->name }}</p>
            <h2 class="wn-tv-tapas__title">{{ Str::upper($sectionTitle) }}</h2>
        </div>
        <span class="wn-tv-tapas__rule" aria-hidden="true"></span>
    </header>

    @if($groups->isEmpty())
        @include('tv.partials.empty', ['message' => 'Marca al menos 4 platos como destacados con foto para llenar esta plantilla.'])
    @else
        <div class="wn-tv-tapas__stage" @if($groups->count() > 1) data-tv-rotate data-tv-interval="{{ $rotateSeconds ?? 12 }}" @endif>
            @foreach($groups as $groupIndex => $group)
                <div class="wn-tv-tapas__grid{{ $groupIndex === 0 ? ' is-active' : '' }}" data-tv-slide style="--slide-index: {{ $groupIndex }}">
                    @foreach($group as $cardIndex => $product)
                        @php
                            $imgUrl = $presenter->productImageUrl($product);
                            $priceLabel = $presenter->formatPrice($product);
                            $highlightMeta = $product->highlightMeta();
                            $markLabel = $highlightMeta['label'] ?? ($product->highlight ? 'Destacado' : null);
                        @endphp
                        <article class="wn-tv-tapas__card" style="--card-i: {{ $cardIndex }}">
                            <div class="wn-tv-tapas__media">
                                @if($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="" class="wn-tv-tapas__img" loading="lazy">
                                @endif
                                <div class="wn-tv-tapas__media-shade" aria-hidden="true"></div>
                                @if($markLabel)
                                    <span class="wn-tv-tapas__mark">{{ $markLabel }}</span>
                                @endif
                            </div>
                            <div class="wn-tv-tapas__body">
                                <div class="wn-tv-tapas__copy">
                                    <h3 class="wn-tv-tapas__name">{{ $product->name }}</h3>
                                    @if($product->description)
                                        <p class="wn-tv-tapas__desc">{{ \Illuminate\Support\Str::limit($product->description, 72) }}</p>
                                    @endif
                                </div>
                                @if($priceLabel)
                                    <span class="wn-tv-tapas__price">{{ $priceLabel }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endforeach
            @if($groups->count() > 1)
                <div class="wn-tv-tapas__dots" data-tv-carousel-dots aria-hidden="true"></div>
            @endif
        </div>
    @endif
</div>
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initRotate();</script>
@endpush
