<div class="wn-studio-step d-none" data-step="publish">
    <div class="mb-4">
        <h5 class="fw-semibold mb-1">Publicación</h5>
        <p class="text-muted small mb-0">Controla si la carta es visible y qué pueden hacer tus clientes.</p>
    </div>

    <div class="card border mb-3">
        <div class="card-body d-flex align-items-start gap-3">
            <div class="form-check form-switch flex-shrink-0 mt-1">
                <input type="checkbox" class="form-check-input" name="enabled" value="1" id="company-enabled-switch" {{ $company->enabled ? 'checked' : '' }}>
            </div>
            <div>
                <label class="form-check-label fw-semibold d-block mb-1" for="company-enabled-switch">Carta publicada</label>
                <p class="text-muted small mb-0">Si está desactivada, los clientes no podrán ver la carta en tu enlace público.</p>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-body d-flex align-items-start gap-3">
            <div class="form-check form-switch flex-shrink-0 mt-1">
                <input type="checkbox" class="form-check-input" name="reservation" value="1" id="company-reservation-switch" {{ $company->reservation ? 'checked' : '' }}>
            </div>
            <div>
                <label class="form-check-label fw-semibold d-block mb-1" for="company-reservation-switch">Reservas desde la carta</label>
                <p class="text-muted small mb-0">Muestra el botón para que los clientes reserven mesa desde la carta digital.</p>
            </div>
        </div>
    </div>

    <div class="card bg-label-secondary border-0 mb-4">
        <div class="card-body">
            <h6 class="fw-semibold mb-2">Enlace de tu carta</h6>
            <p class="text-muted small mb-3">URL limpia y permanente para QR, cartelería y redes. Si el nombre ya existe, añadimos ciudad o un número (<code>la-brasa-2</code>).</p>
            <label class="form-label" for="company-slug">URL pública</label>
            <div class="input-group mb-2">
                <span class="input-group-text text-muted">{{ url('/carta') }}/</span>
                <input
                    type="text"
                    id="company-slug"
                    name="slug"
                    class="form-control font-monospace {{ $errors->has('slug') ? 'is-invalid' : '' }}"
                    value="{{ old('slug', $company->slug) }}"
                    pattern="[a-z0-9]+(-[a-z0-9]+)*"
                    maxlength="64"
                    autocomplete="off"
                    spellcheck="false"
                >
                <a href="{{ $company->publicUrl() }}" target="_blank" rel="noopener" class="btn btn-primary">Abrir</a>
            </div>
            @error('slug')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            <p class="text-muted small mb-0">Solo minúsculas, números y guiones. Cambiarla invalida QR ya impresos con la URL anterior.</p>
        </div>
    </div>
</div>
