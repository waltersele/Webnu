@php
    $currentCompany = null;
    if (!empty($selected_company) && !empty($available_companies)) {
        $currentCompany = $available_companies->firstWhere('id', (int) $selected_company);
    }
@endphp
<div class="offcanvas offcanvas-end" tabindex="-1" id="webnuMorePanel" aria-labelledby="webnuMorePanelLabel">
    <div class="offcanvas-header border-bottom">
        <h5 class="offcanvas-title" id="webnuMorePanelLabel">{{ auth()->user()->name }}</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
    </div>
    <div class="offcanvas-body">
        @if (!empty($selected_company) && !empty($available_companies))
        <div class="mb-4">
            <label class="form-label small text-muted text-uppercase fw-semibold" for="company_selection">Establecimiento</label>
            <form method="POST" action="{{ route('admin.companies.changecompany', '0') }}" id="company-selection-form">
                @csrf
                <select name="company_selection" id="company_selection" class="form-select">
                    @foreach ($available_companies as $company)
                        <option value="{{ $company->id }}" {{ $company->id == $selected_company ? 'selected' : '' }}>{{ $company->name }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        @endif

        <div class="list-group list-group-flush rounded-3 border mb-4">
            <a href="{{ route('admin.companies.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                <i class="fas fa-store text-primary"></i> Negocios
            </a>
            <a href="{{ route('admin.integrations.index') }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2">
                <i class="fas fa-plug text-primary"></i> Integraciones
            </a>
            @if ($currentCompany)
            <a href="{{ route('see_menu', $currentCompany->slug) }}" class="list-group-item list-group-item-action d-flex align-items-center gap-2" target="_blank" rel="noopener">
                <i class="fas fa-external-link-alt text-primary"></i> Ver carta pública
            </a>
            @endif
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger w-100">
                <i class="fas fa-sign-out-alt me-1"></i> Cerrar sesión
            </button>
        </form>
    </div>
</div>

