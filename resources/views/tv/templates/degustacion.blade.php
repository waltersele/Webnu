@extends('tv.layout')

@section('tv_content')
@php
    $presenter = app(\App\Services\TvMenuPresenter::class);
    $renderMenus = collect();
    if ($activeMenu) {
        $renderMenus = collect([$activeMenu]);
    } elseif ($menus->isNotEmpty()) {
        $renderMenus = $menus;
    }
@endphp

@if($renderMenus->isEmpty())
    @include('tv.partials.empty', ['message' => 'Crea un menú del día en /admin/menus para la plantilla Degustación.'])
@else
    <div class="wn-tv-degustacion" @if($renderMenus->count() > 1) data-tv-rotate data-tv-rotate-fade data-tv-interval="{{ $rotateSeconds ?? 20 }}" @endif>
        @foreach($renderMenus as $menuIndex => $menu)
            @php
                $heroImage = $presenter->menuHeroImage($menu);
                $sectionsToShow = $menu->sections->filter(fn ($s) => $s->items->isNotEmpty())->values();
            @endphp
            <article class="wn-tv-degustacion__slide{{ $menuIndex === 0 ? ' is-active' : '' }}" data-tv-slide>
                <div class="wn-tv-degustacion__hero">
                    @if($heroImage)
                        <img src="{{ $heroImage }}" alt="" class="wn-tv-degustacion__hero-img" loading="lazy">
                    @else
                        <div class="wn-tv-degustacion__hero-placeholder" aria-hidden="true"></div>
                    @endif
                    <div class="wn-tv-degustacion__hero-shade" aria-hidden="true"></div>
                </div>
                <div class="wn-tv-degustacion__body">
                    <p class="wn-tv-degustacion__kicker">Menú degustación</p>
                    <h2 class="wn-tv-degustacion__title">{{ $menu->subtitle ?: $menu->name ?: 'Menú del día' }}</h2>
                    <div class="wn-tv-degustacion__timeline">
                        @forelse($sectionsToShow as $section)
                            <section class="wn-tv-degustacion__course">
                                <h3 class="wn-tv-degustacion__course-label">{{ Str::upper($section->name) }}</h3>
                                <ul class="wn-tv-degustacion__list">
                                    @foreach($section->items as $item)
                                        <li>{{ $item->displayName() }}</li>
                                    @endforeach
                                </ul>
                            </section>
                        @empty
                            <p class="wn-tv-degustacion__empty">Añade platos al menú en /admin/menus.</p>
                        @endforelse
                    </div>
                    @if($menu->price !== null)
                        <p class="wn-tv-degustacion__total">
                            <span>Menú completo</span>
                            <strong>{{ $menu->formattedPrice() }}</strong>
                        </p>
                    @endif
                </div>
            </article>
        @endforeach
        @if($renderMenus->count() > 1)
            <div class="wn-tv-degustacion__dots" data-tv-carousel-dots aria-hidden="true"></div>
        @endif
    </div>
@endif
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initRotate();</script>
@endpush
