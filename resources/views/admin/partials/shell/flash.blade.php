@if(session()->has('flash'))
    <div class="webnu-flash">
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            {{ session('flash') }}
            <button type="button" class="close" data-bs-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>
@endif

