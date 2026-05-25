@php
    $d = config('company_templates.defaults.' . $key, config('company_templates.defaults.basic'));
    $isDark = in_array($key, ['lumiere', 'nocturne', 'oriental'], true);
    $isCatalog = $key === 'catalogo';

    $sampleMenu = config('menu_demo.sample_menu', []);
    $samples = [];
    foreach ($sampleMenu as $section) {
        foreach (($section['products'] ?? []) as $product) {
            if (! empty($product['image'])) {
                $samples[] = $product;
            }
            if (count($samples) >= 4) {
                break 2;
            }
        }
    }
    if (count($samples) < 3) {
        $samples = [
            ['name' => 'Burrata', 'description' => 'Tomate, pesto', 'price_unit' => '12,50 €', 'image' => 'productos/brasa-burrata.jpg'],
            ['name' => 'Solomillo', 'description' => 'Pedro Ximénez', 'price_unit' => '24,50 €', 'image' => 'productos/brasa-solomillo.jpg'],
            ['name' => 'Tarta de queso', 'description' => 'Coulis frutos rojos', 'price_unit' => '7,50 €', 'image' => 'productos/brasa-tarta-queso.jpg'],
            ['name' => 'Brownie', 'description' => 'Helado vainilla', 'price_unit' => '6,50 €', 'image' => 'productos/brasa-brownie.jpg'],
        ];
    }
    $heroSample = $samples[1] ?? $samples[0];
@endphp
<div class="wn-tmock wn-tmock--{{ $key }} {{ $isDark ? 'wn-tmock--dark' : '' }} {{ $isCatalog ? 'wn-tmock--catalog' : '' }}"
    style="--tm-primary: {{ $d['primary'] }}; --tm-accent: {{ $d['accent'] }}; --tm-bg: {{ $d['background'] }}; --tm-surface: {{ $d['surface'] }}; --tm-text: {{ $d['text'] }}; --tm-muted: {{ $d['text_muted'] }};">
    <div class="wn-tmock__screen">
        <div class="wn-tmock__header">
            <span class="wn-tmock__logo"></span>
            <span class="wn-tmock__brand">La Taberna</span>
        </div>
        @if(in_array($key, ['lumiere', 'nocturne'], true))
            <div class="wn-tmock__hero" style="background-image: url('{{ asset('img/' . $heroSample['image']) }}');">
                <span class="wn-tmock__hero-title">{{ $heroSample['name'] }}</span>
                <span class="wn-tmock__hero-price">{{ $heroSample['price_unit'] }}</span>
            </div>
        @endif
        <div class="wn-tmock__nav">
            <span class="is-active">Entrantes</span>
            <span>Principales</span>
            <span>Postres</span>
        </div>
        @if($isCatalog)
            @foreach(array_slice($samples, 0, 2) as $p)
                <div class="wn-tmock__row">
                    <span class="wn-tmock__thumb"><img src="{{ asset('img/' . $p['image']) }}" alt="" loading="lazy" decoding="async"></span>
                    <div class="wn-tmock__row-body">
                        <span class="wn-tmock__dish">{{ $p['name'] }}</span>
                        <span class="wn-tmock__desc">{{ $p['description'] }}</span>
                    </div>
                    <span class="wn-tmock__price">{{ $p['price_unit'] }}</span>
                </div>
            @endforeach
        @else
            @foreach(array_slice($samples, 0, 2) as $p)
                <div class="wn-tmock__card">
                    <span class="wn-tmock__card-img"><img src="{{ asset('img/' . $p['image']) }}" alt="" loading="lazy" decoding="async"></span>
                    <div class="wn-tmock__card-body">
                        <span class="wn-tmock__dish">{{ $p['name'] }}</span>
                        <span class="wn-tmock__desc">{{ $p['description'] }}</span>
                        <span class="wn-tmock__price">{{ $p['price_unit'] }}</span>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
