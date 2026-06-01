@php
    $pc = $planComparison ?? null;
    $current = is_array($pc) ? ($pc['current'] ?? []) : [];
    $upgrades = is_array($pc) ? ($pc['upgrades'] ?? []) : [];
    if (empty($upgrades) && is_array($pc) && !empty($pc['upgrade'])) {
        $upgrades = [$pc['upgrade']];
    }
    $bento = is_array($pc) ? ($pc['bento'] ?? []) : [];
    $bentoItems = $bento['items'] ?? [];
    $upgradeTriggerMap = ['videos' => 'video'];
    $compareClass = 'wn-plan-compare' . (count($upgrades) > 1 ? ' wn-plan-compare--multi' : '');
@endphp

<div id="plan" class="wn-plan-suite">
    <div class="{{ $compareClass }}">
        <article class="wn-plan-card wn-plan-card--current" aria-label="Plan actual">
            <header class="wn-plan-card__head">
                <p class="wn-plan-card__eyebrow">{{ $current['label_caps'] ?? 'Tu plan actual' }}</p>
                <h3 class="wn-plan-card__name">{{ $current['title'] ?? '—' }}</h3>
            </header>

            <ul class="wn-plan-card__list">
                @foreach ($current['items'] ?? [] as $item)
                    <li class="wn-plan-card__row {{ !empty($item['ok']) ? 'is-included' : 'is-missing' }}">
                        <span class="wn-plan-card__mark" aria-hidden="true">
                            <i class="ti {{ !empty($item['ok']) ? 'ti-circle-check' : 'ti-x' }}"></i>
                        </span>
                        <span>{{ $item['label'] ?? '—' }}</span>
                    </li>
                @endforeach
            </ul>

            <footer class="wn-plan-card__foot">
                <span class="wn-plan-card__stamp">{{ $current['footer']['label'] ?? 'Plan en uso' }}</span>
            </footer>
        </article>

        @foreach ($upgrades as $upgrade)
            <article class="wn-plan-card wn-plan-card--upgrade" aria-label="Plan {{ $upgrade['title'] ?? '' }}">
                @if (!empty($upgrade['badge']))
                    <span class="wn-plan-card__ribbon">{{ $upgrade['badge'] }}</span>
                @endif

                <div class="wn-plan-card__split">
                    <div class="wn-plan-card__atelier" aria-hidden="true">
                        <p class="wn-plan-card__atelier-caps">{{ $upgrade['label_caps'] ?? 'Mejora tu carta' }}</p>
                        <p class="wn-plan-card__atelier-tier">{{ $upgrade['title'] ?? 'Pro' }}</p>
                        @if (!empty($upgrade['price_label']))
                            <p class="wn-plan-card__atelier-price">{{ $upgrade['price_label'] }}</p>
                        @endif
                    </div>

                    <div class="wn-plan-card__main">
                        <ul class="wn-plan-card__list wn-plan-card__list--upgrade">
                            @foreach ($upgrade['items'] ?? [] as $item)
                                <li class="wn-plan-card__row is-included">
                                    <span class="wn-plan-card__mark" aria-hidden="true">
                                        <i class="ti ti-circle-check"></i>
                                    </span>
                                    <span>{{ $item['label'] ?? '—' }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <footer class="wn-plan-card__foot">
                            @php $cta = $upgrade['cta'] ?? []; @endphp
                            @if (!empty($cta['portal']))
                                <form method="POST" action="{{ route('admin.billing.portal') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="wn-plan-card__cta">
                                        {{ $cta['label'] ?? 'Mejorar ahora' }}
                                        <i class="ri-arrow-right-line" aria-hidden="true"></i>
                                    </button>
                                </form>
                            @elseif (!empty($cta['href']))
                                <a class="wn-plan-card__cta" href="{{ $cta['href'] }}">
                                    {{ $cta['label'] ?? 'Mejorar ahora' }}
                                    <i class="ri-arrow-right-line" aria-hidden="true"></i>
                                </a>
                            @else
                                <span class="wn-plan-card__stamp">{{ $cta['label'] ?? 'Plan en uso' }}</span>
                            @endif
                        </footer>
                    </div>
                </div>
            </article>
        @endforeach
    </div>

    @if (!empty($bentoItems))
        <section class="wn-plan-miss" aria-labelledby="wn-plan-miss-title">
            <h2 class="wn-plan-miss__title" id="wn-plan-miss-title">{{ $bento['title'] ?? 'Funciones que estás perdiendo' }}</h2>
            <p class="wn-plan-miss__lead">Toca una función para ver cómo encaja en tu plan.</p>

            <div class="wn-plan-miss__grid">
                @foreach ($bentoItems as $it)
                    @php
                        $locked = !empty($it['locked']);
                        $trigger = $upgradeTriggerMap[$it['key'] ?? ''] ?? ($it['key'] ?? '');
                    @endphp
                    @if ($locked && $trigger)
                        <button type="button"
                                class="wn-plan-miss__tile is-locked"
                                data-upgrade-trigger="{{ $trigger }}">
                    @else
                        <div class="wn-plan-miss__tile is-unlocked">
                    @endif
                            <span class="wn-plan-miss__viewport" aria-hidden="true">
                                <i class="ti {{ $it['icon'] ?? 'ti-star' }}"></i>
                            </span>
                            <span class="wn-plan-miss__name">{{ $it['title'] ?? '—' }}</span>
                            <span class="wn-plan-miss__desc">{{ $it['desc'] ?? '' }}</span>
                            @if ($locked)
                                <span class="wn-plan-miss__unlock">
                                    <i class="ri-lock-unlock-line" aria-hidden="true"></i>
                                    Ver en Pro
                                </span>
                            @endif
                    @if ($locked && $trigger)
                        </button>
                    @else
                        </div>
                    @endif
                @endforeach
            </div>
        </section>
    @endif

    <p class="wn-plan-pricing-hint text-muted small mt-2 mb-0">
        Ejemplos sin IVA: Plus solo <strong>19,90 €</strong> · Pro + 2 pantallas <strong>25,90 €</strong> · Plus + 2 pantallas <strong>27,90 €</strong>.
    </p>
</div>
