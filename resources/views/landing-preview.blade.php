<!DOCTYPE html>
<html class="scroll-smooth" lang="es">
<head>
    @include('landing.partials.head')
</head>
<body class="bg-background text-on-surface text-body-md">
@php
    $loginUrl = route('login');
    $registerUrl = route('register');
    $contactPublicEmail = $contactPublicEmail ?? 'hola@webnu.es';
    $demoUrl = url('/carta/demo?lang=en');
    $demoCocktailsUrl = url('/carta/demo-cocktails');
    $demoFuegoUrl = url('/carta/demo-fuego');
    $templateCount = count(config('company_templates.templates', []));
    $demoShowcases = [
        [
            'title' => 'Restaurante clásico',
            'subtitle' => 'La Brasa del Puerto',
            'desc' => 'Carta mediterránea con plantilla Básica. Incluye inglés: detecta el idioma del navegador al escanear.',
            'url' => url('/carta/demo'),
            'badge' => 'Básica',
            'tags' => ['ES + EN', 'Reels', 'QR'],
            'preview' => asset('img/productos/brasa-solomillo.jpg'),
            'accent' => 'border-border-subtle bg-surface-container-lowest',
        ],
        [
            'title' => 'Coctelería',
            'subtitle' => 'Azul Coctelería',
            'desc' => 'Copas a ancho completo con reels integrados. Plantilla Nocturno para bares y cocktail bars.',
            'url' => $demoCocktailsUrl,
            'badge' => 'Nocturno',
            'tags' => ['100 % ancho', 'Oscuro', 'Reels'],
            'preview' => asset('img/productos/cocktail-negroni.jpg'),
            'accent' => 'border-primary/30 bg-surface-container',
        ],
        [
            'title' => 'Ramen & asiático',
            'subtitle' => 'Fuego Otaku',
            'desc' => 'Naranja neón, tipografía bold y platos con vídeo. Plantilla Otaku para locales con personalidad.',
            'url' => $demoFuegoUrl,
            'badge' => 'Otaku',
            'tags' => ['Neón', 'Reels', 'Oscuro'],
            'preview' => asset('img/productos/fuego-tonkotsu.jpg'),
            'accent' => 'border-orange-400 bg-orange-950/10',
        ],
    ];
    $tvpikSlides = [
        [
            'tag' => 'Plato del día',
            'title' => 'Solomillo al Pedro Ximénez',
            'price' => '24,50 €',
            'image' => asset('img/productos/brasa-solomillo.jpg'),
            'action' => 'Precio actualizado desde el móvil',
            'theme' => 'warm',
        ],
        [
            'tag' => 'Menú del día',
            'title' => '1º + 2º + postre',
            'price' => '14,90 €',
            'image' => asset('img/productos/brasa-burrata.jpg'),
            'action' => 'Menú del día publicado',
            'theme' => 'menu',
            'items' => ['Ensalada de burrata', 'Lubina a la plancha', 'Tarta de queso'],
        ],
        [
            'tag' => 'Copa destacada',
            'title' => 'Negroni del Puerto',
            'price' => '11,00 €',
            'image' => asset('img/productos/cocktail-negroni.jpg'),
            'action' => 'Carta de copas sincronizada',
            'theme' => 'dark',
        ],
        [
            'tag' => 'Postre con reel',
            'title' => 'Coulant de chocolate',
            'price' => '6,50 €',
            'image' => asset('img/productos/brasa-burrata.jpg'),
            'action' => 'Vídeo añadido al plato',
            'theme' => 'warm',
        ],
    ];
    $heroHooks = [
        'Deja de imprimir cartas cada vez que cambias un precio.',
        'Tu carta en el móvil del cliente. Actualizada en segundos.',
        'Fotografía tu carta en papel y publícala hoy con IA.',
        'Reels en platos, QR al instante y todos tus locales en un panel.',
        'Sin imprenta. Sin PDFs obsoletos. Una carta que vende sola.',
        'El 70 % de tus clientes mira el móvil antes de pedir. Muéstrales calidad real.',
    ];
@endphp

<nav data-landing-nav class="sticky top-0 z-50 flex justify-between items-center w-full px-margin-mobile md:px-gutter max-w-container-max mx-auto h-20 bg-surface-container-lowest border-b border-border-subtle transition-shadow">
    <a href="#inicio" class="text-headline-md font-headline font-extrabold text-primary">Webnu.es</a>
    <div class="hidden md:flex items-center gap-8">
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#demos-carta">Ejemplos</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#funciones">Funciones</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#reels">Reels</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#tvpik">TVPik</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#process">Escaneo IA</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#personalizable">Plantillas</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#pricing">Precios</a>
    </div>
    <div class="flex items-center gap-3">
        <a href="{{ $loginUrl }}" class="hidden sm:inline-flex px-5 py-2 rounded-lg border border-border-subtle text-text-muted text-label-md hover:bg-surface-container transition-colors">Login</a>
        <a href="#inicio" class="px-5 py-2 rounded-lg bg-primary-container text-on-primary text-label-md hover:opacity-90 transition-opacity font-medium">Empezar gratis</a>
    </div>
</nav>

