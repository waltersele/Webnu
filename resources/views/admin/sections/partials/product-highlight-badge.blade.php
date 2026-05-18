@php
    $highlight = $highlight ?? null;
    if (!$highlight) {
        return;
    }
    $meta = config('product_highlights.options.' . $highlight);
    if (!$meta) {
        return;
    }
    $size = $size ?? 'md';
    $class = 'webnu-product-badge ' . ($meta['class'] ?? '');
    if ($size === 'sm') {
        $class .= ' webnu-product-badge--sm';
    }
@endphp
<span class="{{ $class }}">{{ $meta['badge'] }}</span>
