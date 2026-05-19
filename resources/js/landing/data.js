export function buildLandingContent(config) {
    const a = config.assets || {};

    return {
        nav: [
            { id: 'inicio', label: 'Inicio' },
            { id: 'exito', label: 'Tu éxito' },
            { id: 'feature', label: '¿Qué es?' },
            { id: 'ia', label: 'Escaneo IA' },
            { id: 'ventajas', label: 'Ventajas' },
            { id: 'tvpik', label: 'TVPik' },
            { id: 'precios', label: 'Precios' },
            { id: 'contact', label: 'Te llamamos' },
        ],
        hero: {
            title: 'Totalmente GRATIS durante 30 días.',
            titleHighlight: 'Tu carta digital, en un click.',
            subtitle:
                'Sin costes ocultos, con cambios ilimitados, sin permanencia y tu código QR disponible al instante. ¿A qué esperas? Tus clientes lo necesitan.',
            formTitle: 'Pruébanos GRATIS durante 30 días, sin permanencia.',
        },
        success: {
            title: 'Tu éxito, nuestra motivación',
            subtitle: 'Entendemos que cuanto más éxito tengas, mejor te podremos acompañar.',
            blocks: [
                {
                    title: 'Más fácil de lo que piensas',
                    text: 'Podrás modificar tu carta tantas veces como desees, de forma gratuita, y ofreciendo a los clientes tus productos fuera de carta, promociones, menús del día… Queremos que tu negocio sea lo más rentable posible, por eso hemos desarrollado este panel de control para que lo puedas hacer ¡GRATIS!',
                    image: a.clients,
                    demos: config.demos || [],
                },
                {
                    title: 'Tu imagen corporativa, también digital.',
                    text: 'Para que puedas seguir teniendo tu imagen, también hemos querido ofrecerte la posibilidad de que subas tus archivos PDF para que tu identidad no se vea afectada. Entendemos que la imagen cuenta, y nos encanta que la puedas conservar.',
                    image: a.mock1,
                    video: null,
                },
            ],
            video: a.mockVideo,
        },
        ia: {
            badge: 'Nuevo',
            title: 'Digitaliza tu carta con IA',
            text: 'Fotografía tu carta en papel o sube un PDF: nuestra inteligencia artificial detecta secciones, platos, precios y alérgenos. Revisas y publicas en minutos.',
            points: [
                'Escaneo desde móvil con guía de encuadre',
                'Importación a tu panel sin reescribir todo',
                'Ideal para estrenar carta digital rápido',
            ],
        },
        tvpik: {
            title: 'Tu carta en pantalla con TVPik',
            subtitle: 'Proyecta tu carta digital en Smart TV y monitores del local. Se actualiza sola cuando editas en Webnu.',
            badge: 'Integración TVPik',
            heading: 'Digital signage conectado a tu carta',
            text: 'Sincroniza Webnu con TVPik y muestra platos, precios y promociones en las pantallas de tu restaurante sin volver a diseñar nada.',
            features: [
                'Actualización automática al editar la carta',
                'Ideal para barras, salones y terrazas',
                'Misma carta que escanean tus clientes con QR',
            ],
            demoUrl: 'https://webnu.es/carta/webnu-test',
            barImage: a.tvpikBar,
            dishImage: a.tvpikDish,
        },
        features: {
            title: '¿Qué incluye Webnu?',
            centerImage: a.mockMenu,
            left: [
                { icon: 'brush', title: 'Personaliza tu carta', text: 'Elige el estilo que más se adecúe a tu negocio' },
                { icon: 'devices', title: 'Diseño adaptable', text: 'Tu carta visible en todos los dispositivos' },
                { icon: 'docs', title: 'Manejo sencillo', text: 'Vídeos y documentación para facilitar su uso' },
                { icon: 'support', title: 'Soporte técnico', text: 'Equipo dedicado para que funcione a las mil maravillas' },
            ],
            right: [
                { icon: 'updates', title: 'Actualizaciones continuas', text: 'Nuevas funcionalidades para hacerte la vida más fácil' },
                { icon: 'pdf', title: 'También en PDF', text: 'Sube tu PDF en el panel de control si lo prefieres' },
                { icon: 'save', title: 'Ahorra en costes', text: 'Crea tu carta y haz tantos cambios como desees' },
                { icon: 'print', title: 'Imprimimos tus códigos', text: 'Impresión profesional de adhesivos con tu QR' },
            ],
        },
        advantages: {
            title: 'Así es como sería tu experiencia Webnu',
            lists: [
                ['Para fidelizar clientes', 'Para ahorrar costes', 'Para tener control absoluto de tu stock en todo momento'],
                [
                    'Para incluir todos los datos necesarios sobre tus platos',
                    'Para hacer una carta dinámica y sin errores',
                    'Para dar una mayor seguridad tanto a tus empleados como a tus clientes',
                ],
            ],
            screenshots: a.screenshots || [],
        },
        pricing: {
            title: 'Precios',
            subtitle: 'Queremos que pagues lo menos posible, para que tu negocio sea más rentable. Te ayudamos a ahorrar.',
            plans: [
                {
                    id: 'monthly',
                    name: 'Mensual',
                    tagline: 'Para los precavidos',
                    price: '10',
                    period: 'Al mes. I.V.A. incluido',
                    perks: [
                        'Generación automática de QR para que lo imprimas tú mismo',
                        'Posibilidad de impresión profesional de adhesivos con tu QR y logotipos',
                        'Carta digital inmediata',
                        'Cambios ilimitados',
                        'Sin costes ocultos',
                        'Diseño personalizable',
                        'Negocios ilimitados',
                    ],
                },
                {
                    id: 'yearly',
                    name: 'Anual',
                    tagline: '¡Ahorra dos meses!',
                    price: '100',
                    period: 'Al año. I.V.A. incluido',
                    featured: true,
                    perks: [
                        'Generación automática de QR para que lo imprimas tú mismo',
                        'Posibilidad de impresión profesional de adhesivos con tu QR y logotipos',
                        'Carta digital inmediata',
                        'Cambios ilimitados',
                        'Sin costes ocultos',
                        'Diseño personalizable',
                        'Negocios ilimitados',
                    ],
                },
            ],
        },
        stats: [
            { icon: 'clients', value: 542, suffix: '', label: 'Clientes satisfechos' },
            { icon: 'scans', value: 12402, suffix: '', label: 'Escaneos' },
            { icon: 'edits', value: 123141, suffix: 'K', label: 'Modificaciones GRATIS' },
        ],
        contact: {
            title: 'Te llamamos',
            subtitle: 'Déjanos tu contacto y te llamamos sin compromiso.',
        },
    };
}
