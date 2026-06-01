@php
    $selectId = $selectId ?? 'menu-base-locale';
    $selectName = $selectName ?? 'default_locale';
    $currentValue = $currentValue ?? 'es';
    $browserLocale = $browserLocale ?? null;
    $supportedLocales = $supportedLocales ?? config('menu_locales.supported', []);
    $wrapperClass = $wrapperClass ?? 'mb-4';
    $selectClass = $selectClass ?? 'form-select';
    $labelClass = $labelClass ?? 'form-label fw-medium';
@endphp

<div class="{{ $wrapperClass }}" data-menu-base-locale-field>
    <label for="{{ $selectId }}" class="{{ $labelClass }}">
        {{ $label ?? 'Idioma en el que escribes tu carta' }}
    </label>
    <select id="{{ $selectId }}"
            name="{{ $selectName }}"
            class="{{ $selectClass }}"
            data-locale-base-select
            required>
        @foreach ($supportedLocales as $code => $meta)
            <option value="{{ $code }}" {{ $currentValue === $code ? 'selected' : '' }}>
                {{ $meta['native'] ?? $meta['label'] }} ({{ strtoupper($code) }})
            </option>
        @endforeach
    </select>
    @if ($browserLocale)
        <p class="form-text mb-0 {{ $hintClass ?? 'text-muted small' }}">
            @if ($currentValue === $browserLocale)
                Detectado desde tu navegador. Puedes cambiarlo si escribes la carta en otro idioma.
            @else
                @php $browserMeta = $supportedLocales[$browserLocale] ?? null; @endphp
                Tu navegador sugiere
                <strong>{{ $browserMeta['native'] ?? strtoupper($browserLocale) }}</strong>.
                Elige el idioma en el que redactas platos y categorías.
            @endif
        </p>
    @endif
    @error('default_locale')
        <p class="text-danger small mb-0 mt-1">{{ $message }}</p>
    @enderror
</div>
