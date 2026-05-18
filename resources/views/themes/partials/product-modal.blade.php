<div class="details-modal modal fade bd-example-modal-lg" id="dishDetails{{ $product->id }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close modal-close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                @include('themes.partials.product-modal-media', ['product' => $product])
            </div>
            <div>
                <div class="modal.body container">
                    <div class="row">
                        <div class="col-12">
                            <h3>{{ $product->name }} @include('themes.partials.product-highlight-badge', ['product' => $product])</h3>
                        </div>
                    </div>
                    <p>{{ $product->description }}</p>
                </div>
            </div>
            <div class="row text-center">
                <div class="col-6 price half-dish">
                    @if($product->price_portion)
                    Media:
                    {{ $product->price_portion }}€
                    @endif
                </div>
                <div class="col-6 price whole-dish">
                    @if($product->price_portion)
                        Entera:
                    @endif
                    {{ $product->price_unit }} €
                    @include('themes.partials.product-price-suffix', ['product' => $product])
                </div>
            </div>
            <div class="alergenos text-center">
                @foreach ($product->allergens as $allergen)
                    <img src="{{ URL::to('/').'/img/'.$allergen->image }}" alt="{{ $allergen->name }}">
                @endforeach
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-success btn-lg btn-block align-center" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
