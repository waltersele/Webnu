@if($product->allergens->count())
@php
    $allergenLocale = $menuLocale ?? 'es';
    $allergenAria = isset($menuLocaleService)
        ? $menuLocaleService->uiLabel('allergens', $allergenLocale)
        : 'Alérgenos';
@endphp
<ul class="wn-allergens" aria-label="{{ $allergenAria }}">
    @foreach ($product->allergens as $allergen)
        @php
            $meta = \App\Services\AllergenCatalogService::metaFor($allergen);
            $slug = $meta['slug'] ?? null;
            $label = isset($menuLocaleService) && $slug
                ? $menuLocaleService->allergenLabel($slug, $allergenLocale)
                : ($meta['name'] ?? $allergen->name);
            $svgRel = $slug ? 'img/alergenos/' . $slug . '.svg' : null;
            $hasSvg = $svgRel && is_file(public_path($svgRel));
            $color = $meta['color'] ?? '#78909C';
        @endphp
        <li class="wn-allergens__item" title="{{ $label }}" style="--wn-allergen-color: {{ $color }}">
            @if($hasSvg)
                <img class="wn-allergens__icon" src="{{ asset($svgRel) }}" alt="" width="18" height="18" loading="lazy" decoding="async">
            @endif
            <span>{{ $label }}</span>
        </li>
    @endforeach
</ul>
@endif
