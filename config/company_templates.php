<?php

$colorKeys = ['primary', 'accent', 'background', 'surface', 'text', 'text_muted'];

return [
    'templates' => [
        'lumiere' => [
            'label' => "L'Essence",
            'description' => 'Modo oscuro, fotos grandes y tipografía elegante.',
            'group' => 'modern',
            'recommended' => true,
            'preview_image' => 'img/admin/templates/lumiere.svg',
        ],
        'bistro' => [
            'label' => 'Bistro',
            'description' => 'Fondo claro, tarjetas y chips de categoría.',
            'group' => 'modern',
            'recommended' => true,
            'preview_image' => 'img/admin/templates/bistro.svg',
        ],
        'nocturne' => [
            'label' => 'Nocturno',
            'description' => 'Modo oscuro, plato destacado con overlay y categorías fijas.',
            'group' => 'modern',
            'recommended' => true,
            'preview_image' => 'img/admin/templates/nocturne.svg',
        ],
        'temporada' => [
            'label' => 'Temporada',
            'description' => 'Menú claro, tarjetas con foto y etiquetas de alérgenos.',
            'group' => 'modern',
            'recommended' => true,
            'preview_image' => 'img/admin/templates/temporada.svg',
        ],
        'catalogo' => [
            'label' => 'Catálogo',
            'description' => 'Listado horizontal compacto con chips de categoría fijos.',
            'group' => 'modern',
            'recommended' => false,
            'preview_image' => 'img/admin/templates/catalogo.svg',
        ],
        'basic' => [
            'label' => 'Básica',
            'description' => 'Carta clara y compacta, ideal para cualquier negocio.',
            'group' => 'classic',
            'recommended' => false,
            'preview_image' => 'img/admin/templates/basic.svg',
        ],
        'pasion' => [
            'label' => 'Pasión',
            'description' => 'Estilo cálido y tradicional.',
            'group' => 'classic',
            'recommended' => false,
            'preview_image' => 'img/admin/templates/pasion.svg',
        ],
        'oriental' => [
            'label' => 'Oriental',
            'description' => 'Inspiración asiática.',
            'group' => 'classic',
            'recommended' => false,
            'preview_image' => 'img/admin/templates/oriental.svg',
        ],
        'visual' => [
            'label' => 'Visual',
            'description' => 'Fotos grandes y menú horizontal.',
            'group' => 'classic',
            'recommended' => false,
            'preview_image' => 'img/admin/templates/visual.svg',
        ],
    ],

    'color_keys' => [
        'primary' => 'Color principal',
        'accent' => 'Color de acento',
        'background' => 'Fondo',
        'surface' => 'Tarjetas / superficies',
        'text' => 'Texto',
        'text_muted' => 'Texto secundario',
    ],

    'font_keys' => [
        'font_heading' => 'Fuente de títulos',
        'font_body' => 'Fuente de textos',
    ],

    'font_defaults' => [
        'font_heading' => 'playfair',
        'font_body' => 'inter',
    ],

    'fonts' => [
        'inter' => ['label' => 'Inter', 'family' => 'Inter', 'category' => 'sans-serif', 'weights' => '400;500;600;700'],
        'open_sans' => ['label' => 'Open Sans', 'family' => 'Open Sans', 'category' => 'sans-serif', 'weights' => '400;600;700'],
        'montserrat' => ['label' => 'Montserrat', 'family' => 'Montserrat', 'category' => 'sans-serif', 'weights' => '500;600;700'],
        'poppins' => ['label' => 'Poppins', 'family' => 'Poppins', 'category' => 'sans-serif', 'weights' => '400;500;600'],
        'lora' => ['label' => 'Lora', 'family' => 'Lora', 'category' => 'serif', 'weights' => '400;600;700'],
        'playfair' => ['label' => 'Playfair Display', 'family' => 'Playfair Display', 'category' => 'serif', 'weights' => '400;600;700'],
        'cormorant' => ['label' => 'Cormorant Garamond', 'family' => 'Cormorant Garamond', 'category' => 'serif', 'weights' => '400;600;700'],
        'merriweather' => ['label' => 'Merriweather', 'family' => 'Merriweather', 'category' => 'serif', 'weights' => '400;700'],
    ],

    'defaults' => [
        'basic' => [
            'primary' => '#0074d9',
            'accent' => '#e65100',
            'background' => '#ffffff',
            'surface' => '#f8f9fa',
            'text' => '#212529',
            'text_muted' => '#6c757d',
        ],
        'pasion' => [
            'primary' => '#8b0000',
            'accent' => '#d4a017',
            'background' => '#fff8f0',
            'surface' => '#ffffff',
            'text' => '#2c1810',
            'text_muted' => '#6b5344',
        ],
        'oriental' => [
            'primary' => '#c41e3a',
            'accent' => '#d4af37',
            'background' => '#1a1a1a',
            'surface' => '#2d2d2d',
            'text' => '#f5f5f5',
            'text_muted' => '#a0a0a0',
        ],
        'visual' => [
            'primary' => '#1565c0',
            'accent' => '#ff6f00',
            'background' => '#fafafa',
            'surface' => '#ffffff',
            'text' => '#212121',
            'text_muted' => '#757575',
        ],
        'lumiere' => [
            'primary' => '#8ec5ff',
            'accent' => '#d4af37',
            'background' => '#0a0e14',
            'surface' => '#141a22',
            'text' => '#f4f7fb',
            'text_muted' => '#9aa8b8',
        ],
        'bistro' => [
            'primary' => '#1a365d',
            'accent' => '#e8b923',
            'background' => '#eef1f6',
            'surface' => '#ffffff',
            'text' => '#0f172a',
            'text_muted' => '#64748b',
        ],
        'nocturne' => [
            'primary' => '#7ec8ff',
            'accent' => '#d4af37',
            'background' => '#0a0e14',
            'surface' => '#141a22',
            'text' => '#f4f7fb',
            'text_muted' => '#9aa8b8',
        ],
        'temporada' => [
            'primary' => '#1e3a5f',
            'accent' => '#2563eb',
            'background' => '#f4f6f9',
            'surface' => '#ffffff',
            'text' => '#0f172a',
            'text_muted' => '#64748b',
        ],
        'catalogo' => [
            'primary' => '#334155',
            'accent' => '#0ea5e9',
            'background' => '#f8fafc',
            'surface' => '#ffffff',
            'text' => '#1e293b',
            'text_muted' => '#64748b',
        ],
    ],

  /*
  | Paletas rápidas por plantilla (nombre => colores)
  */
    'presets' => [
        'lumiere' => [
            'Noche elegante' => [
                'primary' => '#7ec8ff',
                'accent' => '#c9a962',
                'background' => '#0d1117',
                'surface' => '#161b22',
                'text' => '#f0f6fc',
                'text_muted' => '#8b949e',
            ],
            'Carbón y oro' => [
                'primary' => '#e8d5a3',
                'accent' => '#d4af37',
                'background' => '#121212',
                'surface' => '#1e1e1e',
                'text' => '#fafafa',
                'text_muted' => '#9e9e9e',
            ],
            'Medianoche azul' => [
                'primary' => '#64b5f6',
                'accent' => '#ff8a65',
                'background' => '#0a1628',
                'surface' => '#132238',
                'text' => '#eceff1',
                'text_muted' => '#90a4ae',
            ],
        ],
        'bistro' => [
            'Clásico marino' => [
                'primary' => '#1e3a5f',
                'accent' => '#f5c518',
                'background' => '#f0f2f5',
                'surface' => '#ffffff',
                'text' => '#1a2332',
                'text_muted' => '#64748b',
            ],
            'Fresco verde' => [
                'primary' => '#2d6a4f',
                'accent' => '#95d5b2',
                'background' => '#f8faf9',
                'surface' => '#ffffff',
                'text' => '#1b4332',
                'text_muted' => '#52796f',
            ],
            'Terracota' => [
                'primary' => '#9c4221',
                'accent' => '#f4a261',
                'background' => '#faf6f2',
                'surface' => '#ffffff',
                'text' => '#3d2817',
                'text_muted' => '#8b7355',
            ],
        ],
        'basic' => [
            'Webnu azul' => [
                'primary' => '#0074d9',
                'accent' => '#e65100',
                'background' => '#ffffff',
                'surface' => '#f8f9fa',
                'text' => '#212529',
                'text_muted' => '#6c757d',
            ],
        ],
        'pasion' => [
            'Vino tinto' => [
                'primary' => '#8b0000',
                'accent' => '#d4a017',
                'background' => '#fff8f0',
                'surface' => '#ffffff',
                'text' => '#2c1810',
                'text_muted' => '#6b5344',
            ],
        ],
        'oriental' => [
            'Rojo imperial' => [
                'primary' => '#c41e3a',
                'accent' => '#d4af37',
                'background' => '#1a1a1a',
                'surface' => '#2d2d2d',
                'text' => '#f5f5f5',
                'text_muted' => '#a0a0a0',
            ],
        ],
        'visual' => [
            'Azul moderno' => [
                'primary' => '#1565c0',
                'accent' => '#ff6f00',
                'background' => '#fafafa',
                'surface' => '#ffffff',
                'text' => '#212121',
                'text_muted' => '#757575',
            ],
        ],
        'nocturne' => [
            'Coctelería' => [
                'primary' => '#7ec8ff',
                'accent' => '#c9a962',
                'background' => '#0a0e14',
                'surface' => '#141a22',
                'text' => '#f4f7fb',
                'text_muted' => '#9aa8b8',
            ],
        ],
        'temporada' => [
            'Primavera' => [
                'primary' => '#1e3a5f',
                'accent' => '#2563eb',
                'background' => '#f4f6f9',
                'surface' => '#ffffff',
                'text' => '#0f172a',
                'text_muted' => '#64748b',
            ],
        ],
        'catalogo' => [
            'Minimal' => [
                'primary' => '#334155',
                'accent' => '#0ea5e9',
                'background' => '#f8fafc',
                'surface' => '#ffffff',
                'text' => '#1e293b',
                'text_muted' => '#64748b',
            ],
        ],
    ],
];
