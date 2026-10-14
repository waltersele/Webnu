@php
    $meta = \App\Services\AllergenCatalogService::metaFor($allergen);
    $slug = $meta['slug'] ?? 'gluten';
    $size = (int) ($size ?? 18);
    $label = $label ?? ($meta['name'] ?? $allergen->name);
    $color = $meta['color'] ?? '#78909C';
@endphp
<svg class="webnu-allergen-icon wn-allergens__icon"
     viewBox="0 0 48 48"
     width="{{ $size }}"
     height="{{ $size }}"
     role="img"
     aria-label="{{ $label }}">
    <title>{{ $label }}</title>
    <circle cx="24" cy="24" r="24" fill="{{ $color }}"/>
    <use href="#allergen-{{ $slug }}" xlink:href="#allergen-{{ $slug }}" x="12" y="12" width="24" height="24" fill="#ffffff" color="#ffffff"/>
</svg>
