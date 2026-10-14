@if($product->allergens->count())
@php
    $allergenLocale = $menuLocale ?? 'es';
    $allergenAria = isset($menuLocaleService)
        ? $menuLocaleService->uiLabel('allergens', $allergenLocale)
        : 'Alérgenos';
    // Por defecto solo iconos homologados; pasar $showAllergenLabels = true si hace falta texto.
    $showAllergenLabels = $showAllergenLabels ?? false;
@endphp
<ul class="wn-allergens{{ $showAllergenLabels ? '' : ' wn-allergens--icons-only' }}" aria-label="{{ $allergenAria }}">
    @foreach ($product->allergens as $allergen)
        @php
            $meta = \App\Services\AllergenCatalogService::metaFor($allergen);
            $slug = $meta['slug'] ?? null;
            $label = isset($menuLocaleService) && $slug
                ? $menuLocaleService->allergenLabel($slug, $allergenLocale)
                : ($meta['name'] ?? $allergen->name);
            $color = $meta['color'] ?? '#78909C';
        @endphp
        <li class="wn-allergens__item" title="{{ $label }}" style="--wn-allergen-color: {{ $color }}">
            @include('themes.partials.allergen-icon', [
                'allergen' => $allergen,
                'label' => $label,
                // Móvil-first: en card el glifo debe leerse (~28px); con texto basta ~22px.
                'size' => $showAllergenLabels ? 22 : 28,
            ])
            @if($showAllergenLabels)
                <span>{{ $label }}</span>
            @endif
        </li>
    @endforeach
</ul>
@endif
