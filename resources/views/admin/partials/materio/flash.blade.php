@if(session()->has('flash'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('flash') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif
@if(session()->has('flash_warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('flash_warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif

