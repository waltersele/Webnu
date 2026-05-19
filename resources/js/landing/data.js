export function buildLandingContent(config) {
    const a = config.assets || {};
    const templates = config.templates || [];

    return {
        nav: [
            { id: 'inicio', label: 'Empezar' },
            { id: 'plantillas', label: 'Plantillas' },
            { id: 'feature', label: 'Funciones' },
            { id: 'ia', label: 'Escaneo IA' },
            { id: 'tvpik', label: 'TVPik' },
            { id: 'precios', label: 'Precios' },
            { id: 'contact', label: 'Contacto' },
        ],
        hero: {
            eyebrow: 'Carta digital · QR · Estudio visual',
            title: 'Tu carta digital',
            titleHighlight: 'lista en minutos.',
            subtitle:
                'Plantillas de diseño profesional, escaneo con IA, QR al instante y varios locales en un solo panel. Sin tarjeta, 30 días gratis.',
            formTitle: 'Crea tu cuenta gratis',
            formNote: 'Sin tarjeta · Sin permanencia · Acceso inmediato al panel',
            perks: ['9 plantillas listas', 'QR descargable', 'Escaneo IA incluido'],
        },
        templates: {
            title: 'Plantillas que enamoran a tus clientes',
            subtitle:
                'Elige el estilo en el estudio visual: modo oscuro, reels en platos, listados compactos o carta clásica. Cambia cuando quieras.',
            cta: 'Probar todas en la demo',
        },
        success: {
            title: 'Del papel al móvil sin perder tu marca',
            subtitle: 'Publica, actualiza precios y promociones en segundos desde cualquier dispositivo.',
            blocks: [
                {
                    title: 'Estudio visual sin código',
                    text: 'Colores, tipografías, logo y cabecera en un flujo guiado. Ves el resultado en vivo antes de publicar. Tus clientes escanean el QR y ven la carta al instante.',
                    image: a.clients,
                    demos: config.demos || [],
                },
                {
                    title: 'Un panel, todos tus locales',
                    text: 'Gestiona varios restaurantes con el mismo usuario: cada negocio con su QR, plantilla y carta independiente. Ideal para cadenas y franquicias.',
                    bullets: [
                        'QR único por local',
                        'Cambios ilimitados sin coste extra',
                        'Vídeos tipo reel en platos destacados',
                    ],
                },
            ],
        },
        ia: {
            badge: 'Exclusivo Webnu',
            title: 'Digitaliza tu carta con IA en minutos',
            text: 'Fotografía tu carta en papel o sube un PDF: detectamos secciones, platos, precios y alérgenos. Revisas en el panel y publicas.',
            points: [
                'Guía de encuadre desde el móvil',
                'Importación directa al panel — sin reescribir',
                'Ideal para estrenar carta digital el mismo día',
            ],
        },
        tvpik: {
            title: 'Tu carta en pantalla con TVPik',
            subtitle: 'Proyecta platos y precios en Smart TV del local. Se actualiza sola cuando editas en Webnu.',
            badge: 'Integración TVPik',
            heading: 'Digital signage conectado a tu carta',
            text: 'Sincroniza Webnu con TVPik y muestra promociones y platos del día en barra, salón o terraza — la misma carta que escanean con QR.',
            features: [
                'Actualización automática al guardar cambios',
                'Misma información que la carta QR',
                'Sin rediseñar contenido para pantallas',
            ],
            demoUrl: config.routes?.demoMenu || '/carta/demo',
            barImage: a.tvpikBar,
            dishImage: a.tvpikDish,
        },
        features: {
            title: '¿Qué incluye Webnu?',
            subtitle: 'Todo lo que un restaurante necesita para digitalizar la carta — sin agencias ni desarrolladores.',
            items: [
                {
                    icon: 'scan',
                    title: 'Escaneo IA',
                    text: 'Foto o PDF → carta estructurada lista para revisar.',
                    highlight: true,
                },
                {
                    icon: 'brush',
                    title: 'Estudio visual',
                    text: 'Plantillas, colores y marca en un flujo de 4 pasos.',
                },
                {
                    icon: 'qr',
                    title: 'QR al instante',
                    text: 'Descarga e imprime. Adhesivos profesionales bajo demanda.',
                },
                {
                    icon: 'devices',
                    title: 'Reels en platos',
                    text: 'Vídeos cortos en fichas destacadas — más apetecible que una foto.',
                    highlight: true,
                },
                {
                    icon: 'docs',
                    title: 'Multi-negocio',
                    text: 'Varios locales, un login. Cada uno con su carta y QR.',
                },
                {
                    icon: 'updates',
                    title: 'Alérgenos y etiquetas',
                    text: 'Iconografía clara para cumplir normativa y tranquilizar al cliente.',
                },
                {
                    icon: 'pdf',
                    title: 'Modo PDF',
                    text: '¿Prefieres tu PDF? También puedes publicarlo junto a la carta digital.',
                },
                {
                    icon: 'support',
                    title: 'Soporte humano',
                    text: 'Te ayudamos a publicar la primera carta sin complicaciones.',
                },
            ],
        },
        pricing: {
            title: 'Precios transparentes',
            subtitle: 'Empieza gratis 30 días. Sin permanencia. Cambios ilimitados incluidos.',
            plans: [
                {
                    id: 'monthly',
                    name: 'Mensual',
                    tagline: 'Flexibilidad total',
                    price: '10',
                    period: 'Al mes · IVA incluido',
                    perks: [
                        'Todas las plantillas y el estudio visual',
                        'Escaneo IA y QR ilimitados',
                        'Negocios ilimitados en una cuenta',
                        'Reels y alérgenos en la carta',
                        'Sin costes ocultos',
                    ],
                },
                {
                    id: 'yearly',
                    name: 'Anual',
                    tagline: 'Ahorra 2 meses',
                    price: '100',
                    period: 'Al año · IVA incluido',
                    featured: true,
                    perks: [
                        'Todo lo del plan mensual',
                        '2 meses gratis respecto al mensual',
                        'Prioridad en soporte',
                        'Ideal si ya tienes carta estable',
                    ],
                },
            ],
        },
        stats: [
            { value: 9, suffix: '+', label: 'Plantillas profesionales' },
            { value: 30, suffix: '', label: 'Días de prueba gratis' },
            { value: 2, suffix: ' min', label: 'Para publicar tu primera carta' },
        ],
        contact: {
            title: '¿Prefieres que te llamemos?',
            subtitle: 'Déjanos tu teléfono y te ayudamos a montar la primera carta.',
        },
        templatesList: templates,
    };
}
