@if ($product->image || $product->video)
    <div class="text-right">
        <a class="details-button text-right" data-toggle="modal" data-target="#dishDetails{{ $product->id }}" href="#" title="Ver foto o vídeo">
            @if ($product->video)
                @include('themes.partials.icons.svg-play')
            @else
                @include('themes.partials.icons.svg-camera')
            @endif
        </a>
    </div>
@endif
