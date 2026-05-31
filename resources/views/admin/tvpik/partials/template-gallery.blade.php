@php
    $galleryId = $galleryId ?? 'wn-tvpik-gallery';
    $showFilter = $showFilter ?? true;
@endphp
<div class="wn-tvpik-gallery" id="{{ $galleryId }}">
    @if($showFilter)
        <div class="wn-tvpik-gallery__filters mb-3" role="tablist">
            <button type="button" class="wn-tvpik-gallery__filter is-active" data-filter="all">Todas</button>
            <button type="button" class="wn-tvpik-gallery__filter" data-filter="standard">Estándar</button>
            <button type="button" class="wn-tvpik-gallery__filter" data-filter="premium">Premium</button>
        </div>
    @endif
    <div class="row g-3">
        @foreach($templates as $key => $tpl)
            @php
                $thumb = $tpl['thumbnail'] ?? ('img/tvpik/previews/' . ($tpl['layout'] ?? $key) . '.svg');
                $previewCompany = $company ?? $companies->firstWhere('id', $defaultCompanyId);
                $previewSlug = $previewCompany ? $previewCompany->slug : null;
                $isPremiumTpl = ! empty($tpl['premium']);
                $tplLocked = ! $canTvpik || ($isPremiumTpl && ! $canTvpikPremium);
                $filterClass = $isPremiumTpl ? 'premium' : 'standard';
            @endphp
            <div class="col-md-6 col-lg-3" data-template-filter="{{ $filterClass }}">
                <article class="wn-tvpik-template-card {{ $tplLocked ? 'wn-tvpik-template-card--locked' : '' }}">
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
                                <span class="badge bg-warning text-dark ms-1">Premium</span>
                            @endif
                        </h6>
                        <p class="wn-tvpik-template-card__desc">{{ $tpl['description'] }}</p>
                        @if(!empty($tpl['duration_hint']))
                            <p class="wn-tvpik-template-card__hint">{{ $tpl['duration_hint'] }}</p>
                        @endif
                        <div class="wn-tvpik-template-card__actions">
                            @if($tplLocked)
                                @if(! $canTvpik)
                                    <a href="{{ route('admin.settings') }}#plan" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-crown me-1"></i> Activar plan
                                    </a>
                                @elseif($isPremiumTpl && ! $canTvpikPremium)
                                    <a href="{{ route('admin.settings') }}#plan" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-crown me-1"></i> Plantillas premium en Plus
                                    </a>
                                @endif
                            @elseif($defaultCompanyId)
                                <form method="GET" action="{{ route('admin.tvpik.preview') }}" target="_blank" class="d-inline">
                                    <input type="hidden" name="company_id" value="{{ $defaultCompanyId }}">
                                    <input type="hidden" name="template_key" value="{{ $key }}">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="ti ti-eye me-1"></i> Vista previa
                                    </button>
                                </form>
                                <form method="GET" action="{{ route('admin.tvpik.player') }}" target="_blank" class="d-inline">
                                    <input type="hidden" name="company_id" value="{{ $defaultCompanyId }}">
                                    <input type="hidden" name="template_key" value="{{ $key }}">
                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                        <i class="ti ti-cast me-1"></i> Reproductor
                                    </button>
                                </form>
                                @if($previewSlug)
                                    <a href="{{ route('tv.show.layout', ['companySlug' => $previewSlug, 'layout' => $tpl['layout'] ?? $key]) }}"
                                       class="btn btn-outline-secondary btn-sm"
                                       target="_blank"
                                       rel="noopener">
                                        <i class="ti ti-external-link me-1"></i> URL TV
                                    </a>
                                @endif
                            @else
                                <span class="text-muted small">Selecciona un negocio para previsualizar.</span>
                            @endif
                        </div>
                    </div>
                </article>
            </div>
        @endforeach
    </div>
</div>
