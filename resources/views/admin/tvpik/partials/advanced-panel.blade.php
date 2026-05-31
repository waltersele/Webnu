<div class="collapse" id="wn-tvpik-advanced">
    <div class="card border-0 shadow-sm mt-3">
        <div class="card-body">
            <div class="row g-4">
                <div class="col-lg-5">
                    <h6 class="mb-3">Conexión TVPik</h6>
                    @if($tvpikConnected)
                        <p class="text-success mb-3"><i class="ti ti-check me-1"></i> Cuenta conectada</p>
                        <form method="POST" action="{{ route('admin.tvpik.disconnect') }}" onsubmit="return confirm('¿Desconectar TVPik?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-label-secondary">Desconectar</button>
                        </form>
                    @else
                        <p class="text-muted small">Conexión manual (solo si el arranque automático falla). Pega el token de TVPik.</p>
                        <form method="POST" action="{{ route('admin.tvpik.connect') }}">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label" for="tvpik_token">Token TVPik</label>
                                <input type="password" name="tvpik_token" id="tvpik_token" class="form-control font-monospace" required autocomplete="off">
                                @error('tvpik_token')<div class="text-danger small">{{ $message }}</div>@enderror
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Conectar</button>
                        </form>
                    @endif
                    @unless($tvpikApiConfigured)
                        <p class="small text-warning mt-3 mb-0">
                            <code>TVPIK_API_URL</code> no configurada: verás pantallas de demostración y las URLs se guardan en Webnu.
                        </p>
                    @endunless
                </div>
                <div class="col-lg-7">
                    <h6 class="mb-3 d-flex align-items-center gap-2">
                        Token API Webnu
                        <span class="badge bg-label-secondary">TVPik / desarrolladores</span>
                    </h6>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control font-monospace" id="api-token" readonly value="{{ $apiToken }}">
                        <button type="button" class="btn btn-outline-primary" id="copy-token">Copiar</button>
                    </div>
                    <form method="POST" action="{{ route('admin.integrations.regenerate') }}" class="d-inline" onsubmit="return confirm('¿Regenerar token?');">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-label-secondary">Regenerar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
