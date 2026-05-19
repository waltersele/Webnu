@extends('admin.layout')

@section('page_title', 'Escaneo con IA')
@section('page_subtitle', 'Configuración de Gemini para digitalizar cartas')

@section('page_actions')
    <a href="{{ route('admin.platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <p class="text-muted">
                    Los restaurantes podrán escanear su carta desde el móvil. La clave se guarda cifrada en el servidor.
                    También puedes definirla en <code>.env</code> como respaldo (<code>GEMINI_API_KEY</code>).
                </p>

                @if ($geminiConfigured)
                    <div class="alert alert-success py-2">
                        <i class="ri-check-line me-1"></i> API configurada
                        @if ($geminiKeyHint)
                            <span class="text-muted">({{ $geminiKeyHint }})</span>
                        @endif
                    </div>
                @else
                    <div class="alert alert-warning py-2">
                        Sin API de Gemini. El escaneo con IA no estará disponible para los clientes.
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.platform.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="gemini_api_key" class="form-label">API key de Gemini</label>
                        <input type="password" name="gemini_api_key" id="gemini_api_key" class="form-control font-monospace"
                               placeholder="{{ $geminiConfigured ? 'Dejar vacío para no cambiar' : 'Pega tu clave de Google AI Studio' }}"
                               autocomplete="off">
                        <div class="form-text">
                            Obtén la clave en
                            <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener">Google AI Studio</a>.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="gemini_model" class="form-label">Modelo</label>
                        <select name="gemini_model" id="gemini_model" class="form-select">
                            @foreach ($recommendedModels as $id => $label)
                                <option value="{{ $id }}" @if(old('gemini_model', $geminiModel) === $id) selected @endif>
                                    {{ $label }} ({{ $id }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">
                            <code>gemini-2.0-flash</code> aparece en la lista de Google pero devuelve error 404; usa <code>gemini-2.5-flash-lite</code>.
                            Si hay error 429 (cuota), prueba <code>gemini-flash-lite-latest</code>.
                            Revisa límites en <a href="https://aistudio.google.com/" target="_blank" rel="noopener">Google AI Studio</a>.
                        </div>
                    </div>

                    @if ($geminiConfigured)
                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" name="clear_gemini_key" value="1" id="clear_gemini_key">
                            <label class="form-check-label text-danger" for="clear_gemini_key">Eliminar la API key guardada</label>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="submit" class="btn btn-outline-primary ms-2"
                            formaction="{{ route('admin.platform.settings.test-gemini') }}">
                        <i class="ri-link me-1"></i> Probar conexión con Gemini
                    </button>
                    <p class="text-muted small mt-2 mb-0">La prueba usa la API key y el modelo del formulario (aunque no hayas guardado).</p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

