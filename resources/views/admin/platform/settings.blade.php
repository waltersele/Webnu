@extends('admin.layout')

@section('page_title', 'Configuración de plataforma')
@section('page_subtitle', 'Integraciones, IA, correo y contacto')

@section('page_actions')
    <a href="{{ route('admin.platform.dashboard') }}" class="btn btn-outline-secondary btn-sm">Volver</a>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="alert alert-info mb-4">
            <strong>Portal comercial.</strong>
            Gestiona roles, cierres de venta y métricas en
            <a href="{{ route('admin.platform.sales.index') }}" class="alert-link">Gestión comercial</a>.
            Los comerciales inician sesión en el
            <a href="{{ route('sales.login') }}" class="alert-link" target="_blank" rel="noopener">portal comercial</a>
            (<code>/comercial/login</code>).
            Asigna el rol «comercial» desde <a href="{{ route('admin.platform.users.index') }}" class="alert-link">Clientes</a>
            o desde Gestión comercial.
        </div>

        <form method="POST" action="{{ route('admin.platform.settings.update') }}" id="platform-settings-form">
            @csrf
            @method('PUT')

            {{-- Integraciones (API keys) --}}
            <div class="card mb-4">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="mb-0">Integraciones (API keys)</h5>
                    <a href="{{ route('admin.platform.billing.index') }}" class="btn btn-sm btn-outline-primary">Facturación Stripe</a>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Las claves sensibles se guardan cifradas en la base de datos y tienen prioridad sobre el <code>.env</code>.
                        Tras cambiar de cuenta Stripe, borra el catálogo local en Facturación y vuelve a crear los precios.
                    </p>

                    <h6 class="text-primary mt-2">Stripe (pagos)</h6>
                    @if ($integrations['stripe_secret_configured'])
                        <div class="alert alert-success py-2">
                            <i class="ri-check-line me-1"></i> Clave secreta configurada
                            @if ($integrations['stripe_secret_hint'])
                                <span class="text-muted">({{ $integrations['stripe_secret_hint'] }})</span>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning py-2 mb-3">
                            Sin <code>STRIPE_SECRET</code>. El checkout y la facturación no funcionarán hasta configurarla aquí o en <code>.env</code>.
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="stripe_key" class="form-label">Clave publicable (pk_…)</label>
                            <input type="text" name="stripe_key" id="stripe_key" class="form-control font-monospace"
                                   value="{{ old('stripe_key', $integrations['stripe_key']) }}" placeholder="pk_test_..." autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label for="stripe_secret" class="form-label">Clave secreta (sk_…)</label>
                            <input type="password" name="stripe_secret" id="stripe_secret" class="form-control font-monospace"
                                   placeholder="{{ $integrations['stripe_secret_configured'] ? 'Dejar vacío para no cambiar' : 'sk_test_...' }}"
                                   autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label for="stripe_webhook_secret" class="form-label">Webhook signing secret (whsec_…)</label>
                            <input type="password" name="stripe_webhook_secret" id="stripe_webhook_secret" class="form-control font-monospace"
                                   placeholder="{{ $integrations['stripe_webhook_configured'] ? 'Dejar vacío para no cambiar (' . ($integrations['stripe_webhook_hint'] ?? '') . ')' : 'whsec_...' }}"
                                   autocomplete="off">
                            <div class="form-text">Endpoint: <code>{{ url('stripe/webhook') }}</code></div>
                        </div>
                    </div>

                    @if ($integrations['stripe_secret_configured'])
                        <div class="form-check mb-2">
                            <input type="checkbox" class="form-check-input" name="clear_stripe_secret" value="1" id="clear_stripe_secret">
                            <label class="form-check-label text-danger" for="clear_stripe_secret">Eliminar clave secreta guardada</label>
                        </div>
                    @endif
                    @if ($integrations['stripe_webhook_configured'])
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="clear_stripe_webhook_secret" value="1" id="clear_stripe_webhook_secret">
                            <label class="form-check-label text-danger" for="clear_stripe_webhook_secret">Eliminar webhook secret guardado</label>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-outline-primary mb-4"
                            formaction="{{ route('admin.platform.settings.test-stripe') }}">
                        <i class="ri-bank-card-line me-1"></i> Probar conexión con Stripe
                    </button>

                    <hr>

                    <h6 class="text-primary">TVPik (pantallas)</h6>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="tvpik_api_url" class="form-label">URL API TVPik</label>
                            <input type="url" name="tvpik_api_url" id="tvpik_api_url" class="form-control"
                                   value="{{ old('tvpik_api_url', $integrations['tvpik_api_url']) }}" placeholder="https://api.tvpik.es">
                        </div>
                        <div class="col-md-6">
                            <label for="tvpik_web_url" class="form-label">URL app TVPik</label>
                            <input type="url" name="tvpik_web_url" id="tvpik_web_url" class="form-control"
                                   value="{{ old('tvpik_web_url', $integrations['tvpik_web_url']) }}">
                        </div>
                        <div class="col-md-6">
                            <label for="tvpik_app_key" class="form-label">App key TVPik</label>
                            <input type="password" name="tvpik_app_key" id="tvpik_app_key" class="form-control font-monospace"
                                   placeholder="{{ $integrations['tvpik_app_key_configured'] ? 'Dejar vacío para no cambiar (' . ($integrations['tvpik_app_key_hint'] ?? '') . ')' : 'Clave compartida con TVPik' }}"
                                   autocomplete="off">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="tvpik_stub_screens" value="1" id="tvpik_stub_screens"
                                       @if(old('tvpik_stub_screens', $integrations['tvpik_stub_screens'])) checked @endif>
                                <label class="form-check-label" for="tvpik_stub_screens">Pantallas de demostración (desarrollo)</label>
                            </div>
                        </div>
                    </div>
                    @if ($integrations['tvpik_app_key_configured'])
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="clear_tvpik_app_key" value="1" id="clear_tvpik_app_key">
                            <label class="form-check-label text-danger" for="clear_tvpik_app_key">Eliminar app key TVPik guardada</label>
                        </div>
                    @endif

                    <h6 class="text-primary">Cartelería digital (API /api/signage)</h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="digital_signage_app_key" class="form-label">App key cartelería</label>
                            <input type="password" name="digital_signage_app_key" id="digital_signage_app_key" class="form-control font-monospace"
                                   placeholder="{{ $integrations['digital_signage_app_key_configured'] ? 'Dejar vacío para no cambiar (' . ($integrations['digital_signage_app_key_hint'] ?? '') . ')' : 'DIGITAL_SIGNAGE_APP_KEY' }}"
                                   autocomplete="off">
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" name="digital_signage_only_enabled" value="1" id="digital_signage_only_enabled"
                                       @if(old('digital_signage_only_enabled', $integrations['digital_signage_only_enabled'])) checked @endif>
                                <label class="form-check-label" for="digital_signage_only_enabled">Sincronizar solo productos activos</label>
                            </div>
                        </div>
                    </div>
                    @if ($integrations['digital_signage_app_key_configured'])
                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" name="clear_digital_signage_app_key" value="1" id="clear_digital_signage_app_key">
                            <label class="form-check-label text-danger" for="clear_digital_signage_app_key">Eliminar app key cartelería guardada</label>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Escaneo IA --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Escaneo con IA (Gemini)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        Los restaurantes podrán escanear su carta desde el móvil. La clave se guarda cifrada.
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
                    </div>

                    @if ($geminiConfigured)
                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" name="clear_gemini_key" value="1" id="clear_gemini_key">
                            <label class="form-check-label text-danger" for="clear_gemini_key">Eliminar la API key guardada</label>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-outline-primary"
                            formaction="{{ route('admin.platform.settings.test-gemini') }}">
                        <i class="ri-link me-1"></i> Probar conexión con Gemini
                    </button>
                    <p class="text-muted small mt-2 mb-0">La prueba usa la API key y el modelo del formulario (aunque no hayas guardado).</p>
                </div>
            </div>

            {{-- Correo SMTP --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Servidor de correo (SMTP)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Configura el envío de emails de la plataforma (formularios de la landing, notificaciones, etc.).
                        Si no rellenas estos campos, se usan los valores de <code>.env</code>.
                    </p>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="mail_mailer" class="form-label">Driver</label>
                            <select name="mail_mailer" id="mail_mailer" class="form-select">
                                @foreach (['smtp' => 'SMTP', 'log' => 'Log (solo desarrollo)', 'sendmail' => 'Sendmail'] as $value => $label)
                                    <option value="{{ $value }}" @if(old('mail_mailer', $mail['mail_mailer']) === $value) selected @endif>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="mail_host" class="form-label">Host SMTP</label>
                            <input type="text" name="mail_host" id="mail_host" class="form-control"
                                   value="{{ old('mail_host', $mail['mail_host']) }}" placeholder="smtp.mailgun.org">
                        </div>
                        <div class="col-md-3">
                            <label for="mail_port" class="form-label">Puerto</label>
                            <input type="number" name="mail_port" id="mail_port" class="form-control"
                                   value="{{ old('mail_port', $mail['mail_port']) }}" min="1" max="65535">
                        </div>
                        <div class="col-md-6">
                            <label for="mail_username" class="form-label">Usuario SMTP</label>
                            <input type="text" name="mail_username" id="mail_username" class="form-control"
                                   value="{{ old('mail_username', $mail['mail_username']) }}" autocomplete="off">
                        </div>
                        <div class="col-md-6">
                            <label for="mail_password" class="form-label">Contraseña SMTP</label>
                            <input type="password" name="mail_password" id="mail_password" class="form-control"
                                   placeholder="{{ $mail['mail_password_configured'] ? 'Dejar vacío para no cambiar (' . $mail['mail_password_hint'] . ')' : 'Contraseña del servidor SMTP' }}"
                                   autocomplete="new-password">
                        </div>
                        <div class="col-md-4">
                            <label for="mail_encryption" class="form-label">Cifrado</label>
                            <select name="mail_encryption" id="mail_encryption" class="form-select">
                                <option value="tls" @if(old('mail_encryption', $mail['mail_encryption']) === 'tls') selected @endif>TLS</option>
                                <option value="ssl" @if(old('mail_encryption', $mail['mail_encryption']) === 'ssl') selected @endif>SSL</option>
                                <option value="" @if(old('mail_encryption', $mail['mail_encryption']) === null || old('mail_encryption', $mail['mail_encryption']) === '') selected @endif>Sin cifrado</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="mail_from_address" class="form-label">Remitente (email)</label>
                            <input type="email" name="mail_from_address" id="mail_from_address" class="form-control"
                                   value="{{ old('mail_from_address', $mail['mail_from_address']) }}" placeholder="info@webnu.es">
                        </div>
                        <div class="col-md-4">
                            <label for="mail_from_name" class="form-label">Remitente (nombre)</label>
                            <input type="text" name="mail_from_name" id="mail_from_name" class="form-control"
                                   value="{{ old('mail_from_name', $mail['mail_from_name']) }}" placeholder="Webnu">
                        </div>
                    </div>

                    @if ($mail['mail_password_configured'])
                        <div class="form-check mt-3">
                            <input type="checkbox" class="form-check-input" name="clear_mail_password" value="1" id="clear_mail_password">
                            <label class="form-check-label text-danger" for="clear_mail_password">Eliminar la contraseña SMTP guardada</label>
                        </div>
                    @endif

                    <hr class="my-4">

                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="test_email" class="form-label">Probar envío de correo</label>
                            <input type="email" name="test_email" id="test_email" class="form-control"
                                   value="{{ old('test_email', auth()->user()->email) }}" placeholder="tu@email.com">
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-outline-primary w-100"
                                    formaction="{{ route('admin.platform.settings.test-mail') }}">
                                <i class="ri-mail-send-line me-1"></i> Enviar prueba
                            </button>
                        </div>
                    </div>
                    <p class="text-muted small mt-2 mb-0">La prueba usa los valores del formulario aunque no hayas guardado.</p>
                </div>
            </div>

            {{-- Contacto landing --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Formularios y contacto (landing)</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Destinos de los formularios públicos y email visible en la landing.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="contact_suggestions_email" class="form-label">Sugerencias de mejora</label>
                            <input type="email" name="contact_suggestions_email" id="contact_suggestions_email" class="form-control"
                                   value="{{ old('contact_suggestions_email', $contact['contact_suggestions_email']) }}" placeholder="hello@webnu.es" required>
                            <div class="form-text">Popup «Sugerir una mejora»</div>
                        </div>
                        <div class="col-md-4">
                            <label for="contact_leads_email" class="form-label">Leads «Te llamamos»</label>
                            <input type="email" name="contact_leads_email" id="contact_leads_email" class="form-control"
                                   value="{{ old('contact_leads_email', $contact['contact_leads_email']) }}" placeholder="hello@webnu.es" required>
                            <div class="form-text">Formulario de contacto comercial</div>
                        </div>
                        <div class="col-md-4">
                            <label for="contact_public_email" class="form-label">Email público</label>
                            <input type="email" name="contact_public_email" id="contact_public_email" class="form-control"
                                   value="{{ old('contact_public_email', $contact['contact_public_email']) }}" placeholder="hello@webnu.es" required>
                            <div class="form-text">Mostrado en FAQ y textos de la landing</div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-lg">
                <i class="ri-save-line me-1"></i> Guardar configuración
            </button>
        </form>
    </div>
</div>
@endsection
