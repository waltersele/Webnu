@php
    $userDisplayName = $userDisplayName ?? '';
    $panelUrl = $panelUrl ?? route('admin.dashboard');
    $settingsUrl = $settingsUrl ?? route('admin.settings');
    $logoutUrl = $logoutUrl ?? route('logout');
@endphp
<div class="landing-user-menu relative" data-landing-user-menu>
    <button type="button" class="landing-user-menu__trigger inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-primary-container text-on-primary text-label-md font-medium hover:opacity-90 transition-opacity" aria-expanded="false" aria-haspopup="true" data-landing-user-menu-toggle>
        <span>{{ __('landing.user_menu.greeting', ['name' => $userDisplayName]) }}</span>
        <span class="material-symbols-outlined text-[20px] landing-user-menu__chevron transition-transform">expand_more</span>
    </button>
    <div class="landing-user-menu__panel hidden absolute right-0 top-full mt-2 min-w-[220px] rounded-xl border border-border-subtle bg-surface-container-lowest shadow-lg py-2 z-50" role="menu" data-landing-user-menu-panel>
        <a href="{{ $panelUrl }}" class="flex items-center gap-2 px-4 py-2.5 text-label-md text-on-surface hover:bg-surface-container-low transition-colors" role="menuitem">
            <span class="material-symbols-outlined text-[20px] text-primary">dashboard</span>
            {{ __('landing.user_menu.panel') }}
        </a>
        <a href="{{ $settingsUrl }}" class="flex items-center gap-2 px-4 py-2.5 text-label-md text-on-surface hover:bg-surface-container-low transition-colors" role="menuitem">
            <span class="material-symbols-outlined text-[20px] text-primary">settings</span>
            {{ __('landing.user_menu.settings') }}
        </a>
        <hr class="my-2 border-border-subtle"/>
        <form method="POST" action="{{ $logoutUrl }}" class="m-0" role="menuitem">
            @csrf
            <button type="submit" class="w-full flex items-center gap-2 px-4 py-2.5 text-label-md text-on-surface hover:bg-surface-container-low transition-colors text-left">
                <span class="material-symbols-outlined text-[20px] text-primary">logout</span>
                {{ __('landing.user_menu.logout') }}
            </button>
        </form>
    </div>
</div>
