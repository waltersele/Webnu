@php
    $user = auth()->user();
    $plansSrv = app(\App\Services\UserPlanService::class);
    $translationsSrv = app(\App\Services\MenuTranslationService::class);

    $supportedLocales = config('menu_locales.supported', []);
    $defaultLocale = $company->defaultLocale();
    $enabledExtra = is_array($company->enabled_locales) ? $company->enabled_locales : [];
    $canTranslate = $plansSrv->canUseTranslation($user);
    $maxExtraLocales = $plansSrv->maxTranslationLocales($user);
    $planLabel = $plansSrv->tier($user)['label'] ?? 'Gratis';
    $stats = $translationsSrv->statsForCompany($company);
    $defaultMeta = $supportedLocales[$defaultLocale] ?? ['native' => strtoupper($defaultLocale)];
    $billingUrl = route('admin.settings');
    $languagesEditUrl = route('admin.companies.languages', $company);
@endphp

<div class="wn-translations-panel">
    <header class="mb-3">
        <h2 class="h5 mb-1">Carta en varios idiomas</h2>
        <p class="text-muted small mb-0">
            Gestionas los idiomas de <strong>{{ $company->name }}</strong>.
            Cambia de carta arriba para gestionar otra.
        </p>
    </header>

    @if (! $canTranslate)
        <div class="alert alert-primary d-flex flex-wrap align-items-center gap-3 mb-4">
            <i class="ri-global-line fs-4 shrink-0"></i>
            <div class="flex-grow-1">
                <strong>Plan {{ $planLabel }}</strong>
                <p class="mb-0 small">La carta multilingüe y la traducción automática con IA están incluidas en los planes superiores.</p>
            </div>
            <button type="button" class="btn btn-sm btn-primary" data-upgrade-trigger="translation">Activar idiomas (Plus)</button>
            <a href="{{ $billingUrl }}" class="btn btn-sm btn-label-secondary">Ver planes</a>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Idiomas activos</h5>
                    @if (! $canTranslate)
                        @include('admin.partials.plan-pro-badge', ['label' => 'Plus'])
                    @endif
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Idioma base: <strong>{{ $defaultMeta['native'] ?? strtoupper($defaultLocale) }}</strong>
                        (los textos que editas en Platos de la carta).
                    </p>

                    <form method="POST" action="{{ route('admin.companies.languages.update', $company) }}">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="redirect_to" value="sections">

                        <div class="vstack gap-2 mb-3">
                            @foreach ($supportedLocales as $code => $meta)
                                @if ($code === $defaultLocale)
                                    @continue
                                @endif
                                @php
                                    $checked = in_array($code, $enabledExtra, true);
                                    $stat = $stats[$code] ?? ['percent' => 0, 'done' => 0, 'total' => 0];
                                @endphp
                                <label class="d-flex align-items-start gap-3 p-3 border rounded {{ $checked ? 'border-primary bg-light' : '' }} {{ ! $canTranslate ? 'opacity-75' : '' }}">
                                    <input type="checkbox"
                                           name="locales[]"
                                           value="{{ $code }}"
                                           class="form-check-input mt-1"
                                           {{ $checked ? 'checked' : '' }}
                                           @if(! $canTranslate) disabled @endif>
                                    <span class="flex-grow-1">
                                        <span class="fw-semibold d-block">
                                            {{ $meta['native'] ?? $meta['label'] }} <span class="text-muted">({{ strtoupper($code) }})</span>
                                            @if (! $canTranslate)
                                                @include('admin.partials.plan-pro-badge', ['label' => 'Plus', 'size' => 'xs'])
                                            @endif
                                        </span>
                                        @if ($checked)
                                            <span class="badge bg-label-primary mt-1">{{ $stat['percent'] }}% traducido</span>
                                            <span class="text-muted small d-block">{{ $stat['done'] }}/{{ $stat['total'] }} elementos</span>
                                        @endif
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        @if ($canTranslate && $maxExtraLocales !== null)
                            <p class="text-muted small mb-2">
                                Tu plan permite hasta <strong>{{ $maxExtraLocales }}</strong>
                                {{ $maxExtraLocales === 1 ? 'idioma extra' : 'idiomas extra' }}.
                            </p>
                        @endif

                        @error('locales')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                        <button type="submit" class="btn btn-primary w-100" @if(! $canTranslate) disabled @endif>
                            Guardar idiomas
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($canTranslate && count($enabledExtra) > 0)
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Traducir con IA</h5>
                    </div>
                    <div class="card-body vstack gap-3">
                        <p class="text-muted small mb-0">
                            Genera traducciones automáticas. No sobrescribe textos que hayas editado manualmente.
                        </p>

                        @foreach ($enabledExtra as $code)
                            @php $meta = $supportedLocales[$code] ?? []; @endphp
                            <form method="POST" action="{{ route('admin.companies.languages.translate', $company) }}" class="d-flex gap-2 align-items-center">
                                @csrf
                                <input type="hidden" name="locale" value="{{ $code }}">
                                <input type="hidden" name="redirect_to" value="sections">
                                <span class="flex-grow-1 fw-medium">{{ $meta['native'] ?? strtoupper($code) }}</span>
                                <button type="submit" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-sparkling-line me-1"></i> Generar
                                </button>
                            </form>
                        @endforeach

                        @error('locale')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Edición manual</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Retoca frase a frase las traducciones de cada sección y plato en el editor avanzado.
                    </p>
                    <a href="{{ $languagesEditUrl }}" class="btn btn-label-secondary w-100">
                        <i class="ri-edit-2-line me-1"></i> Editar traducciones manualmente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
