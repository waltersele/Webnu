@php
    $variant = $variant ?? 'card';
@endphp
@if($variant === 'card')
<div id="app" class="card mb-4 wn-pwa-install-card" data-pwa-card>
    <div class="card-header">
        <h5 class="card-title mb-0">Usar Webnu como app</h5>
    </div>
    <div class="card-body">
        <p class="text-muted mb-3">
            Instala el panel en la pantalla de inicio de tu móvil o tablet. Se abre a pantalla completa, con el icono de Webnu, sin pasar por la tienda de aplicaciones.
        </p>
        @include('admin.partials.pwa-install-body')
    </div>
</div>
@elseif($variant === 'compact')
<div class="wn-pwa-install-compact" data-pwa-card>
    <p class="wn-pwa-install-compact__title"><i class="ti ti-device-mobile me-1"></i> Instala Webnu en tu móvil</p>
    <p class="wn-pwa-install-compact__lead small text-muted mb-2">Accede al panel como una app, más rápido desde la pantalla de inicio.</p>
    @include('admin.partials.pwa-install-body')
</div>
@elseif($variant === 'dropdown')
<div class="wn-pwa-dropdown-panel p-3" data-pwa-card style="min-width: 260px;">
    <p class="fw-semibold mb-2">Instalar Webnu</p>
    @include('admin.partials.pwa-install-body')
</div>
@endif
