@if(session()->has('flash'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('flash') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
    </div>
@endif
