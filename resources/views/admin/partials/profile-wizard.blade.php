@php
    $groups          = $profileGroups ?? null;
    $overallProgress = $groups['overall_progress'] ?? ($profileProgress ?? ['done' => 0, 'total' => 0, 'pct' => 0]);
    $accountSteps    = $groups['account_steps'] ?? [];
    $cardSteps       = $groups['card_steps']    ?? [];
    $accountProgress = $groups['account_progress'] ?? ['done' => 0, 'total' => 0, 'pct' => 0];
    $cardProgress    = $groups['card_progress']    ?? ['done' => 0, 'total' => 0, 'pct' => 0];
    $companyName     = $groups['company_name'] ?? '';

    // Fallback: si no se reciben grupos, usar lista plana antigua
    if (! $groups) {
        $accountSteps = $profileSteps ?? [];
        $cardSteps    = [];
        $overallProgress = $profileProgress ?? ['done' => 0, 'total' => 0, 'pct' => 0];
        $accountProgress = $overallProgress;
    }

    $pct           = (int) ($overallProgress['pct'] ?? 0);
    $circumference = 100;
@endphp
<section class="wn-wizard-card" data-wizard>
    <header class="wn-wizard-card__head">
        <div class="wn-wizard-card__heading">
            <h2 class="wn-wizard-card__title">Completa tu perfil</h2>
            <p class="wn-wizard-card__meta">{{ $overallProgress['done'] }} / {{ $overallProgress['total'] }} pasos completados</p>
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

    {{-- Grupo: Tu negocio --}}
    @if(count($accountSteps))
        <div class="wn-wizard-group">
            <div class="wn-wizard-group__header">
                <span class="wn-wizard-group__label">
                    <i class="ri ri-store-2-line"></i> Tu negocio
                </span>
                <span class="wn-wizard-group__pct">{{ $accountProgress['done'] }}/{{ $accountProgress['total'] }}</span>
            </div>
            <ol class="wn-wizard-steps">
                @foreach($accountSteps as $step)
                    <li class="wn-wizard-step {{ $step['is_done'] ? 'is-done' : '' }}"
                        @if($step['is_done'])
                            role="button"
                            tabindex="0"
                            aria-expanded="false"
                            data-wizard-step-toggle
                        @endif>
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
                            <div class="wn-wizard-step__done-actions">
                                <span class="wn-wizard-step__done-pill">
                                    <i class="ri ri-check-line"></i> Hecho
                                </span>
                                <a href="{{ $step['cta_url'] }}" class="wn-wizard-step__cta wn-wizard-step__cta--secondary">
                                    Editar <i class="ri ri-arrow-right-line"></i>
                                </a>
                                <i class="ri ri-arrow-down-s-line wn-wizard-step__chevron" aria-hidden="true"></i>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    @endif

    {{-- Grupo: Esta carta --}}
    @if(count($cardSteps))
        <div class="wn-wizard-group wn-wizard-group--card">
            <div class="wn-wizard-group__header">
                <span class="wn-wizard-group__label">
                    <i class="ri ri-restaurant-line"></i>
                    Esta carta{{ $companyName ? ': ' . $companyName : '' }}
                </span>
                <span class="wn-wizard-group__pct">{{ $cardProgress['done'] }}/{{ $cardProgress['total'] }}</span>
            </div>
            <ol class="wn-wizard-steps">
                @foreach($cardSteps as $step)
                    <li class="wn-wizard-step {{ $step['is_done'] ? 'is-done' : '' }}"
                        @if($step['is_done'])
                            role="button"
                            tabindex="0"
                            aria-expanded="false"
                            data-wizard-step-toggle
                        @endif>
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
                            <div class="wn-wizard-step__done-actions">
                                <span class="wn-wizard-step__done-pill">
                                    <i class="ri ri-check-line"></i> Hecho
                                </span>
                                <a href="{{ $step['cta_url'] }}" class="wn-wizard-step__cta wn-wizard-step__cta--secondary">
                                    Editar <i class="ri ri-arrow-right-line"></i>
                                </a>
                                <i class="ri ri-arrow-down-s-line wn-wizard-step__chevron" aria-hidden="true"></i>
                            </div>
                        @endif
                    </li>
                @endforeach
            </ol>
        </div>
    @endif
