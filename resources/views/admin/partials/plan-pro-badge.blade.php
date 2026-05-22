@php
    $size = $size ?? 'md';
    $label = $label ?? 'Pro';
    $slug = strtolower($label);
    $variant = in_array($slug, ['plus', 'pro', 'free'], true) ? $slug : 'pro';
@endphp
<span class="wn-plan-pro-badge wn-plan-pro-badge--{{ $size }} wn-plan-pro-badge--{{ $variant }}" title="Disponible con plan {{ $label }}">{{ $label }}</span>
