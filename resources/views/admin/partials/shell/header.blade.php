<header class="webnu-header">
    <div class="webnu-header__left">
        <button type="button" id="webnu-menu-toggle" class="webnu-header__menu-btn" aria-label="Abrir menú">
            <i class="fas fa-bars"></i>
        </button>
        <h1 class="webnu-header__title">@yield('page_title', 'Panel')</h1>
    </div>
    <div class="webnu-header__actions">
        @hasSection('page_actions')
            @yield('page_actions')
        @endif
    </div>
    <div class="webnu-header__user d-none d-md-flex">
        <span class="webnu-header__user-name">{{ auth()->user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" class="d-inline">
            @csrf
            <button type="submit" class="webnu-btn webnu-btn--ghost webnu-btn--sm" title="Cerrar sesión">
                <i class="fas fa-sign-out-alt"></i>
            </button>
        </form>
    </div>
</header>

