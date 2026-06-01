@php
    $galleryId = $galleryId ?? 'wn-tvpik-gallery';
    $showFilter = $showFilter ?? false;
@endphp
<div class="wn-tvpik-gallery" id="{{ $galleryId }}">
    @if($showFilter)
        @include('admin.tvpik.partials.template-gallery-filters', ['galleryId' => $galleryId])
    @endif
    <div class="row g-3">
        @foreach($templates as $key => $tpl)
            @php
                $thumb = $tpl['thumbnail'] ?? ('img/tvpik/previews/' . ($tpl['layout'] ?? $key) . '.svg');
                $previewCompany = $company ?? $companies->firstWhere('id', $defaultCompanyId);
                $previewSlug = $previewCompany ? $previewCompany->slug : null;
                $layout = $tpl['layout'] ?? $key;
                $isPremiumTpl = ! empty($tpl['premium']);
                $tplLocked = ! $canTvpik || ($isPremiumTpl && ! $canTvpikPremium);
                $filterClass = $isPremiumTpl ? 'premium' : 'standard';
                $isDefaultSelected = $key === 'menu';
            @endphp
            <div class="col-md-6 col-lg-3" data-template-filter="{{ $filterClass }}" data-template-key="{{ $key }}" data-template-layout="{{ $layout }}">
                <article class="wn-tvpik-template-card {{ $tplLocked ? 'wn-tvpik-template-card--locked' : '' }} {{ ! $tplLocked && $isDefaultSelected ? 'is-selected' : '' }}">
                    <div class="wn-tvpik-template-card__thumb">
                        <img src="{{ asset($thumb) }}" alt="{{ $tpl['label'] }}" width="320" height="180" loading="lazy">
                        <span class="wn-tvpik-template-card__badge">
                            <i class="ti {{ $tpl['icon'] ?? 'ti-layout' }}"></i>
                            {{ $tpl['label'] }}
                        </span>
                        @if($tplLocked)
                            <div class="wn-tvpik-template-card__lock-overlay">
                                <i class="ti ti-lock"></i>
                                @if($canTvpik && $isPremiumTpl && ! $canTvpikPremium)
                                    <span class="small d-block mt-1">Plus</span>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="wn-tvpik-template-card__body">
                        <h6 class="wn-tvpik-template-card__title">
                            {{ $tpl['label'] }}
                            @if($isPremiumTpl)
                                <span class="badge bg-label-primary ms-1">Premium</span>
                            @endif
                        </h6>
                        <p class="wn-tvpik-template-card__desc">{{ $tpl['description'] }}</p>
                        @if(!empty($tpl['duration_hint']))
                            <p class="wn-tvpik-template-card__hint">{{ $tpl['duration_hint'] }}</p>
                        @endif
                        <div class="wn-tvpik-template-card__actions">
                            @if($tplLocked)
                                @if(! $canTvpik)
                                    <a href="{{ route('admin.settings') }}#plan" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="ti ti-crown me-1"></i> Ver planes
                                    </a>
                                @elseif($isPremiumTpl && ! $canTvpikPremium)
                                    <a href="{{ route('admin.settings') }}#plan" class="btn btn-sm btn-primary w-100">
                                        <i class="ti ti-crown me-1"></i> Plantillas premium en Plus
                                    </a>
                                @endif
                            @elseif($defaultCompanyId)
                                <button type="button"
                                        class="btn btn-primary btn-sm w-100 wn-tvpik-use-template"
                                        data-template-key="{{ $key }}"
                                        data-template-label="{{ $tpl['label'] }}"
                                        data-layout="{{ $layout }}"
                                        @if($previewSlug) data-slug="{{ $previewSlug }}" @endif>
                                    Usar
                                </button>
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm wn-tvpik-copy-template-url"
                                        title="Copiar enlace para la TV"
                                        data-template-key="{{ $key }}"
                                        data-layout="{{ $layout }}"
                                        @if($previewSlug) data-slug="{{ $previewSlug }}" @endif>
                                    <i class="ti ti-link"></i>
                                </button>
                            @else
                                <span class="text-muted small">Selecciona un negocio para empezar.</span>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</div>
