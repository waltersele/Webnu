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

            $iconRelative = ltrim((string) $allergen->image, '/');
            $iconAbsolute = $iconRelative !== '' ? public_path('img/' . $iconRelative) : '';
            $svgInline = '';
            if ($iconRelative !== '' && preg_match('/\.svg$/i', $iconRelative) && is_file($iconAbsolute)) {
                $svgInline = @file_get_contents($iconAbsolute) ?: '';
                if ($svgInline !== '') {
                    $svgInline = preg_replace('/<\?xml[^>]*\?>/', '', $svgInline);
                    $svgInline = preg_replace('/<!--.*?-->/s', '', $svgInline);
                    if (! preg_match('/\sclass=/', $svgInline)) {
                        $svgInline = preg_replace('/<svg\b/', '<svg class="wn-allergens__icon"', $svgInline, 1);
                    }
                    if (! preg_match('/\swidth=/', $svgInline)) {
                        $svgInline = preg_replace('/<svg\b/', '<svg width="20" height="20"', $svgInline, 1);
                    }
                    if (! preg_match('/\saria-hidden=/', $svgInline)) {
                        $svgInline = preg_replace('/<svg\b/', '<svg aria-hidden="true"', $svgInline, 1);
                    }
                }
            }
        @endphp
        <li class="wn-allergens__item">
            @if($svgInline !== '')
                {!! $svgInline !!}
            @else
                <img class="wn-allergens__icon" src="{{ $allergen->iconUrl() }}" alt="" width="20" height="20" loading="lazy" decoding="async">
            @endif
            <span>{{ $label }}</span>
        </li>
    @endforeach
</ul>
@endif
