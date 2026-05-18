@if ($product->image || $product->video)
    <div class="text-right">
        <a class="details-button text-right" data-toggle="modal" data-target="#dishDetails{{ $product->id }}" href="#" title="Ver foto o vídeo">
            @if ($product->video)
                <i class="fas fa-play-circle"></i>
            @else
                <i class="fas fa-camera"></i>
            @endif
        </a>
    </div>
@endif
