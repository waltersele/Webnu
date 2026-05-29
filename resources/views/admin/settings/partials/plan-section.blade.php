@php
    $pc = $planComparison ?? null;
    $current = is_array($pc) ? ($pc['current'] ?? []) : [];
    $upgrade = is_array($pc) ? ($pc['upgrade'] ?? []) : [];
    $bento = is_array($pc) ? ($pc['bento'] ?? []) : [];
@endphp

<div id="plan">
    <div class="wn-plan-grid">
        <div class="wn-plan-card">
            <div class="wn-plan-caps">{{ $current['label_caps'] ?? 'Tu plan actual' }}</div>
            <h3 class="wn-plan-title">{{ $current['title'] ?? '—' }}</h3>

            @php $currentItems = $current['items'] ?? []; @endphp
            <ul class="wn-plan-list">
                @foreach($currentItems as $item)
                    <li>
                        <span class="wn-plan-ico {{ !empty($item['ok']) ? 'is-ok' : 'is-no' }}">
                            <i class="ti {{ !empty($item['ok']) ? 'ti-circle-check' : 'ti-x' }}"></i>
                        </span>
                        <span>{{ $item['label'] ?? '—' }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="wn-plan-footer">
                <button class="wn-plan-btn-disabled" type="button" disabled>
                    {{ $current['footer']['label'] ?? 'Plan en uso' }}
                </button>
            </div>
        </div>

        <div class="wn-plan-card wn-plan-card--featured">
            @if(!empty($upgrade['badge']))
                <div class="wn-plan-badge">{{ $upgrade['badge'] }}</div>
            @endif

            <div class="wn-plan-caps" style="color: var(--wn-primary, #004ac6);">{{ $upgrade['label_caps'] ?? 'Revoluciona tu negocio' }}</div>
            <h3 class="wn-plan-title">{{ $upgrade['title'] ?? 'Pro' }}</h3>

            @php $upgradeItems = $upgrade['items'] ?? []; @endphp
            <ul class="wn-plan-list">
                @foreach($upgradeItems as $item)
                    <li style="color: var(--wn-text, #0f172a); font-weight: 600;">
                        <span class="wn-plan-ico is-ok">
                            <i class="ti ti-circle-check"></i>
                        </span>
                        <span>{{ $item['label'] ?? '—' }}</span>
                    </li>
                @endforeach
            </ul>

            <div class="wn-plan-footer">
                @php $cta = $upgrade['cta'] ?? []; @endphp
                @if(!empty($cta['portal']))
                    <form method="POST" action="{{ route('admin.billing.portal') }}" class="m-0">
                        @csrf
                        <button type="submit" class="wn-plan-cta">
                            {{ $cta['label'] ?? 'Mejorar ahora' }}
                            <i class="ti ti-sparkles"></i>
                        </button>
                    </form>
                @elseif(!empty($cta['href']))
                    <a class="wn-plan-cta" href="{{ $cta['href'] }}">
                        {{ $cta['label'] ?? 'Mejorar ahora' }}
                        <i class="ti ti-sparkles"></i>
                    </a>
                @else
                    <button type="button" class="wn-plan-btn-disabled" disabled>Plan en uso</button>
                @endif
            </div>
        </div>
    </div>

    @php
        $bentoItems = $bento['items'] ?? [];
    @endphp
    @if(!empty($bentoItems))
        <h2 class="wn-plan-bento-title">{{ $bento['title'] ?? 'Funciones que estás perdiendo' }}</h2>
        <div class="wn-plan-bento">
            @foreach($bentoItems as $it)
                <div class="wn-plan-bento-item {{ !empty($it['locked']) ? 'is-locked' : '' }}">
                    <div class="wn-plan-bento-icon">
                        <i class="ti {{ $it['icon'] ?? 'ti-star' }}"></i>
                    </div>
                    <div class="fw-bold">{{ $it['title'] ?? '—' }}</div>
                    <div class="small text-muted">{{ $it['desc'] ?? '' }}</div>
                    @if(!empty($it['locked']))
                        <div class="wn-plan-bento-lock">
                            <i class="ti ti-lock"></i> Bloqueado
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
