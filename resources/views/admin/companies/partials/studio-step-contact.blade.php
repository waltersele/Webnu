<div class="wn-studio-step d-none" data-step="contact" id="wn-step-panel-contact">
    <div class="mb-4">
        <h5 class="fw-semibold mb-1">Contacto y ubicación</h5>
        <p class="text-muted small mb-0">Datos que verán tus clientes en el pie de la carta y para contactarte.</p>
    </div>

    <div class="card border mb-4">
        <div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="ri-map-pin-line me-1 text-primary"></i> Ubicación</h6>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label">Dirección</label>
                    <input type="text" name="address" value="{{ old('address', $company->address) }}" class="form-control" placeholder="Calle y número">
                </div>
                <div class="col-md-3">
                    <label class="form-label">C.P.</label>
                    <input type="text" name="postal_code" value="{{ old('postal_code', $company->postal_code) }}" class="form-control">
                </div>
                <div class="col-md-5">
                    <label class="form-label">Localidad</label>
                    <input type="text" name="city" value="{{ old('city', $company->city) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Provincia</label>
                    <input type="text" name="province" value="{{ old('province', $company->province) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">País</label>
                    <input type="text" name="country" value="{{ old('country', $company->country) }}" class="form-control" placeholder="España">
                </div>
            </div>
        </div>
    </div>

    <div class="card border mb-4">
        <div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="ri-phone-line me-1 text-primary"></i> Teléfonos</h6>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Teléfono fijo</label>
                    <input type="text" name="phone" value="{{ old('phone', $company->phone) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Móvil</label>
                    <input type="text" name="mobile_phone" value="{{ old('mobile_phone', $company->mobile_phone) }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">WhatsApp</label>
                    <input type="text" name="whatsapp" value="{{ old('whatsapp', $company->whatsapp) }}" class="form-control" placeholder="627123456">
                    <div class="form-text">Sin prefijo +34</div>
                </div>
            </div>
            <div class="mt-3">
                <label class="form-label">Horario</label>
                <textarea name="schedule" rows="2" class="form-control" placeholder="Ej. Lun–Dom 12:00–16:00 / 20:00–00:00">{{ old('schedule', $company->schedule) }}</textarea>
            </div>
        </div>
    </div>

    <div class="card border">
        <div class="card-body">
            <h6 class="fw-semibold mb-3"><i class="ri-global-line me-1 text-primary"></i> Redes y web</h6>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">E-mail</label>
                    <input type="email" name="email" value="{{ old('email', $company->email) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Web</label>
                    <input type="url" name="web" value="{{ old('web', $company->web) }}" class="form-control" placeholder="https://">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Facebook</label>
                    <input type="text" name="facebook" value="{{ old('facebook', $company->facebook) }}" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Instagram</label>
                    <input type="text" name="instagram" value="{{ old('instagram', $company->instagram) }}" class="form-control">
                </div>
            </div>
        </div>
    </div>
</div>
