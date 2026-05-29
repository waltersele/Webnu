<div class="card wn-settings-card" id="perfil">
    <div class="card-body p-4">
        <h2 class="wn-settings-section-title">Perfil</h2>
        <p class="wn-settings-section-lead">Actualiza tus datos de contacto y revisa la información de acceso.</p>

        <form method="POST" action="{{ route('admin.settings.profile') }}">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label" for="settings-name">Nombre</label>
                    <input type="text" class="form-control" id="settings-name" name="name" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="settings-phone">Teléfono</label>
                    <input type="text" class="form-control" id="settings-phone" name="phone" value="{{ old('phone', $user->phone) }}" placeholder="+34 600 000 000">
                </div>
                <div class="col-12">
                    <label class="form-label" for="settings-email">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-lock"></i></span>
                        <input type="email" class="form-control" id="settings-email" value="{{ $user->email }}" readonly disabled>
                    </div>
                    <div class="form-text">El email no se puede cambiar desde aquí.</div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 mt-4">
                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="btn btn-outline-secondary">Cambiar contraseña</a>
                @endif
                <a href="{{ route('admin.companies.index') }}" class="btn btn-outline-secondary">Gestionar negocios</a>
            </div>
        </form>
    </div>
</div>

