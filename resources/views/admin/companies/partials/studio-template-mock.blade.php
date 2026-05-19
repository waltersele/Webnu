@php
    $d = config('company_templates.defaults.' . $key, config('company_templates.defaults.basic'));
    $isDark = in_array($key, ['lumiere', 'nocturne', 'oriental'], true);
    $isCatalog = $key === 'catalogo';
@endphp
<div class="wn-tmock wn-tmock--{{ $key }} {{ $isDark ? 'wn-tmock--dark' : '' }} {{ $isCatalog ? 'wn-tmock--catalog' : '' }}"
    style="--tm-primary: {{ $d['primary'] }}; --tm-accent: {{ $d['accent'] }}; --tm-bg: {{ $d['background'] }}; --tm-surface: {{ $d['surface'] }}; --tm-text: {{ $d['text'] }}; --tm-muted: {{ $d['text_muted'] }};">
    <div class="wn-tmock__screen">
        <div class="wn-tmock__header">
            <span class="wn-tmock__logo"></span>
            <span class="wn-tmock__brand">La Taberna</span>
        </div>
        @if(in_array($key, ['lumiere', 'nocturne'], true))
            <div class="wn-tmock__hero">
                <span class="wn-tmock__hero-title">Tartar de salmón</span>
                <span class="wn-tmock__hero-price">14,50 €</span>
            </div>
        @endif
        <div class="wn-tmock__nav">
            <span class="is-active">Entrantes</span>
            <span>Principales</span>
            <span>Postres</span>
        </div>
        @if($isCatalog)
            <div class="wn-tmock__row">
                <span class="wn-tmock__thumb"></span>
                <div class="wn-tmock__row-body">
                    <span class="wn-tmock__dish">Gazpacho andaluz</span>
                    <span class="wn-tmock__desc">Tomate, pepino</span>
                </div>
                <span class="wn-tmock__price">6,50 €</span>
            </div>
            <div class="wn-tmock__row">
                <span class="wn-tmock__thumb"></span>
                <div class="wn-tmock__row-body">
                    <span class="wn-tmock__dish">Croquetas jamón</span>
                    <span class="wn-tmock__desc">4 uds.</span>
                </div>
                <span class="wn-tmock__price">8,00 €</span>
            </div>
        @else
            <div class="wn-tmock__card">
                @if(in_array($key, ['temporada', 'visual', 'bistro'], true))
                    <span class="wn-tmock__card-img"></span>
                @endif
                <div class="wn-tmock__card-body">
                    <span class="wn-tmock__dish">Risotto de setas</span>
                    <span class="wn-tmock__desc">Parmesano, trufa</span>
                    <span class="wn-tmock__price">12,00 €</span>
                </div>
            </div>
            <div class="wn-tmock__card">
                @if(in_array($key, ['temporada', 'visual', 'bistro'], true))
                    <span class="wn-tmock__card-img"></span>
                @endif
                <div class="wn-tmock__card-body">
                    <span class="wn-tmock__dish">Solomillo</span>
                    <span class="wn-tmock__desc">Patata, pimientos</span>
                    <span class="wn-tmock__price">18,50 €</span>
                </div>
            </div>
        @endif
    </div>
</div>

