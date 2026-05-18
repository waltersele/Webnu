<div class="wn-studio-step" data-step="identity">
    <div class="mb-4">
        <h5 class="fw-semibold mb-1">Identidad del negocio</h5>
        <p class="text-muted small mb-0">Nombre, chef y texto que aparecerá en la cabecera de tu carta digital.</p>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label" for="company-name">Nombre comercial <span class="text-danger">*</span></label>
            <input type="text" id="company-name" name="name" value="{{ old('name', $company->name) }}" class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" placeholder="Ej. Los Casanueva" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
        <div class="col-md-6">
            <label class="form-label" for="chef_name">Nombre del chef</label>
            <input type="text" id="chef_name" name="chef_name" value="{{ old('chef_name', $company->chef_name) }}" class="form-control" placeholder="Ej. Dani">
        </div>
        <div class="col-12">
            <label class="form-label" for="comments">Slogan / descripción breve</label>
            <textarea id="comments" class="form-control" maxlength="80" name="comments" rows="2" placeholder="Máx. 80 caracteres — aparece en la cabecera de la carta">{{ old('comments', $company->comments) }}</textarea>
            <div class="form-text">Ideal para una frase corta que invite a descubrir tu cocina.</div>
        </div>
    </div>

    <hr class="my-4">

    <h6 class="fw-semibold mb-3"><i class="ri-image-line me-1 text-primary"></i> Imágenes de marca</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Logo</label>
            <div class="dropzone dropzone-logo wn-dropzone"></div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Imagen de cabecera</label>
            <div class="dropzone dropzone-header wn-dropzone"></div>
            <div class="form-text">Foto del local o plato estrella para el banner superior.</div>
        </div>
    </div>
</div>
