<div class="card mb-4 border shadow-none wn-daily-spotlight-card">
    <div class="card-body py-3">
        <form method="POST" action="{{ route('admin.companies.daily-highlights', $company) }}" class="wn-daily-spotlight-form">
            @csrf
            @method('PUT')
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                <div>
                    <strong class="d-block">Especial de hoy</strong>
                    <span class="small text-muted">Texto libre que cambia cada día; no hace falta crear un plato en la carta.</span>
                </div>
                @if($company->hasDailySpotlight())
                    <span class="badge text-bg-success">Visible en la carta</span>
                @endif
            </div>
            <div class="row g-2 align-items-center">
                <div class="col-lg-8">
                    <label class="visually-hidden" for="daily-spotlight">Especial de hoy</label>
                    <input type="text"
                           name="daily_spotlight"
                           id="daily-spotlight"
                           class="form-control form-control-lg"
                           maxlength="500"
                           placeholder="Ej: Lubina del mercado a la plancha"
                           value="{{ old('daily_spotlight', $company->daily_spotlight) }}"
                           autocomplete="off">
                </div>
                <div class="col-lg-2">
                    <label class="visually-hidden" for="daily-spotlight-price">Precio (opcional)</label>
                    <div class="input-group">
                        <input type="text"
                               name="daily_spotlight_price"
                               id="daily-spotlight-price"
                               class="form-control"
                               inputmode="decimal"
                               placeholder="Precio"
                               value="{{ old('daily_spotlight_price', $company->daily_spotlight_price) }}">
                        <span class="input-group-text">€</span>
                    </div>
                </div>
                <div class="col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">Guardar</button>
                    @if($company->hasDailySpotlight())
                        <button type="submit" name="clear" value="1" class="btn btn-outline-secondary" title="Quitar de la carta">×</button>
                    @endif
                </div>
            </div>
            @error('daily_spotlight')
                <p class="text-danger small mb-0 mt-2">{{ $message }}</p>
            @enderror
            @error('daily_spotlight_price')
                <p class="text-danger small mb-0 mt-2">{{ $message }}</p>
            @enderror
        </form>
    </div>
</div>
