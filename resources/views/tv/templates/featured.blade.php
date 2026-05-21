@extends('tv.layout')

@section('tv_content')
@php $presenter = app(\App\Services\TvMenuPresenter::class); @endphp
<div class="wn-tv-featured" data-tv-carousel data-tv-interval="{{ $rotateSeconds ?? 8 }}">
    @forelse($featured as $index => $product)
        <article class="wn-tv-featured__slide{{ $index === 0 ? ' is-active' : '' }}" data-tv-carousel-slide>
            <div class="wn-tv-featured__media">
                @if($img = $presenter->productImageUrl($product))
                    <img src="{{ $img }}" alt="" class="wn-tv-featured__img" loading="lazy">
                @else
                    <div class="wn-tv-featured__placeholder" aria-hidden="true">
                        <span class="wn-tv-featured__placeholder-icon">★</span>
                    </div>
                @endif
            </div>
            <div class="wn-tv-featured__caption">
                @if($product->highlight)
                    <span class="wn-tv-featured__badge">Destacado</span>
                @endif
                <h2 class="wn-tv-featured__name">{{ $product->name }}</h2>
                @if($product->description)
                    <p class="wn-tv-featured__desc">{{ \Illuminate\Support\Str::limit($product->description, 120) }}</p>
                @endif
                @if($price = $presenter->formatPrice($product))
                    <p class="wn-tv-featured__price">{{ $price }}</p>
                @endif
            </div>
        </article>
    @empty
        @include('tv.partials.empty', ['message' => 'Marca platos como destacados o añade fotos para este modo TV.'])
    @endforelse
    @if($featured->count() > 1)
        <div class="wn-tv-featured__dots" data-tv-carousel-dots aria-hidden="true"></div>
    @endif
</div>
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initCarousel();</script>
@endpush
