@php
    $isAdd = ($mode ?? 'add') === 'add';
    $pickerKey = $isAdd ? 'add' : 'modify';
@endphp
<div class="webnu-allergen-picker" id="product-{{ $pickerKey }}-allergens-picker" data-picker="{{ $pickerKey }}">
    <p class="text-muted small mb-3">Toca un icono para marcar los alérgenos que contiene este plato.</p>
    @if(isset($allergens) && $allergens->count())
        <div class="webnu-allergen-picker__grid">
            @foreach ($allergens as $allergen)
                @php $meta = \App\Services\AllergenCatalogService::metaFor($allergen); @endphp
                <label class="webnu-allergen-chip" style="--allergen-color: {{ $meta['color'] }};">
                    <input type="checkbox"
                           class="webnu-allergen-chip__input"
                           name="allergens[]"
                           value="{{ $allergen->id }}"
                           @if(!empty($selectedAllergenIds) && in_array($allergen->id, $selectedAllergenIds, true)) checked @endif>
                    <span class="webnu-allergen-chip__box">
                        @include('admin.sections.partials.allergen-icon', ['allergen' => $allergen, 'size' => 36])
                        <span class="webnu-allergen-chip__name">{{ $allergen->name }}</span>
                    </span>
                </label>
            @endforeach
        </div>
    @else
        <div class="alert alert-warning mb-0" role="alert">
            No hay alérgenos en la base de datos. Recarga la página para sincronizar el catálogo.
        </div>
    @endif
</div>
