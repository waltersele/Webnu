@if ($product->video)
    <a href="#" class="product-video-play-link" data-toggle="modal" data-target="#dishDetails{{ $product->id }}" title="Ver vídeo">
        @if ($product->image)
            <img class="img-responsive" src="{{ URL::to('/') . '/img/' . $product->image }}" alt="{{ $product->name }}">
            <i class="fas fa-play-circle"></i>
        @else
            <div class="product-video-placeholder">
                <i class="fas fa-play-circle fa-3x"></i>
            </div>
        @endif
    </a>
@elseif ($product->image)
    <img class="img-responsive" src="{{ URL::to('/') . '/img/' . $product->image }}" alt="{{ $product->name }}">
@endif