<main class="max-w-container-max mx-auto px-margin-mobile md:px-gutter">
    {{-- Hero --}}
    <section id="inicio" class="py-16 md:py-24 grid grid-cols-1 md:grid-cols-2 gap-12 md:gap-16 items-center">
        <div class="space-y-8">
            <div class="flex flex-wrap gap-3">
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-container-high border border-outline-variant text-label-sm text-primary">
                    <span class="material-symbols-outlined text-[16px]">psychology</span>
                    Escaneo IA
                </span>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-container-high border border-outline-variant text-label-sm text-primary">
                    <span class="material-symbols-outlined text-[16px]">qr_code_2</span>
                    QR al instante
                </span>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-surface-container-high border border-outline-variant text-label-sm text-primary">
                    <span class="material-symbols-outlined text-[16px]">translate</span>
                    Multilingüe
                </span>
            </div>
            <h1
                id="hero-headline"
                class="font-headline text-headline-xl text-on-surface leading-tight min-h-[3.6em] md:min-h-[2.4em]"
                data-hooks='@json($heroHooks)'
            >{{ $heroHooks[0] }}</h1>
            <p class="text-body-lg text-text-muted max-w-lg">
                Convierte tu carta en papel en una experiencia digital: más de {{ $templateCount }} plantillas personalizables, reels en platos, traducciones automáticas y QR listo en minutos.
            </p>
            <div class="flex items-center gap-4">
                <div class="flex -space-x-3">
                    <span class="w-11 h-11 rounded-full border-2 border-surface bg-primary-container flex items-center justify-center text-on-primary text-label-sm font-bold">+</span>
                    <span class="w-11 h-11 rounded-full border-2 border-surface bg-surface-container flex items-center justify-center text-primary text-label-sm font-bold">IA</span>
                    <span class="w-11 h-11 rounded-full border-2 border-surface bg-surface-container-high flex items-center justify-center text-primary text-label-sm font-bold">QR</span>
                </div>
                <span class="text-label-md text-text-muted">+500 hosteleros confían en Webnu.es</span>
            </div>
        </div>

        <div class="bg-surface-container-lowest border border-border-subtle p-8 rounded-xl shadow-sm">
            <div class="mb-6">
                <h3 class="font-headline text-headline-md text-on-surface">Empieza gratis</h3>
                <p class="mt-2 text-label-sm text-text-muted">Introduce tu email y completa el registro en un minuto. Sin tarjeta.</p>
            </div>
            <form action="{{ $registerUrl }}" method="GET" class="space-y-4">
                <div>
                    <label class="text-label-md text-on-surface-variant block mb-1">Email profesional</label>
                    <input name="email" required class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="chef@restaurante.com" type="email" autocomplete="email"/>
                </div>
                <button type="submit" class="w-full py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 transition-opacity font-semibold flex items-center justify-center gap-2">
                    Crear mi carta gratis <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </button>
                <p class="text-center text-label-sm text-text-muted">Nombre del local, contraseña y plantilla los configuras después, sin repetir datos.</p>
            </form>
        </div>
    </section>

    {{-- Métricas --}}
    <section class="py-12 border-y border-border-subtle mb-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div><div class="text-headline-lg font-headline text-primary">542+</div><div class="text-label-md text-text-muted">Restaurantes activos</div></div>
            <div><div class="text-headline-lg font-headline text-primary">12k</div><div class="text-label-md text-text-muted">Escaneos al mes</div></div>
            <div><div class="text-headline-lg font-headline text-primary">{{ $templateCount }}+</div><div class="text-label-md text-text-muted">Plantillas pro</div></div>
            <div><div class="text-headline-lg font-headline text-primary">8 min</div><div class="text-label-md text-text-muted">Setup medio</div></div>
        </div>
    </section>

    {{-- 3 cartas demo premium --}}
    <section id="demos-carta" class="py-20 md:py-24">
        <div class="text-center mb-14">
            <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">En vivo</span>
            <h2 class="font-headline text-headline-xl mb-4">Tres cartas reales para que veas el resultado</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">
                Abre cualquier demo en tu móvil: restaurante clásico, coctelería o local asiático. Cada una con su plantilla, colores y menú curado.
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-5xl mx-auto">
            @foreach($demoShowcases as $demo)
                <article class="rounded-2xl border-2 {{ $demo['accent'] }} overflow-hidden flex flex-col hover:shadow-lg transition-shadow">
                    <div class="aspect-[16/10] overflow-hidden bg-surface-container">
                        <img src="{{ $demo['preview'] }}" alt="{{ $demo['subtitle'] }}" class="w-full h-full object-cover" loading="lazy"/>
                    </div>
                    <div class="p-6 flex flex-col flex-grow">
                        <span class="text-label-sm font-bold text-primary uppercase tracking-wide">{{ $demo['badge'] }}</span>
                        <h3 class="font-headline text-headline-md mt-1 mb-1">{{ $demo['title'] }}</h3>
                        <p class="text-label-md text-text-muted mb-3">{{ $demo['subtitle'] }}</p>
                        <p class="text-body-md text-text-muted mb-4 flex-grow">{{ $demo['desc'] }}</p>
                        <div class="flex flex-wrap gap-2 mb-5">
                            @foreach($demo['tags'] as $tag)
                                <span class="px-2.5 py-1 rounded-full bg-surface-container-high text-label-sm text-on-surface-variant">{{ $tag }}</span>
                            @endforeach
                        </div>
                        <a href="{{ $demo['url'] }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity">
                            Ver carta en vivo <span class="material-symbols-outlined text-[18px]">open_in_new</span>
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        {{-- Más plantillas + animación personalización --}}
        <div id="personalizable" class="mt-20 md:mt-24 grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center max-w-5xl mx-auto">
            <div class="landing-customize-wrap order-2 lg:order-1">
                <div id="customize-phone" class="landing-customize-phone" aria-hidden="true">
                    <div class="landing-customize-phone__status">
                        <span></span><span></span><span></span>
                    </div>
                    <div id="customize-header" class="landing-customize-phone__header">
                        <span id="customize-business" class="landing-customize-phone__business">La Brasa del Puerto</span>
                        <span id="customize-template" class="landing-customize-phone__tpl">Básica</span>
                    </div>
                    <div class="landing-customize-phone__section" id="customize-section">Carta · Principales</div>
                    <article class="landing-customize-phone__card">
                        <div class="landing-customize-phone__thumb"></div>
                        <div class="landing-customize-phone__info">
                            <div class="landing-customize-phone__row">
                                <span id="customize-dish" class="landing-customize-phone__dish">Solomillo al Pedro Ximénez</span>
                                <span id="customize-price" class="landing-customize-phone__price">24,50 €</span>
                            </div>
                            <p id="customize-desc" class="landing-customize-phone__desc">Reducción de Pedro Ximénez y patata confitada.</p>
                        </div>
                    </article>
                </div>
                <div class="landing-customize-controls">
                    <div class="landing-customize-controls__row">
                        <span class="landing-customize-controls__label"><span class="material-symbols-outlined text-[16px]">palette</span> Color</span>
                        <div class="landing-customize-swatches" id="customize-swatches"></div>
                    </div>
                    <div class="landing-customize-controls__row">
                        <span class="landing-customize-controls__label"><span class="material-symbols-outlined text-[16px]">edit</span> Texto</span>
                        <span id="customize-hint" class="landing-customize-hint">Nombre del plato y precio</span>
                    </div>
                </div>
            </div>
            <div class="space-y-6 order-1 lg:order-2">
                <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider">Estudio visual</span>
                <h3 class="font-headline text-headline-lg">Y {{ $templateCount - 3 }} plantillas más, totalmente personalizables</h3>
                <p class="text-body-lg text-text-muted">
                    Además de estas tres demos hay japonés, fast food, marisquería, fine dining, asador y más. Cambia colores, tipografías, logo y textos desde el panel — la carta se actualiza al instante.
                </p>
                <ul class="space-y-3 text-label-md text-text-muted">
                    <li class="flex gap-3 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span>{{ $templateCount }}+ diseños listos para restaurante, bar, delivery o hotel</li>
                    <li class="flex gap-3 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span>Paleta de colores y fuentes por plantilla</li>
                    <li class="flex gap-3 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span>Previsualiza antes de publicar, sin perder tus platos</li>
                </ul>
                <a href="#inicio" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity">
                    Crear mi carta <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Funciones para el día a día --}}
    <section id="funciones" class="py-20 md:py-24 mb-8">
        <div class="text-center mb-14">
            <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">Hecho para sala y cocina</span>
            <h2 class="font-headline text-headline-xl mb-4">Funciones que mejoran la vida de tu restaurante o bar</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">Menos papeles, menos prisas y más control cuando el servicio aprieta. Todo pensado para quien vive del turno, no del ordenador.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach([
                ['icon' => 'bolt', 't' => 'Cambios al instante', 'd' => 'Actualiza precios, menú del día o marca un plato como agotado desde el móvil. El QR refleja el cambio al momento.', 'plan' => null],
                ['icon' => 'print_disabled', 't' => 'Adiós a la imprenta', 'd' => 'Olvídate de reimprimir cartas cada vez que sube un ingrediente o cambias la oferta de temporada.', 'plan' => null],
                ['icon' => 'photo_camera', 't' => 'Escaneo con IA', 'd' => 'Fotografía tu carta en papel o sube un PDF: la IA ordena secciones, platos y precios para que solo revises.', 'plan' => 'plus', 'free_note' => '5 escaneos en Gratis'],
                ['icon' => 'translate', 't' => 'Carta multilingüe', 'd' => 'Activa inglés, francés, alemán, italiano y más. Detecta el idioma del navegador del comensal o deja que elija al escanear el QR. Traducción con IA o edición manual.', 'plan' => 'plus'],
                ['icon' => 'videocam', 't' => 'Reels que venden', 'd' => 'Un vídeo corto en el card del plato despierta el apetito mejor que una foto estática.', 'plan' => 'plus'],
                ['icon' => 'storefront', 't' => 'Varios locales, un panel', 'd' => 'Gestiona varias cartas desde la misma cuenta: terraza, barra, menú degustación o segunda marca.', 'plan' => 'plus'],
                ['icon' => 'health_and_safety', 't' => 'Alérgenos claros', 'd' => 'Marca alérgenos por plato y muéstralos en la carta digital. Menos dudas en mesa y más tranquilidad.', 'plan' => null],
                ['icon' => 'qr_code_2', 't' => 'QR listo en segundos', 'd' => 'Genera y descarga el código para mesas, barra o cartelería. Sin diseñador ni imprenta.', 'plan' => null],
                ['icon' => 'live_tv', 't' => 'TVPik en pantalla', 'd' => 'Muestra tu carta en las pantallas del local y contrólalo todo desde el móvil. Sin cables ni dispositivos extra.', 'plan' => 'ilimitado'],
            ] as $feat)
                <div class="relative bg-surface-container-lowest border border-border-subtle rounded-xl p-6 hover:border-primary/30 hover:shadow-md transition-all {{ !empty($feat['plan']) ? 'landing-feat--premium' : '' }}">
                    @if(!empty($feat['plan']))
                        <span class="landing-plan-badge landing-plan-badge--{{ $feat['plan'] === 'ilimitado' ? 'unlimited' : 'plus' }}">
                            {{ $feat['plan'] === 'ilimitado' ? 'Ilimitado' : 'Plus' }}
                        </span>
                    @endif
                    <div class="w-11 h-11 rounded-xl bg-primary-fixed flex items-center justify-center text-primary mb-4">
                        <span class="material-symbols-outlined text-[24px]">{{ $feat['icon'] }}</span>
                    </div>
                    <h3 class="font-headline text-headline-md mb-2">{{ $feat['t'] }}</h3>
                    <p class="text-label-md text-text-muted leading-relaxed">{{ $feat['d'] }}</p>
                    @if(!empty($feat['free_note']))
                        <p class="text-label-sm text-primary mt-3 font-medium">{{ $feat['free_note'] }}</p>
                    @elseif(!empty($feat['plan']))
                        <p class="text-label-sm text-text-muted mt-3">Incluido en plan {{ $feat['plan'] === 'ilimitado' ? 'Ilimitado' : 'Plus' }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="mt-12 bg-surface-container border border-border-subtle rounded-2xl p-8 md:p-10 flex flex-col md:flex-row gap-8 items-start md:items-center">
            <div class="w-14 h-14 rounded-2xl bg-primary-container text-on-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[28px]">forum</span>
            </div>
            <div class="flex-1 space-y-2">
                <h3 class="font-headline text-headline-md">En constante mejora, escuchando a los hosteleros</h3>
                <p class="text-body-md text-text-muted max-w-2xl">
                    Webnu evoluciona cada semana atendiendo las opiniones de quienes lo usan en servicio. Si echas en falta algo, te gustaría una función distinta o tienes una idea que te facilitaría el turno, <strong class="text-on-surface font-medium">cuéntanoslo: nos encanta recibir sugerencias</strong> y priorizamos lo que más alivia el día a día en restaurante y bar.
                </p>
            </div>
            <button type="button" id="suggestion-open" class="inline-flex items-center gap-2 px-6 py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity shrink-0 whitespace-nowrap">
                <span class="material-symbols-outlined text-[20px]">lightbulb</span>
                Sugerir una mejora
            </button>
        </div>
    </section>

    {{-- Reels --}}
    <section id="reels" class="py-20 md:py-24">
        <div class="text-center mb-14">
            <h2 class="font-headline text-headline-xl mb-4">Cartas que cobran vida con Reels</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">
                Vídeos cortos integrados en el card de cada plato: el cliente ve el movimiento sin salir de la carta. <span class="text-primary font-medium">Incluido desde el plan Plus.</span>
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="flex justify-center">
                <div class="landing-menu-mock w-full max-w-sm">
                    <div class="landing-menu-mock__chrome">
                        <span class="landing-menu-mock__dot"></span>
                        <span class="landing-menu-mock__dot"></span>
                        <span class="landing-menu-mock__dot"></span>
                        <span class="landing-menu-mock__title">Carta · Entrantes</span>
                    </div>
                    <div class="landing-menu-mock__body">
                        <article class="landing-menu-card">
                            <div class="landing-menu-card__media landing-menu-card__media--reel">
                                <video class="landing-menu-card__reel" autoplay muted loop playsinline preload="metadata" poster="{{ asset('img/productos/brasa-solomillo.jpg') }}">
                                    <source src="{{ asset('img/demo/reel-01.mp4') }}" type="video/mp4"/>
                                </video>
                                <span class="landing-menu-card__badge"><i class="material-symbols-outlined text-[14px]">videocam</i> Reel</span>
                            </div>
                            <div class="landing-menu-card__content">
                                <div class="landing-menu-card__head">
                                    <h4>Solomillo al Pedro Ximénez</h4>
                                    <span class="landing-menu-card__price">24,50 €</span>
                                </div>
                                <p>Reducción de Pedro Ximénez, patata confitada y verduras de temporada.</p>
                            </div>
                        </article>
                        <article class="landing-menu-card landing-menu-card--photo">
                            <div class="landing-menu-card__media">
                                <img src="{{ asset('img/productos/brasa-burrata.jpg') }}" alt="Ensalada de burrata" loading="lazy"/>
                            </div>
                            <div class="landing-menu-card__content">
                                <div class="landing-menu-card__head">
                                    <h4>Ensalada de burrata</h4>
                                    <span class="landing-menu-card__price">12,50 €</span>
                                </div>
                                <p>Burrata fresca, tomate cherry confitado y pesto de albahaca.</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
            <div class="space-y-8">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">view_agenda</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">Dentro del card o a ancho completo</h4>
                        <p class="text-text-muted">En restaurantes, el reel va en la tarjeta del plato. En coctelería, prueba la carta <a href="{{ $demoCocktailsUrl }}" target="_blank" class="text-primary font-medium hover:underline">Azul Coctelería</a> con copas al 100 % del ancho.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">speed</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">Carga optimizada</h4>
                        <p class="text-text-muted">Vídeos comprimidos para 4G: autoplay silenciado en la carta QR, sin bloquear la navegación.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- TVPik --}}
    <section id="tvpik" class="py-20 md:py-24 bg-surface-container-low rounded-3xl px-6 md:px-10 mb-8" data-tvpik-slides='@json($tvpikSlides)'>
        <div class="text-center mb-14">
            <span class="inline-block bg-orange-500/10 text-orange-700 px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">Plan Ilimitado</span>
            <h2 class="font-headline text-headline-xl mb-4">Tu carta en la TV del local con TVPik</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">
                Muestra platos, menú del día y promos en las pantallas del local. Lo controlas todo desde el móvil con TVPik — sin cables, sin Fire Stick y la misma carta que escanean con QR.
            </p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center max-w-5xl mx-auto">
            <div class="space-y-8 order-2 lg:order-1">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">sync</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">Sincronización en tiempo real</h4>
                        <p class="text-text-muted">Cambias un precio o marcas un plato agotado en el móvil y la pantalla del local refleja el cambio al instante.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">smartphone</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">Control desde el móvil</h4>
                        <p class="text-text-muted">Elige qué carta mostrar, qué plato destacar y cuándo cambiar la pantalla desde la app TVPik. Sin HDMI, sin Fire Stick ni configuraciones técnicas.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">tv</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">Barra, sala o terraza</h4>
                        <p class="text-text-muted">Ideal para menú del día en barra, promos en sala o carta de copas en coctelería. Conecta la TV a TVPik y gestiona el contenido desde el móvil.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">qr_code_2</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">Una sola carta, dos pantallas</h4>
                        <p class="text-text-muted">Lo que ves en la TV es la misma carta digital que el cliente abre al escanear el QR. Sin duplicar trabajo ni diseños.</p>
                    </div>
                </div>
                <a href="#pricing" class="inline-flex items-center gap-2 text-primary font-semibold text-label-md hover:underline">
                    Disponible en plan Ilimitado <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                </a>
            </div>

            <div class="landing-tvpik-scene order-1 lg:order-2" aria-hidden="true">
                <div class="landing-tvpik-scene__ambience"></div>

                <div class="landing-tvpik-phone" id="tvpik-phone">
                    <div class="landing-tvpik-phone__bar">
                        <span></span><span></span><span></span>
                        <span class="landing-tvpik-phone__label">TVPik · Móvil</span>
                    </div>
                    <div class="landing-tvpik-phone__body">
                        <span class="landing-tvpik-phone__chip"><span class="material-symbols-outlined text-[14px]">tv</span> Controlando pantalla</span>
                        <p id="tvpik-action" class="landing-tvpik-phone__action">Precio actualizado desde el móvil</p>
                        <span id="tvpik-phone-status" class="landing-tvpik-phone__status">Publicando…</span>
                    </div>
                </div>

                <div class="landing-tvpik-sync" id="tvpik-sync" aria-hidden="true">
                    <span class="landing-tvpik-sync__dot"></span>
                    <span class="landing-tvpik-sync__line"></span>
                    <span class="landing-tvpik-sync__label">Sincronizando</span>
                </div>

                <div class="landing-tvpik-tv" id="tvpik-tv">
                    <div class="landing-tvpik-tv__mount"></div>
                    <div class="landing-tvpik-tv__unit">
                        <div class="landing-tvpik-tv__bezel">
                            <div class="landing-tvpik-tv__screen landing-tvpik-tv__screen--warm" id="tvpik-screen">
                                <img id="tvpik-photo" class="landing-tvpik-tv__photo" src="{{ $tvpikSlides[0]['image'] }}" alt=""/>
                                <div class="landing-tvpik-tv__overlay">
                                    <span id="tvpik-tag" class="landing-tvpik-tv__tag">{{ $tvpikSlides[0]['tag'] }}</span>
                                    <p id="tvpik-title" class="landing-tvpik-tv__title">{{ $tvpikSlides[0]['title'] }}</p>
                                    <ul id="tvpik-items" class="landing-tvpik-tv__items hidden"></ul>
                                    <p id="tvpik-price" class="landing-tvpik-tv__price">{{ $tvpikSlides[0]['price'] }}</p>
                                </div>
                                <span class="landing-tvpik-tv__brand">TVPik</span>
                                <span class="landing-tvpik-tv__live"><span></span> EN VIVO</span>
                                <span id="tvpik-updated" class="landing-tvpik-tv__updated">Actualizado</span>
                            </div>
                        </div>
                    </div>
                    <div class="landing-tvpik-tv__glow"></div>
                </div>

                <div class="landing-tvpik-dots" id="tvpik-dots"></div>
            </div>
        </div>
    </section>

    {{-- Testimonios --}}
    <section class="py-16 mb-8">
        <h2 class="text-center font-headline text-headline-xl mb-12">Lo que dicen los hosteleros</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            @foreach([
                ['q' => 'Poder actualizar un precio en mitad del servicio es un alivio. Ya no dependemos de la imprenta.', 'n' => 'Marc R.', 'r' => 'GastroBarna'],
                ['q' => 'El escáner de IA es magia: subimos un PDF y en minutos teníamos la carta categorizada.', 'n' => 'Laura M.', 'r' => 'Grupo Marea'],
                ['q' => 'Los reels han disparado la venta de postres. Ver el chocolate fundido convence a cualquiera.', 'n' => 'Andrés S.', 'r' => 'Terraza Azul'],
            ] as $t)
                <div class="bg-surface-container-lowest p-8 rounded-xl border border-border-subtle">
                    <div class="flex gap-1 text-primary mb-4">
                        @for($i = 0; $i < 5; $i++)<span class="material-symbols-outlined text-[20px]" style="font-variation-settings: 'FILL' 1">star</span>@endfor
                    </div>
                    <p class="text-body-md text-on-surface-variant italic mb-6">"{{ $t['q'] }}"</p>
                    <p class="font-label-md text-on-surface">{{ $t['n'] }}</p>
                    <p class="text-label-sm text-text-muted">{{ $t['r'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 3 pasos IA --}}
    <section id="process" class="py-20 md:py-24">
        <div class="text-center mb-14">
            <span class="inline-block bg-primary/10 text-primary px-3 py-1 rounded-full text-label-sm font-bold uppercase tracking-wider mb-3">Exclusivo Webnu</span>
            <h2 class="font-headline text-headline-xl">Tu carta digital en 3 pasos</h2>
            <p class="text-body-lg text-text-muted mt-3">Digitalización técnica sin reescribir todo a mano. <span class="text-primary font-medium">Escaneo IA en Plus e Ilimitado.</span></p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 relative">
            <div class="hidden md:block absolute top-12 left-1/4 right-1/4 h-0.5 border-t-2 border-dashed border-outline-variant -z-10"></div>
            @foreach([
                ['icon' => 'photo_camera', 't' => '1. Escanea o sube', 'd' => 'Foto de tu carta o PDF. Nuestra IA entiende secciones, platos y precios.'],
                ['icon' => 'psychology', 't' => '2. Procesado IA', 'd' => 'Detecta alérgenos y categorías. Revisas en el panel en minutos.'],
                ['icon' => 'qr_code_2', 't' => '3. Listo para servir', 'd' => 'QR y enlace web listos. Cambios en tiempo real desde el móvil.'],
            ] as $step)
                <div class="text-center space-y-4">
                    <div class="w-16 h-16 rounded-2xl bg-primary-container text-on-primary flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-[32px]">{{ $step['icon'] }}</span>
                    </div>
                    <h3 class="font-headline text-headline-md">{{ $step['t'] }}</h3>
                    <p class="text-text-muted px-2">{{ $step['d'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Precios freemium --}}
    <section id="pricing" class="py-20 md:py-24">
        <div class="text-center mb-12">
            <h2 class="font-headline text-headline-xl mb-4">Freemium: empieza gratis, crece cuando quieras</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">Sin permanencia. <strong>30 días de Plus gratis</strong> al registrarte. Sube de plan solo si necesitas más.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch max-w-5xl mx-auto">
            <div class="bg-surface-container-lowest border border-border-subtle p-8 rounded-xl flex flex-col">
                <h3 class="font-headline text-headline-md mb-1">Gratis</h3>
                <p class="text-label-sm text-text-muted mb-4">Para probar con un local</p>
                <div class="mb-6"><span class="text-4xl font-bold">0</span><span class="text-text-muted">€</span><span class="text-text-muted text-label-md"> / siempre</span></div>
                <ul class="space-y-3 mb-8 flex-grow text-label-md">
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span><span><strong>1 carta</strong> digital</span></li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> QR y plantillas</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Edición manual de platos</li>
                    <li class="flex gap-2 items-start text-text-muted"><span class="material-symbols-outlined text-[20px] shrink-0 opacity-40">schedule</span> Tras 30 días de prueba Plus → límites free</li>
                </ul>
                <a href="#inicio" class="w-full py-3 rounded-lg border border-border-subtle text-center font-medium hover:bg-surface-container transition-colors">Crear carta gratis</a>
            </div>
            <div class="bg-surface-container-lowest border-2 border-primary p-8 rounded-xl flex flex-col relative md:-translate-y-2 shadow-lg">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary text-on-primary px-4 py-1 rounded-full text-label-sm font-semibold">Más popular</span>
                <h3 class="font-headline text-headline-md mb-1">Plus</h3>
                <p class="text-label-sm text-text-muted mb-4">30 días gratis · luego 9,90 €/mes</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold">9,90</span><span class="text-text-muted">€</span>
                    <span class="text-text-muted text-label-md"> / mes · IVA incl.</span>
                </div>
                <ul class="space-y-3 mb-8 flex-grow text-label-md">
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span><span><strong>5 cartas</strong> (negocios)</span></li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Vídeos / reels en platos</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Escaneo IA (foto y PDF)</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Carta en <strong>2 idiomas</strong> (IA + manual)</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> QR por carta</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Todas las plantillas</li>
                </ul>
                <a href="#inicio" class="w-full py-4 rounded-lg bg-primary text-on-primary text-center font-semibold hover:opacity-90">Empezar con Plus</a>
            </div>
            <div class="bg-surface-container-lowest border border-border-subtle p-8 rounded-xl flex flex-col">
                <h3 class="font-headline text-headline-md mb-1">Ilimitado</h3>
                <p class="text-label-sm text-text-muted mb-4">Cadenas y alto volumen</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold">29,90</span><span class="text-text-muted">€</span>
                    <span class="text-text-muted text-label-md"> / mes · IVA incl.</span>
                </div>
                <ul class="space-y-3 mb-8 flex-grow text-label-md">
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span><span><strong>Cartas ilimitadas</strong></span></li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Vídeos / reels en platos</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Escaneo IA (foto y PDF)</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Carta <strong>multilingüe</strong> (EN, FR, DE, IT, RU…)</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> <strong>TVPik</strong> (cartas en pantalla)</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Soporte prioritario</li>
                </ul>
                <a href="#inicio" class="w-full py-3 rounded-lg border border-primary text-primary text-center font-semibold hover:bg-primary/5 transition-colors">Empezar con Ilimitado</a>
            </div>
        </div>
        <p class="text-center text-label-sm text-text-muted mt-8 max-w-xl mx-auto">Todos los planes incluyen carta responsive y cambios en tiempo real. El plan Gratis no requiere tarjeta.</p>
    </section>

    {{-- FAQ --}}
    <section class="py-16 max-w-3xl mx-auto">
        <h2 class="text-center font-headline text-headline-xl mb-10">Preguntas frecuentes</h2>
        <div class="space-y-3">
            @foreach([
                ['q' => '¿Cómo funciona el escáner IA?', 'a' => 'Subes foto o PDF de tu carta. Un modelo especializado en gastronomía detecta platos, precios, secciones y alérgenos. Revisas en el panel y publicas.'],
                ['q' => '¿Qué incluye el plan gratis?', 'a' => 'Carta digital con QR, plantillas profesionales, cambios en tiempo real y escaneos IA limitados. Al registrarte tienes 30 días de Plus gratis (vídeos, traducciones e IA ilimitada). Después vuelves al plan Gratis si no suscribes.'],
                ['q' => '¿Puedo cambiar precios desde el móvil?', 'a' => 'Sí. Los cambios son instantáneos para quien escanee el QR. Ideal para productos agotados o menú del día.'],
                ['q' => '¿Qué son los reels en la carta?', 'a' => 'Son vídeos cortos que se muestran dentro del card de cada plato en la carta QR. Se reproducen en silencio en la tarjeta; al tocar, el cliente ve el detalle ampliado. No ocupan toda la pantalla como en redes sociales.'],
                ['q' => '¿Necesito WiFi en el local para el cliente?', 'a' => 'No. El comensal usa su 4G/5G. La carta está optimizada para señal débil y los vídeos cargan de forma progresiva.'],
                ['q' => '¿Puedo gestionar varios locales?', 'a' => 'Sí. Desde un mismo panel puedes crear y administrar varios negocios, cada uno con su QR y carta independiente. Disponible según tu plan.'],
                ['q' => '¿Puedo cambiar de plantilla después?', 'a' => 'Por supuesto. En el estudio visual eliges entre más de ' . $templateCount . ' plantillas y ajustas colores, tipografía y logo cuando quieras, sin perder tus platos.'],
                ['q' => '¿Cómo funciona el idioma en la carta?', 'a' => 'Desde Plus activas idiomas adicionales (inglés, francés, alemán, italiano, portugués, catalán, ruso…). Puedes traducir con IA o editar manualmente. Si el navegador del comensal está en otro idioma y lo tienes activo, la carta se abre en ese idioma; también puede elegirlo con el selector al escanear el QR.'],
                ['q' => '¿Cómo funciona TVPik?', 'a' => 'TVPik muestra tu carta en las pantallas del local (TV o monitor). Lo configuras y controlas desde el móvil: qué carta ver, qué plato destacar y cuándo actualizar. Sin HDMI ni Fire Stick. Los cambios que haces en Webnu se reflejan al instante en pantalla.'],
                ['q' => '¿Puedo cancelar o cambiar de plan?', 'a' => 'Sí. Puedes mejorar, bajar o cancelar tu suscripción desde el panel de facturación. El plan gratis sigue disponible sin permanencia.'],
                ['q' => '¿Puedo proponer mejoras o nuevas funciones?', 'a' => 'Sí, y nos encanta que lo hagas. Webnu mejora continuamente con feedback de hosteleros. Usa el botón «Sugerir una mejora» en la landing o escríbenos a ' . $contactPublicEmail . ': leemos todas las propuestas y priorizamos lo que más ayuda en sala y cocina.'],
            ] as $i => $faq)
                <div class="faq-item border border-border-subtle rounded-xl overflow-hidden {{ $i === 0 ? 'faq-open' : '' }}">
                    <button type="button" class="w-full p-5 flex justify-between items-center text-left hover:bg-surface-container-low transition-colors font-headline text-headline-md" onclick="toggleFAQ(this)">
                        {{ $faq['q'] }}
                        <span class="material-symbols-outlined faq-icon transition-transform">expand_more</span>
                    </button>
                    <div class="faq-content px-5">
                        <p class="pb-5 text-text-muted">{{ $faq['a'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- CTA final --}}
    <section id="contacto" class="py-16 mb-20">
        <div class="bg-primary rounded-[2rem] p-10 md:p-16 text-center text-on-primary relative overflow-hidden">
            <div class="absolute top-0 right-0 w-72 h-72 bg-white/10 rounded-full -mr-24 -mt-24 blur-3xl"></div>
            <h2 class="font-headline text-headline-xl mb-6 relative z-10">Tu mesa 1 puede tener carta digital esta tarde</h2>
            <p class="text-body-lg mb-8 opacity-90 max-w-xl mx-auto relative z-10">Únete a la hostelería que ya no imprime cartas por cada cambio de precio.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4 relative z-10">
                <a href="#inicio" class="px-10 py-4 bg-white text-primary font-bold rounded-xl hover:scale-[1.02] transition-transform">Empezar gratis</a>
                <a href="{{ $demoUrl }}" target="_blank" class="px-10 py-4 border border-white/40 rounded-xl font-bold hover:bg-white/10 transition-colors">Ver demo en vivo</a>
            </div>
        </div>
    </section>
</main>

<footer class="bg-surface border-t border-border-subtle">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-gutter py-12 flex flex-col md:flex-row justify-between gap-10">
        <div class="max-w-sm space-y-4">
            <div class="font-headline text-headline-md font-bold">Webnu.es</div>
            <p class="text-text-muted text-body-md">Technical hospitality for modern dining. Carta digital, IA y QR para restaurantes que buscan control operativo.</p>
            <p class="text-text-muted text-sm">© {{ date('Y') }} Webnu.es</p>
        </div>
        <div class="grid grid-cols-2 gap-10">
            <div>
                <h5 class="font-label-md font-semibold mb-3">Producto</h5>
                <ul class="space-y-2 text-text-muted text-sm">
                    <li><a href="#funciones" class="hover:text-primary">Funciones</a></li>
                    <li><a href="#demos-carta" class="hover:text-primary">Ejemplos en vivo</a></li>
                    <li><a href="#personalizable" class="hover:text-primary">Plantillas</a></li>
                    <li><a href="#reels" class="hover:text-primary">Reels</a></li>
                    <li><a href="#tvpik" class="hover:text-primary">TVPik</a></li>
                    <li><a href="#process" class="hover:text-primary">Escaneo IA</a></li>
                    <li><a href="#pricing" class="hover:text-primary">Precios</a></li>
                </ul>
            </div>
            <div>
                <h5 class="font-label-md font-semibold mb-3">Cuenta</h5>
                <ul class="space-y-2 text-text-muted text-sm">
                    <li><a href="{{ $loginUrl }}" class="hover:text-primary">Login</a></li>
                    <li><a href="#inicio" class="hover:text-primary">Inicio</a></li>
                </ul>
            </div>
        </div>
    </div>
</footer>

<div id="suggestion-modal" class="landing-modal" hidden aria-hidden="true">
    <div class="landing-modal__backdrop" data-suggestion-close></div>
    <div class="landing-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="suggestion-modal-title">
        <button type="button" class="landing-modal__close" data-suggestion-close aria-label="Cerrar">
            <span class="material-symbols-outlined">close</span>
        </button>
        <div class="landing-modal__header">
            <span class="material-symbols-outlined landing-modal__icon">lightbulb</span>
            <h2 id="suggestion-modal-title" class="font-headline text-headline-md">Sugerir una mejora</h2>
            <p class="text-label-md text-text-muted">Cuéntanos qué te falta o qué te facilitaría el turno. Lo leemos todos.</p>
        </div>
        <form id="suggestion-form" action="{{ route('suggestion') }}" method="POST" class="landing-modal__form space-y-4">
            @csrf
            <div>
                <label for="suggestion-name" class="text-label-md text-on-surface-variant block mb-1">Tu nombre</label>
                <input id="suggestion-name" name="name" required maxlength="255" class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="María, chef de La Brasa"/>
            </div>
            <div>
                <label for="suggestion-email" class="text-label-md text-on-surface-variant block mb-1">Email</label>
                <input id="suggestion-email" name="email" type="email" required maxlength="255" class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="tu@restaurante.com" autocomplete="email"/>
            </div>
            <div>
                <label for="suggestion-message" class="text-label-md text-on-surface-variant block mb-1">Tu sugerencia</label>
                <textarea id="suggestion-message" name="message" required maxlength="3000" rows="4" class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none resize-y min-h-[120px]" placeholder="Me gustaría poder…"></textarea>
            </div>
            <p id="suggestion-error" class="text-label-sm text-red-600 hidden" role="alert"></p>
            <p id="suggestion-success" class="text-label-sm text-primary font-medium hidden" role="status"></p>
            <button type="submit" id="suggestion-submit" class="w-full py-3 rounded-lg bg-primary text-on-primary text-label-md font-semibold hover:opacity-90 transition-opacity flex items-center justify-center gap-2">
                Enviar sugerencia <span class="material-symbols-outlined text-[20px]">send</span>
            </button>
        </form>
    </div>
</div>

<script src="{{ asset('js/landing-preview.js') }}"></script>
</body>
</html>
