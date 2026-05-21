@extends('sales.layout')

@section('title', 'Fotos demo')

@section('content')
<p style="margin: 0 0 0.25rem;"><a href="{{ route('sales.visit.show', $visit->id) }}" style="color: #64748b; font-size: 0.9rem;">← {{ $visit->name }}</a></p>
<h1 style="font-size: 1.25rem; margin: 0 0 0.5rem;">Platos con foto</h1>
<p style="color: #64748b; font-size: 0.9rem; margin: 0 0 1rem;">
    Puedes destacar hasta {{ $maxPhotos }} platos. Te quedan {{ $photoSlotsRemaining }}.
</p>

@forelse ($products as $product)
    <div class="wn-sales-card wn-sales-product-row" style="flex-direction: column; align-items: stretch;">
        <div style="display: flex; gap: 0.75rem; align-items: center;">
            @if ($product->image)
                <img src="{{ $product->image_url }}" alt="" class="wn-sales-product-thumb">
            @else
                <div class="wn-sales-product-thumb"></div>
            @endif
            <div>
                <strong>{{ $product->name }}</strong>
                <span style="display: block; color: #64748b; font-size: 0.85rem;">{{ $product->section->name ?? '' }}</span>
                @if ($product->sales_demo_highlight)
                    <span style="font-size: 0.75rem; color: #2563eb;">Foto demo</span>
                @endif
            </div>
        </div>
        <form method="POST" action="{{ route('sales.demo-products.update', [$visit->id, $product->id]) }}" enctype="multipart/form-data" style="margin-top: 0.75rem;">
            @csrf
            <input type="file" name="photo" accept="image/*" capture="environment" style="margin-bottom: 0.5rem; width: 100%;">
            <button type="submit" class="wn-sales-btn wn-sales-btn-outline" style="padding: 0.5rem;">Subir foto</button>
        </form>
        @if ($product->sales_demo_highlight)
            <form method="POST" action="{{ route('sales.demo-products.update', [$visit->id, $product->id]) }}" style="margin-top: 0.35rem;">
                @csrf
                <input type="hidden" name="clear_photo" value="1">
                <button type="submit" class="btn btn-link btn-sm text-danger p-0">Quitar foto demo</button>
            </form>
        @endif
    </div>
@empty
    <p style="color: #64748b;">Importa la carta antes de añadir fotos.</p>
@endforelse
@endsection
