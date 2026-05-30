@extends('tv.layout')

@section('tv_content')
@php $presenter = app(\App\Services\TvMenuPresenter::class); @endphp
<div class="wn-tv-menu-banner">
    <header class="wn-tv-menu-banner__banner">
        @include('tv.partials.video-zone', [
            'presenter' => $presenter,
            'videos' => $videos,
            'compact' => true,
            'showCaption' => true,
            'rootClass' => 'wn-tv-menu-banner__video',
            'interval' => $rotateSeconds ?? 12,
        ])
    </header>
    <div class="wn-tv-menu-banner__menu wn-tv-menu" data-tv-rotate data-tv-interval="{{ $rotateSeconds ?? 12 }}">
        @forelse($sections as $sectionIndex => $section)
            <section class="wn-tv-menu__section" data-tv-slide style="--slide-index: {{ $sectionIndex }}">
                <h2 class="wn-tv-menu__section-title">{{ $section->name }}</h2>
                <ul class="wn-tv-menu__list">
                    @foreach($section->products as $product)
                        @include('tv.partials.menu-item', ['product' => $product, 'presenter' => $presenter])
                    @endforeach
                </ul>
            </section>
        @empty
            @include('tv.partials.empty', ['message' => 'No hay platos publicados en esta carta.'])
        @endforelse
    </div>
</div>
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initPage({ video: true, lazyVideo: true });</script>
@endpush
