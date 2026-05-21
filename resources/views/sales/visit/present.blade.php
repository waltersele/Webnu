@extends('sales.present-layout')

@section('title', 'Presentar — ' . $visit->name)

@section('content')
<div class="wn-sales-present">
    <div class="wn-sales-present__preview">
        <iframe id="wn-sales-present-frame"
                title="Vista previa carta"
                src=""
                loading="lazy"></iframe>
    </div>

    <div class="wn-sales-present__bar">
        <div class="wn-sales-present__bar-scroll">
            <p class="wn-sales-present__label">Plantilla</p>
            <div class="wn-sales-present__chips" id="wn-sales-templates">
                @foreach ($templates as $key => $meta)
                    <button type="button"
                            class="wn-sales-present__chip {{ ($visit->template ?: 'lumiere') === $key ? 'is-active' : '' }}"
                            data-template="{{ $key }}">
                        {{ $meta['label'] ?? $key }}
                    </button>
                @endforeach
            </div>

            @if (! empty($presets))
                <p class="wn-sales-present__label">Paletas</p>
                <div class="wn-sales-present__chips" id="wn-sales-presets">
                    @foreach ($presets as $templateKey => $templatePresets)
                        @foreach ($templatePresets as $presetName => $colors)
                            <button type="button"
                                    class="wn-sales-present__chip wn-sales-present__chip--preset"
                                    data-preset-template="{{ $templateKey }}"
                                    data-preset-colors="{{ json_encode($colors) }}">
                                {{ $presetName }}
                            </button>
                        @endforeach
                    @endforeach
                </div>
            @endif

            <p class="wn-sales-present__label">Colores</p>
            <div class="wn-sales-present__colors">
                @foreach ($colorKeys as $key => $label)
                    <label class="wn-sales-present__color">
                        <span>{{ $label }}</span>
                        <input type="color"
                               class="wn-sales-theme-color"
                               name="theme_{{ $key }}"
                               data-key="{{ $key }}"
                               value="{{ $resolvedTheme[$key] ?? '#0074d9' }}">
                    </label>
                @endforeach
            </div>
        </div>

        <div class="wn-sales-present__actions">
            <button type="button" class="wn-sales-btn wn-sales-btn-primary" id="wn-sales-save-design">
                Guardar diseño
            </button>
            <div class="wn-sales-present__links">
                <a href="{{ route('sales.demo-products.index', $visit->id) }}">Fotos demo</a>
                <a href="{{ route('sales.handoff.show', $visit->id) }}">Cerrar venta</a>
                <a href="{{ route('sales.visit.show', $visit->id) }}">Visita</a>
            </div>
        </div>
    </div>
</div>

<script>
window.WebnuSalesPresent = {
    companyId: {{ $visit->id }},
    previewUrl: @json($previewUrl),
    template: @json($visit->template ?: 'lumiere'),
    saveUrl: @json(route('sales.visit.design.update', $visit->id)),
    csrfToken: @json(csrf_token())
};
</script>
<script src="{{ asset('js/webnu-sales-present.js') }}"></script>
@endsection
