@extends('tv.layout')

@section('tv_content')
@php
    $presenter = app(\App\Services\TvMenuPresenter::class);
    $cards = $highlights->filter(fn ($p) => ! empty($p->image))->values();
    $groups = $cards->chunk(4)->values();
    $sectionTitle = $company->chef_name ? 'La selección de ' . $company->chef_name : 'Destacados de la casa';
@endphp

<div class="wn-tv-tapas">
    <header class="wn-tv-tapas__head">
        <span class="wn-tv-tapas__rule" aria-hidden="true"></span>
        <h2 class="wn-tv-tapas__title">{{ Str::upper($sectionTitle) }}</h2>
        <span class="wn-tv-tapas__rule" aria-hidden="true"></span>
    </header>

    @if($groups->isEmpty())
        @include('tv.partials.empty', ['message' => 'Marca al menos 4 platos como destacados con foto para llenar esta plantilla.'])
    @else
        <div class="wn-tv-tapas__stage" @if($groups->count() > 1) data-tv-rotate data-tv-interval="{{ $rotateSeconds ?? 12 }}" @endif>
            @foreach($groups as $groupIndex => $group)
                <div class="wn-tv-tapas__grid{{ $groupIndex === 0 ? ' is-active' : '' }}" data-tv-slide style="--slide-index: {{ $groupIndex }}">
                    @foreach($group as $product)
                        @php
                            $imgUrl = $presenter->productImageUrl($product);
                            $priceLabel = $presenter->formatPrice($product);
                        @endphp
                        <article class="wn-tv-tapas__card">
                            <div class="wn-tv-tapas__media">
                                @if($imgUrl)
                                    <img src="{{ $imgUrl }}" alt="" class="wn-tv-tapas__img" loading="lazy">
                                @endif
                            </div>
                            <div class="wn-tv-tapas__body">
                                <h3 class="wn-tv-tapas__name">{{ $product->name }}</h3>
                                @if($priceLabel)
                                    <span class="wn-tv-tapas__price">{{ $priceLabel }}</span>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initRotate();</script>
@endpush
