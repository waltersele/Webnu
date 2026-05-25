@php
    $shareUrl = $shareUrl ?? (isset($company) ? $company->publicUrl() : '');
    $sharePath = $sharePath ?? (isset($company) ? 'webnu.es/' . $company->publicPath() : '');
    $shareTitle = $shareTitle ?? (isset($company) ? $company->name : 'Mi carta');
    $compact = !empty($compact);
@endphp
@if($shareUrl)
<div class="wn-share-menu{{ $compact ? ' wn-share-menu--compact' : '' }}"
     data-share-menu
     data-share-url="{{ $shareUrl }}"
     data-share-title="{{ $shareTitle }}">
    @unless($compact)
        <p class="wn-share-menu__lead text-muted small mb-3">
            Copia el enlace o compártelo en redes, WhatsApp o email para que tus clientes vean la carta al instante.
        </p>
    @endunless
    <label class="form-label small text-muted mb-1" for="wn-share-url-{{ md5($shareUrl) }}">Enlace público de tu carta</label>
    <div class="input-group mb-3">
        <input type="text"
               class="form-control font-monospace"
               id="wn-share-url-{{ md5($shareUrl) }}"
               value="{{ $shareUrl }}"
               readonly
               data-share-input>
        <button type="button" class="btn btn-primary" data-share-copy title="Copiar enlace">
            <i class="ti ti-copy"></i>
            <span class="d-none d-sm-inline ms-1">Copiar</span>
        </button>
    </div>
    <p class="small text-muted mb-2"><code>{{ $sharePath }}</code></p>
    <p class="small text-success mb-3 d-none" data-share-feedback role="status"></p>

    <div class="wn-share-menu__actions d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm d-none" data-share-native>
            <i class="ti ti-share me-1"></i> Compartir…
        </button>
        <a href="https://wa.me/?text={{ rawurlencode('Mira nuestra carta: ' . $shareUrl) }}"
           class="btn btn-outline-success btn-sm"
           data-share-channel="whatsapp"
           target="_blank"
           rel="noopener noreferrer">
            <i class="ti ti-brand-whatsapp me-1"></i> WhatsApp
        </a>
        <a href="https://www.facebook.com/sharer/sharer.php?u={{ rawurlencode($shareUrl) }}"
           class="btn btn-outline-primary btn-sm"
           data-share-channel="facebook"
           target="_blank"
           rel="noopener noreferrer">
            <i class="ti ti-brand-facebook me-1"></i> Facebook
        </a>
        <a href="https://twitter.com/intent/tweet?url={{ rawurlencode($shareUrl) }}&text={{ rawurlencode('Carta de ' . $shareTitle) }}"
           class="btn btn-outline-secondary btn-sm"
           data-share-channel="twitter"
           target="_blank"
           rel="noopener noreferrer">
            <i class="ti ti-brand-x me-1"></i> X
        </a>
        <a href="mailto:?subject={{ rawurlencode('Carta de ' . $shareTitle) }}&body={{ rawurlencode("Mira nuestra carta digital:\n\n" . $shareUrl) }}"
           class="btn btn-outline-secondary btn-sm"
           data-share-channel="email">
            <i class="ti ti-mail me-1"></i> Email
        </a>
        <a href="{{ $shareUrl }}" target="_blank" rel="noopener" class="btn btn-outline-secondary btn-sm">
            <i class="ti ti-external-link me-1"></i> Abrir carta
        </a>
    </div>
</div>
@endif
