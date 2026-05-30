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
            @php $logoUrl = $company->logo ? '/img/' . $company->logo : null; @endphp
            <div class="wn-brand-upload" id="wn-upload-logo"
                data-upload-url="{{ route('admin.companies.storelogo', $company) }}"
                data-delete-url="{{ route('admin.companies.deletelogo', $company) }}"
                data-param="logo"
                data-existing-url="{{ $logoUrl }}">
                <input type="file" class="wn-brand-upload__input" accept="image/*" tabindex="-1" aria-hidden="true">
                <div class="wn-brand-upload__empty {{ $logoUrl ? 'd-none' : '' }}">
                    <i class="ri-upload-cloud-2-line d-block fs-3 mb-1"></i>
                    Arrastra el logo o haz clic
                </div>
                <div class="wn-brand-upload__preview {{ $logoUrl ? '' : 'd-none' }}">
                    <img src="{{ $logoUrl ?: '' }}" alt="Logo">
                    <button type="button" class="btn btn-sm btn-outline-danger wn-brand-upload__remove">Quitar</button>
                </div>
                <div class="wn-brand-upload__status d-none" aria-live="polite"></div>
            </div>
        </div>
        <div class="col-md-6">
            <label class="form-label">Imagen de cabecera</label>
            @php $headerUrl = $company->background_header ? '/img/' . $company->background_header : null; @endphp
            <div class="wn-brand-upload" id="wn-upload-header"
                data-upload-url="{{ route('admin.companies.storeheader', $company) }}"
                data-delete-url="{{ route('admin.companies.deleteheader', $company) }}"
                data-param="background_header"
                data-existing-url="{{ $headerUrl }}">
                <input type="file" class="wn-brand-upload__input" accept="image/*" tabindex="-1" aria-hidden="true">
                <div class="wn-brand-upload__empty {{ $headerUrl ? 'd-none' : '' }}">
                    <i class="ri-image-line d-block fs-3 mb-1"></i>
                    Arrastra la imagen o haz clic
                </div>
                <div class="wn-brand-upload__preview {{ $headerUrl ? '' : 'd-none' }}">
                    <img src="{{ $headerUrl ?: '' }}" alt="Cabecera">
                    <button type="button" class="btn btn-sm btn-outline-danger wn-brand-upload__remove">Quitar</button>
                </div>
                <div class="wn-brand-upload__status d-none" aria-live="polite"></div>
            </div>
            <div class="form-text">Foto del local o plato estrella para el banner superior.</div>
            @if($company->background_header)
                <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="wn-header-crop-open">
                    <i class="ri-crop-line me-1"></i> Ajustar recorte
                </button>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="wn-header-crop-modal" tabindex="-1" aria-labelledby="wn-header-crop-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="wn-header-crop-title">Recorte del banner</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small">Arrastra para centrar el plato o el local. El recorte se adapta a tu plantilla.</p>
                <div class="wn-header-crop-stage">
                    <img id="wn-header-crop-image" src="" alt="Recorte de cabecera" class="w-100">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="wn-header-crop-save">Guardar recorte</button>
            </div>
        </div>
    </div>
</div>

