@if($product->allergens->count())
<ul class="wn-allergens" aria-label="Alérgenos">
    @foreach ($product->allergens as $allergen)
        <li class="wn-allergens__item">
            <img src="{{ $allergen->iconUrl() }}" alt="" width="20" height="20">
            <span>{{ $allergen->name }}</span>
        </li>
    @endforeach
</ul>
@endif
