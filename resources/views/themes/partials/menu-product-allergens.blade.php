@if($product->allergens->count())
@php
    $allergenLocale = $menuLocale ?? 'es';
@endphp
<ul class="wn-allergens" aria-label="{{ isset($menuLocaleService) ? $menuLocaleService->uiLabel('allergens', $allergenLocale) : 'Alérgenos' }}">
    @foreach ($product->allergens as $allergen)
        @php
            $slug = \App\Services\AllergenCatalogService::slugFromAllergen($allergen);
            $label = isset($menuLocaleService) && $slug
                ? $menuLocaleService->allergenLabel($slug, $allergenLocale)
                : $allergen->name;
        @endphp
        <li class="wn-allergens__item">
            <img src="{{ $allergen->iconUrl() }}" alt="" width="20" height="20">
            <span>{{ $label }}</span>
        </li>
    @endforeach
</ul>
@endif
