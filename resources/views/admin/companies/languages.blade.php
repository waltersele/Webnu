@extends('admin.layout')

@section('page_title', 'Idiomas · ' . $company->name)
@section('page_subtitle', 'Carta multilingüe para clientes internacionales')

@section('page_actions')
    <a href="{{ route('admin.companies.edit', $company) }}" class="btn btn-label-secondary btn-sm">
        <i class="ri-arrow-left-line me-1"></i> Mi negocio
    </a>
    <a href="{{ $publicUrl }}?lang=en" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">
        <i class="ri-external-link-line me-1"></i> Ver carta
    </a>
@endsection

@section('content')
@php
    $defaultMeta = $supportedLocales[$defaultLocale] ?? ['native' => strtoupper($defaultLocale)];
@endphp

@if (! $canTranslate)
    @php $ut = $upgradeTriggers ?? []; @endphp
    <div class="alert alert-primary d-flex flex-wrap align-items-center gap-3">
        <i class="ri-global-line fs-4 shrink-0"></i>
        <div class="flex-grow-1">
            <strong>Plan {{ $planLabel }}</strong>
            <p class="mb-0 small">{{ $ut['copy']['translation']['body'] ?? 'La carta multilingüe y traducción con IA están incluidas en Plus.' }}</p>
        </div>
        <button type="button" class="btn btn-sm btn-primary" data-upgrade-trigger="translation">Activar idiomas (Plus)</button>
        <a href="{{ $billingUrl }}" class="btn btn-sm btn-label-secondary">Ver planes</a>
    </div>
