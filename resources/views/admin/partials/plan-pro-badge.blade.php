@php
    $label = $label ?? 'Plus';
    $size = $size ?? 'sm';
@endphp
<span class="wn-plan-pro-badge wn-plan-pro-badge--{{ $size }} wn-plan-pro-badge--{{ strtolower($label) === 'ilimitado' ? 'unlimited' : 'plus' }}" title="Disponible con plan {{ $label }}">{{ $label }}</span>