</section>

@once
@push('styles')
<style>
.wn-wizard-group {
    border-top: 1px solid var(--wn-border-subtle, #e5e7eb);
    padding-top: 4px;
}
.wn-wizard-group + .wn-wizard-group {
    margin-top: 4px;
}
.wn-wizard-group__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 20px 4px;
    gap: 8px;
}
.wn-wizard-group__label {
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: var(--wn-text-muted, #64748b);
    display: flex;
    align-items: center;
    gap: 5px;
}
.wn-wizard-group__label i { font-size: 13px; }
.wn-wizard-group__pct {
    font-size: 11px;
    font-weight: 600;
    color: var(--wn-primary, #004ac6);
    background: var(--wn-primary-container, #e6f0ff);
    padding: 1px 8px;
    border-radius: 999px;
}

/* Pasos completados: colapsados y atenuados, click para expandir */
.wn-wizard-step.is-done {
    opacity: .55;
    padding-top: 8px;
    padding-bottom: 8px;
    cursor: pointer;
    transition: opacity .2s ease, padding .2s ease, background-color .2s ease;
    border-radius: 6px;
}
.wn-wizard-step.is-done:hover {
    opacity: .85;
    background-color: rgba(15, 23, 42, 0.03);
}
.wn-wizard-step.is-done:focus-visible {
    opacity: .9;
    outline: none;
    box-shadow: inset 0 0 0 2px rgba(0, 74, 198, 0.25);
}
.wn-wizard-step.is-done .wn-wizard-step__title {
    font-size: 13.5px;
}
.wn-wizard-step.is-done .wn-wizard-step__desc { display: none; }
.wn-wizard-step.is-done .wn-wizard-step__art { display: none; }
.wn-wizard-step.is-done .wn-wizard-step__cta--secondary { display: none; }

.wn-wizard-step__done-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
    justify-content: flex-end;
}
.wn-wizard-step__chevron {
    font-size: 18px;
    opacity: .55;
    transition: transform .2s ease, opacity .2s ease;
    pointer-events: none;
}
.wn-wizard-step.is-done:hover .wn-wizard-step__chevron { opacity: .85; }

.wn-wizard-step.is-done.is-expanded {
    opacity: 1;
    padding-top: 14px;
    padding-bottom: 14px;
    background-color: rgba(15, 23, 42, 0.02);
}
.wn-wizard-step.is-done.is-expanded .wn-wizard-step__title {
    font-size: 15px;
}
.wn-wizard-step.is-done.is-expanded .wn-wizard-step__desc { display: block; }
.wn-wizard-step.is-done.is-expanded .wn-wizard-step__cta--secondary {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 13px;
    background: transparent;
    color: var(--wn-primary, #004ac6);
    border: 1px solid var(--wn-primary, #004ac6);
    border-radius: 8px;
    text-decoration: none;
    transition: background-color .15s ease, color .15s ease;
}
.wn-wizard-step.is-done.is-expanded .wn-wizard-step__cta--secondary:hover {
    background: var(--wn-primary, #004ac6);
    color: #fff;
    transform: none;
}
.wn-wizard-step.is-done.is-expanded .wn-wizard-step__chevron {
    transform: rotate(180deg);
    opacity: .85;
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    function toggle(el) {
        var expanded = el.classList.toggle('is-expanded');
        el.setAttribute('aria-expanded', expanded ? 'true' : 'false');
    }
    function bind() {
        var steps = document.querySelectorAll('[data-wizard-step-toggle]');
        steps.forEach(function (el) {
            if (el.dataset.wizardBound === '1') return;
            el.dataset.wizardBound = '1';
            el.addEventListener('click', function (e) {
                if (e.target.closest('a, button')) return;
                toggle(el);
            });
            el.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggle(el);
                }
            });
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind);
    } else {
        bind();
    }
})();
</script>
@endpush
@endonce