@endif

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Idiomas activos</h5>
                @if (! $canTranslate)
                    @include('admin.partials.plan-pro-badge', ['label' => 'Plus'])
                @endif
            </div>
            <div class="card-body">
                <p class="text-muted small">Idioma base: <strong>{{ $defaultMeta['native'] ?? strtoupper($defaultLocale) }}</strong> (textos que editas en Mi carta).</p>

                <form method="POST" action="{{ route('admin.companies.languages.update', $company) }}">
                    @csrf
                    @method('PUT')
                    @php $maxPublicLocales = $maxExtraLocales !== null ? $maxExtraLocales + 1 : null; @endphp
                    <div class="vstack gap-2 mb-4"
                         data-locale-limit
                         data-max-extra-locales="{{ $maxExtraLocales ?? '' }}"
                         data-billing-url="{{ $billingUrl }}">
                        @foreach($supportedLocales as $code => $meta)
                            @if($code === $defaultLocale)
                                @continue
                            @endif
                            @php
                                $checked = in_array($code, $enabledExtra, true);
                                $stat = $stats[$code] ?? ['percent' => 0, 'done' => 0, 'total' => 0];
                            @endphp
                            <label class="d-flex align-items-start gap-3 p-3 border rounded wn-locale-extra-row {{ $checked ? 'border-primary bg-light' : '' }} {{ ! $canTranslate ? 'opacity-90' : '' }}"
                                   data-locale-code="{{ $code }}">
                                <input type="checkbox" name="locales[]" value="{{ $code }}" class="form-check-input mt-1"
                                    {{ $checked ? 'checked' : '' }} @if(! $canTranslate) disabled @endif>
                                <span class="flex-grow-1">
                                    <span class="fw-semibold d-block">
                                        {{ $meta['native'] ?? $meta['label'] }} <span class="text-muted">({{ strtoupper($code) }})</span>
                                        @if (! $canTranslate)
                                            @include('admin.partials.plan-pro-badge', ['label' => 'Plus', 'size' => 'xs'])
                                        @endif
                                    </span>
                                    @if($checked)
                                        <span class="badge bg-label-primary mt-1">{{ $stat['percent'] }}% traducido</span>
                                        <span class="text-muted small d-block">{{ $stat['done'] }}/{{ $stat['total'] }} elementos</span>
                                    @endif
                                </span>
                                @if ($canTranslate)
                                    <span class="wn-onb-locale__plus-badge align-self-center" hidden>
                                        @include('admin.partials.plan-pro-badge', ['label' => 'Plus', 'size' => 'xs'])
                                    </span>
                                @endif
                            </label>
                        @endforeach
                    </div>

                    @if($canTranslate && $maxExtraLocales !== null)
                        <p class="text-muted small">Hasta <strong>{{ $maxPublicLocales }} idiomas en la carta</strong> (1 principal + {{ $maxExtraLocales }} extras).</p>
                    @endif

                    @error('locales')<div class="text-danger small mb-2">{{ $message }}</div>@enderror

                    <button type="submit" class="btn btn-primary w-100" @if(! $canTranslate) disabled @endif>
                        Guardar idiomas
                    </button>
                </form>
            </div>
        </div>

        @if($canTranslate && count($enabledExtra) > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Traducir con IA</h5>
                </div>
                <div class="card-body vstack gap-3">
                    <p class="text-muted small mb-0">Genera traducciones automáticas con Gemini. No sobrescribe textos que hayas editado manualmente.</p>
                    @foreach($enabledExtra as $code)
                        @php $meta = $supportedLocales[$code] ?? []; @endphp
                        <form method="POST" action="{{ route('admin.companies.languages.translate', $company) }}" class="d-flex gap-2 align-items-center">
                            @csrf
                            <input type="hidden" name="locale" value="{{ $code }}">
                            <span class="flex-grow-1 fw-medium">{{ $meta['native'] ?? strtoupper($code) }}</span>
                            <button type="submit" class="btn btn-sm btn-outline-primary">
                                <i class="ri-sparkling-line me-1"></i> Generar
                            </button>
                        </form>
                    @endforeach
                    @error('locale')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>
        @elseif(! $canTranslate)
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="card-title mb-0">Traducir con IA</h5>
                    @include('admin.partials.plan-pro-badge', ['label' => 'Plus'])
                </div>
                @component('admin.partials.plan-feature-lock', [
                    'feature' => 'translation',
                    'message' => 'Genera traducciones automáticas de toda tu carta con el plan Plus.',
                ])
                <div class="card-body vstack gap-3">
                    <p class="text-muted small mb-0">Genera traducciones automáticas con Gemini. No sobrescribe textos que hayas editado manualmente.</p>
                    @foreach($supportedLocales as $code => $meta)
                        @if($code === $defaultLocale)
                            @continue
                        @endif
                        @if($loop->iteration > 2)
                            @break
                        @endif
                        <div class="d-flex gap-2 align-items-center">
                            <span class="flex-grow-1 fw-medium">{{ $meta['native'] ?? strtoupper($code) }}</span>
                            <button type="button" class="btn btn-sm btn-outline-primary" disabled>
                                <i class="ri-sparkling-line me-1"></i> Generar
                            </button>
                        </div>
                    @endforeach
                </div>
                @endcomponent
            </div>
        @endif
    </div>

    <div class="col-lg-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Edición manual</h5>
                <span class="text-muted small">Plato a plato</span>
            </div>
            <div class="card-body p-0">
                @if(! $canTranslate)
                    @component('admin.partials.plan-feature-lock', [
                        'feature' => 'translation',
                        'message' => 'Edita traducciones plato a plato cuando actives idiomas con Plus.',
                    ])
                    <div class="p-4">
                        <div class="border rounded p-3 mb-2 bg-light">
                            <span class="badge bg-label-secondary mb-2">EN</span>
                            <input type="text" class="form-control form-control-sm mb-2" value="Ej. Starters" disabled>
                            <textarea class="form-control form-control-sm" rows="2" disabled placeholder="Descripción traducida…"></textarea>
                        </div>
                        <div class="border rounded p-3 bg-light opacity-75">
                            <span class="badge bg-label-secondary mb-2">FR</span>
                            <input type="text" class="form-control form-control-sm" value="Ej. Entrées" disabled>
                        </div>
                    </div>
                    @endcomponent
                @elseif(count($enabledExtra) === 0)
                    <div class="p-4 text-muted">Activa al menos un idioma extra para editar traducciones.</div>
                @else
                    <div class="accordion accordion-flush" id="lang-sections">
                        @foreach($sections as $section)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#lang-sec-{{ $section->id }}">
                                        {{ $section->name }}
                                    </button>
                                </h2>
                                <div id="lang-sec-{{ $section->id }}" class="accordion-collapse collapse" data-bs-parent="#lang-sections">
                                    <div class="accordion-body">
                                        @foreach($enabledExtra as $code)
                                            @php
                                                $secTr = $section->translations->firstWhere('locale', $code);
                                                $meta = $supportedLocales[$code] ?? [];
                                            @endphp
                                            <form method="POST" action="{{ route('admin.companies.languages.section', [$company, $section]) }}" class="border rounded p-3 mb-3">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="locale" value="{{ $code }}">
                                                <label class="form-label fw-medium">{{ $meta['native'] ?? strtoupper($code) }} · Sección</label>
                                                <div class="input-group input-group-sm mb-2">
                                                    <input type="text" name="name" class="form-control" value="{{ old('name', $secTr->name ?? '') }}" placeholder="{{ $section->name }}" required>
                                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                                </div>
                                                @if($secTr)
                                                    <span class="badge bg-label-secondary">{{ $secTr->source === 'ai' ? 'IA' : ($secTr->source === 'ai_edited' ? 'IA editado' : 'Manual') }}</span>
                                                @endif
                                            </form>
                                        @endforeach

                                        @foreach($section->products as $product)
                                            <div class="border-top pt-3 mt-3">
                                                <p class="fw-medium mb-2">{{ $product->name }}</p>
                                                @foreach($enabledExtra as $code)
                                                    @php
                                                        $prodTr = $product->translations->firstWhere('locale', $code);
                                                        $meta = $supportedLocales[$code] ?? [];
                                                    @endphp
                                                    <form method="POST" action="{{ route('admin.companies.languages.product', [$company, $product]) }}" class="border rounded p-3 mb-2 bg-light">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="hidden" name="locale" value="{{ $code }}">
                                                        <span class="badge bg-label-primary mb-2">{{ strtoupper($code) }}</span>
                                                        <input type="text" name="name" class="form-control form-control-sm mb-2" value="{{ old('name', $prodTr->name ?? '') }}" placeholder="{{ $product->name }}" required>
                                                        <textarea name="description" class="form-control form-control-sm mb-2" rows="2" placeholder="{{ $product->description }}">{{ old('description', $prodTr->description ?? '') }}</textarea>
                                                        <button type="submit" class="btn btn-sm btn-outline-primary">Guardar plato</button>
                                                        @if($prodTr)
                                                            <span class="badge bg-label-secondary ms-2">{{ $prodTr->source === 'ai' ? 'IA' : ($prodTr->source === 'ai_edited' ? 'IA editado' : 'Manual') }}</span>
                                                        @endif
                                                    </form>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/webnu-locale-limit.js') }}"></script>
@endpush
