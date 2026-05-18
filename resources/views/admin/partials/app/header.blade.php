<header class="webnu-topbar">
    <a href="{{ route('admin.dashboard') }}" class="webnu-topbar__brand">Webnu<span>.es</span></a>
    <button type="button"
            class="webnu-avatar"
            data-bs-toggle="offcanvas"
            data-bs-target="#webnuMorePanel"
            aria-label="Menú de cuenta">
        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
    </button>
</header>
