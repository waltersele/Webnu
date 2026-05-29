<div class="wn-settings-grid" id="facturacion">
    <div class="card wn-settings-card">
        <div class="card-body p-4">
            <h2 class="wn-settings-section-title">Datos de facturación</h2>
            <p class="wn-settings-section-lead">Añade la información fiscal para tus facturas. Si pagas con Stripe, la sincronizamos cuando guardas.</p>

            <form method="POST" action="{{ route('admin.settings.billing-info') }}">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label" for="settings-legal-name">Razón social / nombre fiscal</label>
                        <input type="text" class="form-control" id="settings-legal-name" name="legal_name" value="{{ old('legal_name', $user->legal_name) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="settings-tax-id">NIF / CIF / nº IVA</label>
                        <input type="text" class="form-control" id="settings-tax-id" name="tax_id" value="{{ old('tax_id', $user->tax_id) }}" placeholder="B12345678">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="settings-country">País</label>
                        <input type="text" class="form-control" id="settings-country" name="billing_country" value="{{ old('billing_country', $user->billing_country ?: 'ES') }}" maxlength="2" placeholder="ES">
                    </div>
                    <div class="col-12">
                        <label class="form-label" for="settings-address">Dirección fiscal</label>
                        <input type="text" class="form-control" id="settings-address" name="billing_address" value="{{ old('billing_address', $user->billing_address) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label" for="settings-postal">Código postal</label>
                        <input type="text" class="form-control" id="settings-postal" name="billing_postal_code" value="{{ old('billing_postal_code', $user->billing_postal_code) }}">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label" for="settings-city">Ciudad</label>
                        <input type="text" class="form-control" id="settings-city" name="billing_city" value="{{ old('billing_city', $user->billing_city) }}">
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">Guardar datos</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card wn-settings-card">
        <div class="card-body p-4">
            <h3 class="wn-settings-section-title">Pago y facturas</h3>
            <p class="wn-settings-section-lead">Consulta tu método de pago y las últimas facturas disponibles.</p>

            <div class="mb-3">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <div class="small text-muted">Método de pago</div>
                        <div class="fw-semibold">{{ $cardSummary ?? '—' }}</div>
                    </div>
                    <div>
                        @if ($user->stripe_id)
                            <form method="POST" action="{{ route('admin.billing.portal') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm">Abrir portal de Stripe</button>
                            </form>
                        @else
                            <a href="{{ route('welcome') }}" class="btn btn-primary btn-sm">Contratar un plan</a>
                        @endif
                    </div>
                </div>
            </div>

            @php
                $invoiceItems = $invoices ?? collect();
            @endphp
            @if ($invoiceItems->count())
                <div class="small text-muted mb-2">Últimas facturas</div>
                <div class="list-group">
                    @foreach($invoiceItems as $invoice)
                        @php
                            $label = method_exists($invoice, 'date') ? $invoice->date()->format('d/m/Y') : ($invoice->date ?? null);
                            $amount = method_exists($invoice, 'total') ? $invoice->total() : null;
                        @endphp
                        <div class="list-group-item d-flex flex-wrap align-items-center justify-content-between gap-2">
                            <div>
                                <div class="fw-semibold">Factura {{ $label ?: '—' }}</div>
                                <div class="small text-muted">{{ $amount ?: '' }}</div>
                            </div>
                            <div class="d-flex gap-2">
                                <form method="POST" action="{{ route('admin.billing.portal') }}" class="m-0">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm">Ver en Stripe</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="border rounded p-3">
                    <p class="mb-1 fw-semibold">Aún no hay facturas.</p>
                    <p class="mb-0 text-muted small">Cuando actives un plan y se genere el primer cobro, aquí verás tus facturas.</p>
                </div>
            @endif
        </div>
    </div>
</div>

