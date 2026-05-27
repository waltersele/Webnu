@extends('admin.onboarding.layout')

@section('content')
@php
    $maxStep = $maxStep ?? 6;
@endphp
<div class="wn-onb" data-step="{{ $step }}">
    <div class="wn-onb__bg" aria-hidden="true">
        <span class="wn-onb__orb wn-onb__orb--1"></span>
        <span class="wn-onb__orb wn-onb__orb--2"></span>
    </div>

    <div class="wn-onb__top-bar">
        <header class="wn-onb__top">
            <a href="{{ route('home') }}" class="wn-onb__logo" aria-label="Webnu">
                <img src="{{ \App\PlatformSetting::brandUrl('logo') }}" alt="Webnu" class="wn-onb__logo-img">
            </a>
            <div class="wn-onb__plan-badge">
                <i class="ri-gift-line"></i> {{ $planPresentation['label'] ?? ($plan['label'] ?? 'Gratis') }}
                @if(!empty($planPresentation['trial_active']))
                    · {{ $planPresentation['trial_days_remaining'] }} {{ $planPresentation['trial_days_remaining'] === 1 ? 'día' : 'días' }} restantes
                @elseif($scanLimit !== null)
                    · {{ $scansRemaining }} / {{ $scanLimit }} escaneos IA
                    @if(($scanPeriod ?? null) === 'monthly')
                        este mes
                    @endif
                @endif
            </div>
        </header>
    </div>

    <div class="wn-onb__progress" role="progressbar" aria-valuenow="{{ $step }}" aria-valuemin="1" aria-valuemax="{{ $maxStep }}">
        @for($i = 1; $i <= $maxStep; $i++)
            <span class="wn-onb__progress-seg {{ $i <= $step ? 'is-done' : '' }} {{ $i === $step ? 'is-current' : '' }}"></span>
        @endfor
    </div>

    <main class="wn-onb__main {{ $step === 3 ? 'wn-onb__main--wide' : '' }}">
        {{-- Paso 1: Bienvenida --}}
        <section class="wn-onb-step {{ $step === 1 ? 'is-active' : '' }}" data-onb-step="1">
            <div class="wn-onb-card wn-onb-card--hero">
                <div class="wn-onb-confetti" aria-hidden="true"></div>
                <div class="wn-onb-step__visual">
                    @include('admin.onboarding.animations.welcome')
                </div>
                <span class="wn-onb-eyebrow">Bienvenido, {{ explode(' ', $user->name)[0] }}</span>
                <h1>Tu carta digital en <em>minutos</em></h1>
                <p class="wn-onb-lead">Vamos a dejar lista la carta de <strong>{{ $company->name }}</strong>. Son {{ $maxStep }} pasos rápidos: diseño, idiomas y QR.</p>
                <ul class="wn-onb-checklist">
                    @if(!empty($planPresentation['trial_active']))
                        <li><i class="ri-check-line"></i> <strong>30 días de Pro gratis</strong> — vídeos, traducciones e IA</li>
                    @else
                        <li><i class="ri-check-line"></i> Sin tarjeta · Plan {{ $plan['label'] ?? 'Gratis' }}</li>
                    @endif
                    <li><i class="ri-check-line"></i> Plantillas profesionales listas</li>
                    <li><i class="ri-check-line"></i> Carta multilingüe para clientes internacionales</li>
                </ul>
                <a href="{{ route('admin.onboarding', ['step' => $companyHasIdentity ? 3 : 2]) }}" class="wn-onb-btn wn-onb-btn--primary">
                    Empezar configuración <i class="ri-arrow-right-line"></i>
                </a>
            </div>
        </section>

        {{-- Paso 2: Negocio + carta + URL --}}
        <section class="wn-onb-step {{ $step === 2 ? 'is-active' : '' }}" data-onb-step="2">
            <div class="wn-onb-card">
                <div class="wn-onb-step__visual">
                    @include('admin.onboarding.animations.business-name')
                </div>
                <span class="wn-onb-step-num">Paso 2 de {{ $maxStep }}</span>
                <h2>Tu negocio y la URL de tu carta</h2>
                <p>Esta dirección irá en tu código QR. Elige bien antes de imprimirlo.</p>
                <form method="POST" action="{{ route('admin.onboarding.update') }}" class="wn-onb-form"
                      data-webnu-url-preview
                      data-url-mode="simple"
                      data-url-host="webnu.es"
                      data-check-url="{{ route('admin.check-public-path') }}"
                      data-company-id="{{ $company->id }}"
                      data-user-id="{{ $user->id }}">
                    @csrf
                    <input type="hidden" name="step" value="2">
                    <label class="wn-onb-label" for="onb-business-name">Nombre del negocio</label>
                    <input id="onb-business-name" type="text" name="business_name" class="wn-onb-input"
                           value="{{ old('business_name', $defaultBusinessName ?? '') }}"
                           placeholder="Ej. Grupo La Brasa" data-url-owner-input required autofocus>
                    @error('business_name')<p class="wn-onb-error">{{ $message }}</p>@enderror

                    <label class="wn-onb-label mt-3" for="onb-company-name">Nombre de la carta</label>
                    <input id="onb-company-name" type="text" name="name" class="wn-onb-input"
                           value="{{ old('name', $company->name === 'Mi restaurante' ? '' : $company->name) }}"
                           placeholder="Ej. La Brasa del Puerto" data-url-name-input required>
                    @error('name')<p class="wn-onb-error">{{ $message }}</p>@enderror

                    <label class="wn-onb-label mt-3" for="onb-company-slug">URL de la carta</label>
                    <input id="onb-company-slug" type="text" name="company_slug" class="wn-onb-input"
                           value="{{ old('company_slug', $defaultCompanySlug ?? '') }}"
                           placeholder="la-brasa-del-puerto" data-url-slug-input required>
                    @error('company_slug')<p class="wn-onb-error">{{ $message }}</p>@enderror

                    <div class="wn-url-preview mt-3" role="status" aria-live="polite">
                        <p class="wn-url-preview__path"><span data-url-preview-path>webnu.es/carta/tu-carta</span></p>
                        <p class="wn-url-preview__status" data-url-preview-status></p>
                        <p class="wn-url-preview__hint">Esta URL irá en tu código QR. No la cambies después de imprimirlo.</p>
                    </div>

                    <button type="submit" class="wn-onb-btn wn-onb-btn--primary w-100 mt-3">Continuar <i class="ri-arrow-right-line"></i></button>
                </form>
            </div>
        </section>

        {{-- Paso 3: Plantilla --}}
        @php
            $selectedTemplate = old('template', $company->template ?: 'lumiere');
            $initialPreviewUrl = $templatePreviewUrls[$selectedTemplate] ?? $templatePreviewUrls['lumiere'] ?? route('public.hub', ['slug' => 'demo']);
        @endphp
        <section class="wn-onb-step {{ $step === 3 ? 'is-active' : '' }}" data-onb-step="3">
            <div class="wn-onb-card wn-onb-card--wide">
                <div class="wn-onb-step__visual">
                    @include('admin.onboarding.animations.template')
                </div>
                <span class="wn-onb-step-num">Paso 3 de {{ $maxStep }}</span>
                <h2>Elige el estilo de tu carta</h2>
                <p>Puedes cambiarlo después en el estudio visual. Selecciona un estilo y mira el ejemplo en vivo.</p>
                <form method="POST" action="{{ route('admin.onboarding.update') }}" class="wn-onb-form" id="onb-template-form">
                    @csrf
                    <input type="hidden" name="step" value="3">
                    <input type="hidden" name="template" id="onb-template-input" value="{{ $selectedTemplate }}">
                    <div class="wn-onb-template-layout">
                        <div class="wn-onb-templates" role="listbox" aria-label="Estilos de carta">
                            @foreach($templates as $id => $tpl)
                                @php
                                    $onbLocked = isset($templateAccess)
                                        && ! ($templateAccess['can_use_all'] ?? true)
                                        && ! in_array($id, $templateAccess['allowed_keys'] ?? [], true)
                                        && $id !== $selectedTemplate;
                                @endphp
                                <button type="button"
                                    class="wn-onb-template {{ $selectedTemplate === $id ? 'is-selected' : '' }} {{ $onbLocked ? 'wn-onb-template--locked' : '' }}"
                                    data-template="{{ $id }}"
                                    data-preview-url="{{ $templatePreviewUrls[$id] ?? $initialPreviewUrl }}"
                                    @if($onbLocked) data-upgrade-trigger="templates" @endif
                                    aria-pressed="{{ $selectedTemplate === $id ? 'true' : 'false' }}">
                                    <span class="wn-onb-template__label">{{ $tpl['label'] }}</span>
                                    @if($onbLocked)
                                        @include('admin.partials.plan-pro-badge', ['label' => 'Pro', 'size' => 'xs'])
                                    @endif
                                </button>
                            @endforeach
                        </div>
                        <div class="wn-onb-template-preview">
                            <div class="wn-onb-template-preview__phone">
                                <iframe id="onb-template-iframe" src="{{ $initialPreviewUrl }}" title="Vista previa de la carta" loading="lazy"></iframe>
                            </div>
                            <a id="onb-template-preview-link" href="{{ $initialPreviewUrl }}" target="_blank" rel="noopener" class="wn-onb-link">
                                Abrir ejemplo en nueva pestaña <i class="ri-external-link-line"></i>
                            </a>
                        </div>
                    </div>
                    <button type="submit" class="wn-onb-btn wn-onb-btn--primary w-100">Continuar <i class="ri-arrow-right-line"></i></button>
                </form>
            </div>
        </section>

        {{-- Paso 4: Idiomas --}}
        @php
            $onbBaseLocale = old('default_locale', $defaultLocale);
            $onbSuggestedBase = $suggestedBaseLocale ?? $defaultLocale;
        @endphp
        <section class="wn-onb-step {{ $step === 4 ? 'is-active' : '' }}" data-onb-step="4">
            <div class="wn-onb-card wn-onb-card--wide">
                <div class="wn-onb-step__visual">
                    @include('admin.onboarding.animations.languages')
                </div>
                <span class="wn-onb-step-num">Paso 4 de {{ $maxStep }}</span>
                <h2>Idioma de tu carta</h2>
                <p class="wn-onb-lead wn-onb-lead--left">Escribirás platos y categorías en este idioma. Si activas extras, tus clientes podrán cambiar al escanear el QR.</p>

                <form method="POST" action="{{ route('admin.onboarding.update') }}" class="wn-onb-form" id="onb-locales-form">
                    @csrf
                    <input type="hidden" name="step" value="4">

                    <p class="wn-onb-label">Idioma principal de la carta</p>
                    <div class="wn-onb-locales wn-onb-locales--base" data-locale-role="base">
                        @foreach($supportedLocales as $code => $meta)
                            <label class="wn-onb-locale wn-onb-locale--radio {{ $onbBaseLocale === $code ? 'is-selected' : '' }}">
                                <input type="radio" name="default_locale" value="{{ $code }}"
                                    {{ $onbBaseLocale === $code ? 'checked' : '' }}
                                    data-locale-base-radio>
                                <span>
                                    <strong>{{ $meta['native'] ?? $meta['label'] }}</strong>
                                    <small>{{ strtoupper($code) }}</small>
                                    @if($code === $onbSuggestedBase)
                                        <span class="wn-onb-locale__hint">Recomendado (tu navegador)</span>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('default_locale')<p class="wn-onb-error">{{ $message }}</p>@enderror

                    @if($canTranslate)
                        <hr class="wn-onb-divider">
                        <h3 class="wn-onb-subtitle">¿Carta para turistas internacionales?</h3>
                        <p class="wn-onb-hint mb-3">Idiomas adicionales con traducción IA (no incluye el idioma principal).</p>

                        <div class="wn-onb-locales wn-onb-locales--extras"
                             id="onb-locale-extras"
                             data-locale-limit
                             data-max-extra-locales="{{ $maxExtraLocales ?? '' }}"
                             data-billing-url="{{ $billingUrl }}">
                            @foreach($supportedLocales as $code => $meta)
                                <label class="wn-onb-locale wn-onb-locale--extra" data-locale-code="{{ $code }}"
                                    @if($code === $onbBaseLocale) hidden @endif>
                                    <input type="checkbox" name="locales[]" value="{{ $code }}"
                                        {{ in_array($code, $enabledExtra, true) ? 'checked' : '' }}>
                                    <span>
                                        <strong>{{ $meta['native'] ?? $meta['label'] }}</strong>
                                        <small>{{ strtoupper($code) }}</small>
                                    </span>
                                    <span class="wn-onb-locale__plus-badge" hidden>
                                        @include('admin.partials.plan-pro-badge', ['label' => 'Plus', 'size' => 'xs'])
                                    </span>
                                </label>
                            @endforeach
                        </div>
                        @if($maxExtraLocales !== null)
                            <p class="wn-onb-hint">
                                Tu plan permite hasta <strong>{{ $maxPublicLocales ?? ($maxExtraLocales + 1) }} idiomas en la carta</strong>
                                (1 principal + {{ $maxExtraLocales }} {{ $maxExtraLocales === 1 ? 'extra' : 'extras' }}).
                            </p>
                        @endif
                        @error('locales')<p class="wn-onb-error">{{ $message }}</p>@enderror
                        <label class="wn-onb-check">
                            <input type="checkbox" name="generate_ai" value="1">
                            <span>Generar traducciones automáticamente con IA</span>
                        </label>
                    @else
                        <div class="wn-onb-upsell mt-3">
                            <p class="mb-0">La carta multilingüe requiere plan <strong>Pro</strong>. <a href="{{ $billingUrl }}">Ver planes</a>.</p>
                        </div>
                    @endif

                    <button type="submit" class="wn-onb-btn wn-onb-btn--primary w-100 mt-3">Guardar y continuar <i class="ri-arrow-right-line"></i></button>
                </form>
                <form method="POST" action="{{ route('admin.onboarding.update') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="step" value="4">
                    <input type="hidden" name="default_locale" value="{{ $onbBaseLocale }}">
                    <button type="submit" class="wn-onb-btn wn-onb-btn--ghost w-100">Saltar · sin idiomas extra por ahora</button>
                </form>
            </div>
        </section>

        {{-- Paso 5: Carta --}}
        <section class="wn-onb-step {{ $step === 5 ? 'is-active' : '' }}" data-onb-step="5">
            <div class="wn-onb-card wn-onb-card--wide">
                <div class="wn-onb-step__visual">
                    @include('admin.onboarding.animations.menu-scan')
                </div>
                <span class="wn-onb-step-num">Paso 5 de {{ $maxStep }}</span>
                <h2>¿Cómo quieres crear tu carta?</h2>
                <div class="wn-onb-choice-grid">
                    <div class="wn-onb-choice {{ $scansRemaining > 0 || $scanLimit === null ? '' : 'is-disabled' }}">
                        <div class="wn-onb-choice__icon wn-onb-choice__icon--ai"><i class="ri-scan-line"></i></div>
                        <h3>Escaneo con IA</h3>
                        <p>Foto o PDF de tu carta actual. Detectamos platos y precios automáticamente.</p>
                        @if($scanLimit !== null)
                            <span class="wn-onb-choice__badge">{{ $scansRemaining }} de {{ $scanLimit }} escaneos correctos
                                @if(($scanPeriod ?? null) === 'monthly') este mes @endif
                            </span>
                        @else
                            <span class="wn-onb-choice__badge wn-onb-choice__badge--pro">Sin límite de escaneos</span>
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
                        <a href="{{ route('admin.onboarding', ['step' => 6]) }}" class="wn-onb-btn wn-onb-btn--primary w-100">Seguir al paso final</a>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.onboarding.update') }}" class="mt-3">
                    @csrf
                    <input type="hidden" name="step" value="5">
                    <button type="submit" class="wn-onb-btn wn-onb-btn--ghost w-100">Saltar y continuar</button>
                </form>
            </div>
        </section>

        {{-- Paso 6: Publicar --}}
        <section class="wn-onb-step {{ $step === 6 ? 'is-active' : '' }}" data-onb-step="6">
            <div class="wn-onb-card wn-onb-card--finish">
                <div class="wn-onb-step__visual">
                    @include('admin.onboarding.animations.publish')
                </div>
                <span class="wn-onb-step-num">Paso 6 de {{ $maxStep }}</span>
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
                            <input type="hidden" name="step" value="6">
                            <button type="submit" class="wn-onb-btn wn-onb-btn--primary w-100 wn-onb-btn--pulse">
                                <i class="ri-rocket-line"></i> Publicar e ir al panel
                            </button>
                        </form>
                        <a href="{{ route('admin.qrgenerator', $company) }}" target="_blank" class="wn-onb-link">Descargar QR en PDF</a>
                    </div>
                </div>
                <div class="wn-onb-pwa mt-4">
                    @include('admin.partials.pwa-install-onboarding')
                </div>
            </div>
        </section>
    </main>

    <footer class="wn-onb__footer">
        @if($step > 1 && $step < $maxStep)
            @php
                if ($step === 3 && $companyHasIdentity) {
                    $prevStep = 1;
                } elseif ($step === 4 && $companyHasIdentity) {
                    $prevStep = 3;
                } else {
                    $prevStep = $step - 1;
                }
            @endphp
            <a href="{{ route('admin.onboarding', ['step' => $prevStep]) }}" class="wn-onb-link"><i class="ri-arrow-left-line"></i> Atrás</a>
        @endif
        <form method="POST" action="{{ route('admin.onboarding.skip') }}" class="ms-auto">
            @csrf
            <button type="submit" class="wn-onb-link wn-onb-link--muted">Saltar por ahora</button>
        </form>
    </footer>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/webnu-public-url-preview.js') }}" defer></script>
<script>
    var previewIframe = document.getElementById('onb-template-iframe');
    var previewLink = document.getElementById('onb-template-preview-link');
    var billingUrl = @json($billingUrl);
    document.querySelectorAll('.wn-onb-template').forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (btn.classList.contains('wn-onb-template--locked')) {
                window.location.href = billingUrl;
                return;
            }
            document.querySelectorAll('.wn-onb-template').forEach(function (b) {
                b.classList.remove('is-selected');
                b.setAttribute('aria-pressed', 'false');
            });
            btn.classList.add('is-selected');
            btn.setAttribute('aria-pressed', 'true');
            document.getElementById('onb-template-input').value = btn.getAttribute('data-template');
            var url = btn.getAttribute('data-preview-url');
            if (url && previewIframe) {
                previewIframe.src = url;
            }
            if (url && previewLink) {
                previewLink.href = url;
            }
        });
    });
</script>
@endpush
