@php
    $progress = $profileProgress ?? ['done' => 0, 'total' => 0, 'pct' => 0];
    $steps = $profileSteps ?? [];
    $pct = (int) ($progress['pct'] ?? 0);
    $circumference = 100;
    $dashOffset = max(0, $circumference - $pct);
@endphp
<section class="wn-wizard-card" data-wizard>
    <header class="wn-wizard-card__head">
        <div class="wn-wizard-card__heading">
            <h2 class="wn-wizard-card__title">Completa tu perfil</h2>
            <p class="wn-wizard-card__meta">{{ $progress['done'] }} / {{ $progress['total'] }} pasos completados</p>
        </div>
        <div class="wn-wizard-pct" role="img" aria-label="Progreso {{ $pct }} por ciento" data-pct="{{ $pct }}">
            <svg viewBox="0 0 36 36" class="wn-wizard-pct__svg" aria-hidden="true">
                <circle class="wn-wizard-pct__track" cx="18" cy="18" r="15.9155"></circle>
                <circle class="wn-wizard-pct__fill"
                        cx="18" cy="18" r="15.9155"
                        style="stroke-dasharray: {{ $pct }} {{ $circumference - $pct }};"></circle>
            </svg>
            <span class="wn-wizard-pct__num">{{ $pct }}<small>%</small></span>
        </div>
        <button type="button"
                class="wn-wizard-card__dismiss"
                data-wizard-dismiss
                data-dismiss-url="{{ route('admin.profile-wizard.dismiss') }}"
                data-csrf="{{ csrf_token() }}"
                aria-label="Ocultar asistente">
            <i class="ri ri-close-line"></i>
        </button>
    </header>

    <ol class="wn-wizard-steps">
        @foreach($steps as $step)
            <li class="wn-wizard-step {{ $step['is_done'] ? 'is-done' : '' }}">
                <span class="wn-wizard-step__check" aria-hidden="true">
                    @if($step['is_done'])
                        <i class="ri ri-check-line"></i>
                    @else
                        <i class="ri {{ $step['icon'] }}"></i>
                    @endif
                </span>
                <div class="wn-wizard-step__body">
                    <strong class="wn-wizard-step__title">{{ $step['title'] }}</strong>
                    <span class="wn-wizard-step__desc">{{ $step['description'] }}</span>
                </div>
                <div class="wn-wizard-step__art" aria-hidden="true">
                    @if(isset($step['animation']) && $step['animation'])
                        @include('admin.onboarding.animations.' . $step['animation'])
                    @endif
                </div>
                @if(! $step['is_done'])
                    <a href="{{ $step['cta_url'] }}" class="wn-wizard-step__cta">
                        {{ $step['cta_label'] }} <i class="ri ri-arrow-right-line"></i>
                    </a>
                @else
                    <span class="wn-wizard-step__done-pill">
                        <i class="ri ri-check-line"></i> Hecho
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</section>
