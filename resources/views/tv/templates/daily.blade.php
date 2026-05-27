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
    @include('tv.partials.empty', ['message' => 'Crea tu primer menú en /admin/menus para mostrarlo aquí.'])
@else
    <div class="wn-tv-daily" @if($renderMenus->count() > 1) data-tv-rotate data-tv-interval="{{ $rotateSeconds ?? 15 }}" @endif>
        @foreach($renderMenus as $menuIndex => $menu)
            @php
                $heroImage = $presenter->menuHeroImage($menu);
                $sectionsToShow = $menu->sections->filter(fn ($s) => $s->items->isNotEmpty())->values();
                $count = $sectionsToShow->count();
            @endphp
            <article class="wn-tv-daily__slide{{ $menuIndex === 0 ? ' is-active' : '' }}" data-tv-slide style="--slide-index: {{ $menuIndex }}">
                <aside class="wn-tv-daily__panel">
                    <header class="wn-tv-daily__head">
                        <div class="wn-tv-daily__heading">
                            <h2 class="wn-tv-daily__title">{{ $menu->subtitle ?: $menu->name ?: 'Menú del día' }}</h2>
                            <p class="wn-tv-daily__company">{{ $company->name }}</p>
                        </div>
                        @if($menu->price !== null)
                            <p class="wn-tv-daily__price">{{ $menu->formattedPrice() }}</p>
                        @endif
                    </header>

                    <div class="wn-tv-daily__courses" data-section-count="{{ $count }}">
                        @forelse($sectionsToShow as $section)
                            <section class="wn-tv-daily__course">
                                <h3 class="wn-tv-daily__course-label">{{ Str::upper($section->name) }}</h3>
                                <ul class="wn-tv-daily__list">
                                    @foreach($section->items as $item)
                                        <li class="wn-tv-daily__item">{{ $item->displayName() }}</li>
                                    @endforeach
                                </ul>
                            </section>
                        @empty
                            <p class="wn-tv-daily__empty">Añade platos en /admin/menus para ver este menú en la TV.</p>
                        @endforelse
                    </div>

                    <footer class="wn-tv-daily__foot">
                        @if($menu->includes)
                            <span class="wn-tv-daily__includes">
                                <i class="ti ti-glass"></i> {{ $menu->includes }}
                            </span>
                        @endif
                        <span class="wn-tv-daily__brand">
                            <i class="ti ti-toggle-right-filled"></i> WEBNU
                        </span>
                    </footer>
                </aside>

                <div class="wn-tv-daily__art">
                    @if($heroImage)
                        <img src="{{ $heroImage }}" alt="" class="wn-tv-daily__art-img" loading="lazy">
                    @else
                        <div class="wn-tv-daily__art-placeholder" aria-hidden="true">
                            <i class="ti ti-bowl-spoon"></i>
                        </div>
                    @endif
                </div>
            </article>
        @endforeach
        @if($renderMenus->count() > 1)
            <div class="wn-tv-daily__dots" aria-hidden="true">
                @foreach($renderMenus as $idx => $_)
                    <span class="wn-tv-daily__dot{{ $idx === 0 ? ' is-active' : '' }}" data-tv-slide-dot></span>
                @endforeach
            </div>
        @endif
    </div>
@endif
@endsection

@push('tv_scripts')
<script src="{{ asset('js/webnu-tv.js') }}"></script>
<script>WebnuTv.initRotate();</script>
@endpush
