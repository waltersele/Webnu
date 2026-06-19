<link rel="stylesheet" href="{{ asset('css/themes/front-suspended-overlay.css') }}">
<div class="wn-suspended-overlay" role="dialog" aria-modal="true" aria-labelledby="wn-suspended-title">
    <div class="wn-suspended-overlay__inner">
        <img class="wn-suspended-overlay__logo" src="{{ \App\PlatformSetting::brandUrl('logo_white') }}" alt="Webnu" decoding="async">
        <h1 id="wn-suspended-title" class="wn-suspended-overlay__title">Parece que este usuario ya no está usando Webnu</h1>
        <p class="wn-suspended-overlay__hint">
            <a href="{{ route('login', ['redirect' => url()->current()]) }}" class="wn-suspended-overlay__link">Si eres el propietario, haz login</a>
        </p>
    </div>
</div>
