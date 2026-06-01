@php
    $galleryId = $galleryId ?? 'wn-tvpik-gallery';
@endphp
<div class="wn-tvpik-gallery__filters" role="tablist" data-gallery-filters-for="{{ $galleryId }}">
    <button type="button" class="wn-tvpik-gallery__filter is-active" data-filter="all">Todas</button>
    <button type="button" class="wn-tvpik-gallery__filter" data-filter="standard">Estándar</button>
    <button type="button" class="wn-tvpik-gallery__filter" data-filter="premium">
        <i class="ti ti-star-filled me-1" style="font-size:0.75rem"></i> Premium
    </button>
</div>
