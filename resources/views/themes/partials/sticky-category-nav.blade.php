<nav class="wn-menu-nav wn-menu-nav--{{ $variant ?? 'default' }}" id="sticker" aria-label="Secciones del menú">
    <div class="wn-menu-nav__track">
        @foreach ($sections as $index => $section)
            <a href="#" class="wn-menu-chip linkTo {{ $index === 0 ? 'is-active' : '' }}" id="{{ $section->id }}">{{ $section->name }}</a>
        @endforeach
    </div>
</nav>
