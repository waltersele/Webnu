@php
    $meta = \App\Services\AllergenCatalogService::metaFor($allergen);
    $slug = $meta['slug'] ?? 'gluten';
    $size = (int) ($size ?? 40);
@endphp
<svg class="webnu-allergen-icon"
     viewBox="0 0 48 48"
     width="{{ $size }}"
     height="{{ $size }}"
     role="img"
     aria-label="{{ $allergen->name }}">
    <title>{{ $allergen->name }}</title>
    <circle cx="24" cy="24" r="24" fill="{{ $meta['color'] }}"/>
    <use href="#allergen-{{ $slug }}" xlink:href="#allergen-{{ $slug }}" x="12" y="12" width="24" height="24" fill="#ffffff" color="#ffffff"/>
</svg>

