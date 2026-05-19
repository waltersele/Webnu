@extends('admin.onboarding.layout')

@section('content')
<div class="wn-onb" data-step="{{ $step }}">
    <div class="wn-onb__bg" aria-hidden="true">
        <span class="wn-onb__orb wn-onb__orb--1"></span>
        <span class="wn-onb__orb wn-onb__orb--2"></span>
    </div>

    <header class="wn-onb__top">
        <a href="{{ route('home') }}" class="wn-onb__logo">Webnu<span>.es</span></a>
        <div class="wn-onb__plan-badge">
            <i class="ri-gift-line"></i> Plan {{ $plan['label'] ?? 'Gratis' }}
            @if($scanLimit !== null)
                · {{ $scansRemaining }} / {{ $scanLimit }} escaneos IA
            @endif
        </div>
    </header>

    <div class="wn-onb__progress" role="progressbar" aria-valuenow="{{ $step }}" aria-valuemin="1" aria-valuemax="5">
        @for($i = 1; $i <= 5; $i++)
            <span class="wn-onb__progress-seg {{ $i <= $step ? 'is-done' : '' }} {{ $i === $step ? 'is-current' : '' }}"></span>
        @endfor
    </div>

    <main class="wn-onb__main">
        {{-- Paso 1: Bienvenida --}}
        <section class="wn-onb-step {{ $step === 1 ? 'is-active' : '' }}" data-onb-step="1">
            <div class="wn-onb-card wn-onb-card--hero">
                <div class="wn-onb-confetti" aria-hidden="true"></div>
                <span class="wn-onb-eyebrow">Bienvenido, {{ explode(' ', $user->name)[0] }}</span>
                <h1>Tu carta digital en <em>minutos</em></h1>
                <p class="wn-onb-lead">Vamos a dejar lista la carta de <strong>{{ $company->name }}</strong>. Son 5 pasos rápidos: identidad, diseño y QR para tus mesas.</p>
                <ul class="wn-onb-checklist">
                    <li><i class="ri-check-line"></i> Sin tarjeta · Plan {{ $plan['label'] }}</li>
                    <li><i class="ri-check-line"></i> Plantillas profesionales listas</li>
                    <li><i class="ri-check-line"></i> @if($scanLimit) {{ $scanLimit }} escaneos IA incluidos @else Escaneo IA ilimitado @endif</li>
                </ul>
                <a href="{{ route('admin.onboarding', ['step' => 2]) }}" class="wn-onb-btn wn-onb-btn--primary">
                    Empezar configuración <i class="ri-arrow-right-line"></i>
                </a>
            </div>
        </section>

        {{-- Paso 2: Nombre --}}
        <section class="wn-onb-step {{ $step === 2 ? 'is-active' : '' }}" data-onb-step="2">
            <div class="wn-onb-card">
                <span class="wn-onb-step-num">Paso 2 de 5</span>
                <h2>¿Cómo se llama tu negocio?</h2>
                <p>Aparecerá en la carta y en el código QR.</p>
                <form method="POST" action="{{ route('admin.onboarding.update') }}" class="wn-onb-form">
                    @csrf
                    <input type="hidden" name="step" value="2">
                    <label class="wn-onb-label" for="company-name">Nombre del restaurante</label>
                    <input id="company-name" type="text" name="name" class="wn-onb-input" value="{{ old('name', $company->name) }}" required autofocus>
                    <button type="submit" class="wn-onb-btn wn-onb-btn--primary w-100">Continuar <i class="ri-arrow-right-line"></i></button>
                </form>
            </div>
        </section>

        {{-- Paso 3: Plantilla --}}
        <section class="wn-onb-step {{ $step === 3 ? 'is-active' : '' }}" data-onb-step="3">
            <div class="wn-onb-card wn-onb-card--wide">
                <span class="wn-onb-step-num">Paso 3 de 5</span>
                <h2>Elige el estilo de tu carta</h2>
                <p>Puedes cambiarlo después en el estudio visual.</p>
                <form method="POST" action="{{ route('admin.onboarding.update') }}" class="wn-onb-form" id="onb-template-form">
                    @csrf
                    <input type="hidden" name="step" value="3">
                    <input type="hidden" name="template" id="onb-template-input" value="{{ old('template', $company->template ?: 'lumiere') }}">
                    <div class="wn-onb-templates">
                        @foreach($templates as $id => $tpl)
                            <button type="button"
                                class="wn-onb-template {{ ($company->template ?: 'lumiere') === $id ? 'is-selected' : '' }}"
                                data-template="{{ $id }}">
                                <img src="{{ asset($tpl['preview_image']) }}" alt="{{ $tpl['label'] }}">
                                <span>{{ $tpl['label'] }}</span>
                            </button>
                        @endforeach
                    </div>
                    <button type="submit" class="wn-onb-btn wn-onb-btn--primary w-100">Continuar <i class="ri-arrow-right-line"></i></button>
                </form>
            </div>
        </section>

        {{-- Paso 4: Carta --}}
        <section class="wn-onb-step {{ $step === 4 ? 'is-active' : '' }}" data-onb-step="4">
            <div class="wn-onb-card wn-onb-card--wide">
                <span class="wn-onb-step-num">Paso 4 de 5</span>
                <h2>¿Cómo quieres crear tu carta?</h2>
                <div class="wn-onb-choice-grid">
                    <div class="wn-onb-choice {{ $scansRemaining > 0 || $scanLimit === null ? '' : 'is-disabled' }}">
                        <div class="wn-onb-choice__icon wn-onb-choice__icon--ai"><i class="ri-scan-line"></i></div>
                        <h3>Escaneo con IA</h3>
                        <p>Foto o PDF de tu carta actual. Detectamos platos y precios automáticamente.</p>
                        @if($scanLimit !== null)
                            <span class="wn-onb-choice__badge">{{ $scansRemaining }} de {{ $scanLimit }} escaneos restantes</span>
                        @else
                            <span class="wn-onb-choice__badge wn-onb-choice__badge--pro">Ilimitado en tu plan</span>
                        @endif
                        @if($scansRemaining > 0 || $scanLimit === null)
                            <a href="{{ $menuScanUrl }}" class="wn-onb-btn wn-onb-btn--outline w-100">Escanear ahora</a>
                        @else
                            <a href="{{ $billingUrl }}" class="wn-onb-btn wn-onb-btn--ghost w-100">Mejorar plan</a>
                        @endif
                    </div>
                    <div class="wn-onb-choice">
                        <div class="wn-onb-choice__icon"><i class="ri-edit-line"></i></div>
                        <h3>Crear manualmente</h3>
                        <p>Añade secciones y platos tú mismo. Ideal si empiezas desde cero.</p>
                        <a href="{{ route('admin.onboarding', ['step' => 5]) }}" class="wn-onb-btn wn-onb-btn--primary w-100">Seguir al paso final</a>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.onboarding.update') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="step" value="4">
                    <button type="submit" class="wn-onb-btn wn-onb-btn--ghost w-100">Saltar y continuar</button>
                </form>
            </div>
        </section>

        {{-- Paso 5: Publicar --}}
        <section class="wn-onb-step {{ $step === 5 ? 'is-active' : '' }}" data-onb-step="5">
            <div class="wn-onb-card wn-onb-card--finish">
                <span class="wn-onb-step-num">Paso 5 de 5</span>
                <h2>¡Tu carta está casi lista!</h2>
                <p>Publica y comparte el QR con tus clientes.</p>
                <div class="wn-onb-finish-grid">
                    <div class="wn-onb-qr-wrap">
                        <img src="{{ $qrImageUrl }}" alt="Código QR de tu carta" width="220" height="220" class="wn-onb-qr-img">
                        <p class="wn-onb-qr-caption">Escanea para probar</p>
                    </div>
                    <div class="wn-onb-finish-actions">
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener" class="wn-onb-btn wn-onb-btn--outline w-100">
                            <i class="ri-external-link-line"></i> Ver carta en vivo
                        </a>
                        <form method="POST" action="{{ route('admin.onboarding.update') }}">
                            @csrf
                            <input type="hidden" name="step" value="5">
                            <button type="submit" class="wn-onb-btn wn-onb-btn--primary w-100 wn-onb-btn--pulse">
                                <i class="ri-rocket-line"></i> Publicar e ir al panel
                            </button>
                        </form>
                        <a href="{{ route('admin.qrgenerator', $company) }}" target="_blank" class="wn-onb-link">Descargar QR en PDF</a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="wn-onb__footer">
        @if($step > 1 && $step < 5)
            <a href="{{ route('admin.onboarding', ['step' => $step - 1]) }}" class="wn-onb-link"><i class="ri-arrow-left-line"></i> Atrás</a>
        @endif
        <form method="POST" action="{{ route('admin.onboarding.skip') }}" class="ms-auto">
            @csrf
            <button type="submit" class="wn-onb-link wn-onb-link--muted">Saltar por ahora</button>
        </form>
    </footer>
</div>
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.wn-onb-template').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.wn-onb-template').forEach(function (b) { b.classList.remove('is-selected'); });
            btn.classList.add('is-selected');
            document.getElementById('onb-template-input').value = btn.getAttribute('data-template');
        });
    });
</script>
@endpush
