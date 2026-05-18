@php
    $imgUrl = $product->image ? URL::to('/') . '/img/' . $product->image : null;
    $videoUrl = $product->video ? URL::to('/') . '/img/' . $product->video : null;
@endphp

@if ($imgUrl || $videoUrl)
    <div class="product-modal-media">
        @if ($imgUrl && !$videoUrl)
            <img class="img-responsive modal-img" src="{{ $imgUrl }}" alt="{{ $product->name }}">
        @elseif ($videoUrl)
            <video class="product-modal-video" controls playsinline preload="metadata" @if($imgUrl) poster="{{ $imgUrl }}" @endif>
                <source src="{{ $videoUrl }}" type="video/mp4">
                <source src="{{ $videoUrl }}" type="video/webm">
                Tu navegador no reproduce vídeo. <a href="{{ $videoUrl }}">Descargar</a>
            </video>
            @if ($imgUrl)
                <img class="img-responsive modal-img mt-2" src="{{ $imgUrl }}" alt="{{ $product->name }}">
            @endif
        @endif
    </div>
@endif
