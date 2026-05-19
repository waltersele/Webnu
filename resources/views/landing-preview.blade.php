<!DOCTYPE html>
<html class="scroll-smooth" lang="es">
<head>
    @include('landing.partials.head')
</head>
<body class="bg-background text-on-surface text-body-md">
@php
    $loginUrl = route('login');
    $registerUrl = route('register');
    $demoUrl = url('/carta/demo');
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
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#reels">Reels</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#process">Escaneo IA</a>
        <a class="text-text-muted hover:text-primary transition-colors text-label-md" href="#plantillas">Plantillas</a>
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
            </div>
            <h1
                id="hero-headline"
                class="font-headline text-headline-xl text-on-surface leading-tight min-h-[3.6em] md:min-h-[2.4em]"
                data-hooks='@json($heroHooks)'
            >{{ $heroHooks[0] }}</h1>
            <p class="text-body-lg text-text-muted max-w-lg">
                La plataforma técnica para hostelería: convierte tu carta física en una experiencia digital con plantillas profesionales, vídeos en platos y gestión multi-local.
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
                <h3 class="font-headline text-headline-md text-on-surface">Crea tu carta ahora</h3>
                <div class="mt-4 flex gap-2">
                    <div id="step-1-indicator" class="h-1 flex-1 rounded-full step-active"></div>
                    <div id="step-2-indicator" class="h-1 flex-1 rounded-full step-inactive"></div>
                </div>
                <p class="mt-2 text-label-sm text-text-muted">Paso 1 de 2 · Plan gratis · Sin tarjeta</p>
            </div>
            <form id="hero-registration" action="{{ $registerUrl }}" method="POST" class="space-y-4">
                @csrf
                <div id="step-1-fields" class="space-y-4">
                    <div>
                        <label class="text-label-md text-on-surface-variant block mb-1">Nombre del restaurante</label>
                        <input name="business_name" required class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="Ej. La Brasa de Juan" type="text" autocomplete="organization"/>
                    </div>
                    <div>
                        <label class="text-label-md text-on-surface-variant block mb-1">Email profesional</label>
                        <input name="email" required class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary focus:border-primary outline-none" placeholder="chef@restaurante.com" type="email" autocomplete="email"/>
                    </div>
                    <button type="button" onclick="landingNextStep()" class="w-full py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 transition-opacity font-semibold flex items-center justify-center gap-2">
                        Continuar <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </div>
                <div id="step-2-fields" class="hidden space-y-4">
                    <div>
                        <label class="text-label-md text-on-surface-variant block mb-1">Tu nombre</label>
                        <input name="name" required class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary outline-none" placeholder="Nombre y apellidos" type="text" autocomplete="name"/>
                    </div>
                    <div>
                        <label class="text-label-md text-on-surface-variant block mb-1">Contraseña</label>
                        <input name="password" required minlength="8" class="w-full px-4 py-3 rounded-lg border border-border-subtle focus:ring-2 focus:ring-primary outline-none" placeholder="Mínimo 8 caracteres" type="password" autocomplete="new-password"/>
                        <input type="hidden" name="password_confirmation" id="landing-pwd-confirm" value=""/>
                    </div>
                    <label class="flex items-start gap-2 text-label-md text-text-muted cursor-pointer">
                        <input type="checkbox" name="privacy_policy" value="1" required class="mt-1 rounded border-border-subtle"/>
                        Acepto la política de privacidad
                    </label>
                    <button type="submit" class="w-full py-4 bg-primary text-on-primary text-label-md rounded-lg hover:opacity-90 font-semibold">
                        Crear mi carta gratis
                    </button>
                </div>
            </form>
        </div>
    </section>

    {{-- Métricas --}}
    <section class="py-12 border-y border-border-subtle mb-16">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div><div class="text-headline-lg font-headline text-primary">542+</div><div class="text-label-md text-text-muted">Restaurantes activos</div></div>
            <div><div class="text-headline-lg font-headline text-primary">12k</div><div class="text-label-md text-text-muted">Escaneos al mes</div></div>
            <div><div class="text-headline-lg font-headline text-primary">9</div><div class="text-label-md text-text-muted">Plantillas pro</div></div>
            <div><div class="text-headline-lg font-headline text-primary">8 min</div><div class="text-label-md text-text-muted">Setup medio</div></div>
        </div>
    </section>

    {{-- Reels --}}
    <section id="reels" class="py-20 md:py-24">
        <div class="text-center mb-14">
            <h2 class="font-headline text-headline-xl mb-4">Cartas que cobran vida con Reels</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">
                No solo el nombre del plato: muestra la técnica, el vapor y la frescura con vídeos verticales integrados en la carta. <span class="text-primary font-medium">Incluido desde el plan Plus.</span>
            </p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-center">
            <div class="flex justify-center gap-6 md:gap-8">
                <div class="w-56 md:w-64 aspect-[9/16] rounded-[2.5rem] border-[6px] border-on-surface overflow-hidden shadow-xl relative bg-black">
                    <video class="w-full h-full object-cover" autoplay muted loop playsinline poster="{{ asset('img/front/tvpik-dish.jpg') }}">
                        <source src="{{ asset('img/demo/reel-01.mp4') }}" type="video/mp4"/>
                    </video>
                    <div class="absolute bottom-4 left-4 right-4 bg-black/50 backdrop-blur-md p-3 rounded-xl text-white">
                        <p class="text-xs font-bold uppercase tracking-wider mb-1">Chef's choice</p>
                        <p class="text-sm font-medium">Solomillo al punto</p>
                    </div>
                </div>
                <div class="w-56 md:w-64 aspect-[9/16] rounded-[2.5rem] border-[6px] border-on-surface overflow-hidden shadow-xl relative mt-10 md:mt-14 bg-black hidden sm:block">
                    <video class="w-full h-full object-cover" autoplay muted loop playsinline>
                        <source src="{{ asset('img/demo/reel-02.mp4') }}" type="video/mp4"/>
                    </video>
                    <div class="absolute bottom-4 left-4 right-4 bg-black/50 backdrop-blur-md p-3 rounded-xl text-white">
                        <p class="text-xs font-bold uppercase tracking-wider mb-1">Barra</p>
                        <p class="text-sm font-medium">Cóctel de autor</p>
                    </div>
                </div>
            </div>
            <div class="space-y-8">
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">videocam</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">Impacto visual inmediato</h4>
                        <p class="text-text-muted">El cliente ve el plato en movimiento antes de pedir. Ideal para postres, carnes y coctelería.</p>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined">speed</span>
                    </div>
                    <div>
                        <h4 class="font-headline text-headline-md mb-2">Carga optimizada</h4>
                        <p class="text-text-muted">Vídeos comprimidos para 4G: se reproducen sin esperas en la carta QR.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Plantillas --}}
    <section id="plantillas" class="py-20 bg-surface-container-low rounded-3xl px-6 md:px-10 mb-20">
        <div class="text-center mb-12">
            <h2 class="font-headline text-headline-xl mb-4">Plantillas que enamoran</h2>
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">L'Essence, Bistro, Nocturno, Catálogo y más. Cambia el estilo desde el estudio visual sin código.</p>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-4">
            @foreach(collect(config('company_templates.templates', []))->only(['lumiere', 'bistro', 'nocturne', 'temporada', 'catalogo']) as $id => $tpl)
                <a href="{{ $demoUrl }}?tpl={{ $id }}" target="_blank" rel="noopener" class="group bg-surface-container-lowest border border-border-subtle rounded-xl p-4 text-center hover:border-primary transition-colors">
                    <img src="{{ asset($tpl['preview_image']) }}" alt="{{ $tpl['label'] }}" class="w-14 h-14 mx-auto mb-2 object-contain"/>
                    <span class="text-label-md font-medium text-on-surface group-hover:text-primary">{{ $tpl['label'] }}</span>
                </a>
            @endforeach
        </div>
        <div class="text-center mt-10">
            <a href="{{ $demoUrl }}" target="_blank" class="inline-flex px-8 py-3 rounded-lg bg-primary text-on-primary font-medium hover:opacity-90">Ver carta demo en vivo</a>
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
            <p class="text-body-lg text-text-muted max-w-2xl mx-auto">Sin permanencia. Sube de plan solo si necesitas más cartas, vídeos en platos o escaneo IA.</p>
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
                    <li class="flex gap-2 items-start text-text-muted"><span class="material-symbols-outlined text-[20px] shrink-0 opacity-40">cancel</span> Sin vídeos / reels</li>
                    <li class="flex gap-2 items-start text-text-muted"><span class="material-symbols-outlined text-[20px] shrink-0 opacity-40">cancel</span> Sin escaneo IA (foto/PDF)</li>
                </ul>
                <a href="#inicio" class="w-full py-3 rounded-lg border border-border-subtle text-center font-medium hover:bg-surface-container transition-colors">Crear carta gratis</a>
            </div>
            <div class="bg-surface-container-lowest border-2 border-primary p-8 rounded-xl flex flex-col relative md:-translate-y-2 shadow-lg">
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary text-on-primary px-4 py-1 rounded-full text-label-sm font-semibold">Más popular</span>
                <h3 class="font-headline text-headline-md mb-1">Plus</h3>
                <p class="text-label-sm text-text-muted mb-4">Para restaurantes en crecimiento</p>
                <div class="mb-6">
                    <span class="text-4xl font-bold">9,90</span><span class="text-text-muted">€</span>
                    <span class="text-text-muted text-label-md"> / mes · IVA incl.</span>
                </div>
                <ul class="space-y-3 mb-8 flex-grow text-label-md">
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span><span><strong>5 cartas</strong> (negocios)</span></li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Vídeos / reels en platos</li>
                    <li class="flex gap-2 items-start"><span class="material-symbols-outlined text-primary text-[20px] shrink-0">check_circle</span> Escaneo IA (foto y PDF)</li>
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
                ['q' => '¿Puedo cambiar precios desde el móvil?', 'a' => 'Sí. Los cambios son instantáneos para quien escanee el QR. Ideal para productos agotados o menú del día.'],
                ['q' => '¿Necesito WiFi en el local para el cliente?', 'a' => 'No. El comensal usa su 4G/5G. La carta está optimizada para señal débil.'],
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
                    <li><a href="#reels" class="hover:text-primary">Reels</a></li>
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

<script src="{{ asset('js/landing-preview.js') }}"></script>
</body>
</html>
