@php
    $brandKey = $brandKey ?? 'logo';
    $brandClass = $brandClass ?? '';
@endphp
<img src="{{ \App\PlatformSetting::brandUrl($brandKey) }}" alt="Webnu" @if($brandClass !== '') class="{{ $brandClass }}" @endif loading="eager" decoding="async"/>
