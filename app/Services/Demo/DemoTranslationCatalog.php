<?php

namespace App\Services\Demo;

/**
 * Traducciones curadas de la carta demo principal (/carta/demo).
 * Usado por DemoMenuSeeder y scripts/seed-demo-translations.php.
 */
class DemoTranslationCatalog
{
    /** @return array<string, array<string, string>> sectionName => [locale => translatedName] */
    public function sections(): array
    {
        return [
            'Entrantes' => ['en' => 'Starters', 'ru' => 'Закуски'],
            'Principales' => ['en' => 'Main courses', 'ru' => 'Основные блюда'],
            'Postres' => ['en' => 'Desserts', 'ru' => 'Десерты'],
        ];
    }

    /**
     * @return array<string, array<string, array{name: string, description: string}>>
     */
    public function products(): array
    {
        return [
            'Gazpacho andaluz' => [
                'en' => ['name' => 'Andalusian gazpacho', 'description' => 'Pear tomato, cucumber, pepper and estate extra virgin olive oil.'],
                'ru' => ['name' => 'Гаспачо по-андалузски', 'description' => 'Помидоры грушевидные, огурец, перец и оливковое масло extra virgin с поместья.'],
            ],
            'Croquetas de jamón ibérico' => [
                'en' => ['name' => 'Iberian ham croquettes', 'description' => 'House recipe, creamy béchamel and 36-month cured ham. Six pieces.'],
                'ru' => ['name' => 'Крокеты с иберийской ветчиной', 'description' => 'Домашний рецепт, нежная бешамель и ветчина 36 месяцев выдержки. 6 штук.'],
            ],
            'Ensalada de burrata' => [
                'en' => ['name' => 'Burrata salad', 'description' => 'Fresh burrata, confit cherry tomatoes, basil pesto and balsamic reduction.'],
                'ru' => ['name' => 'Салат с burrata', 'description' => 'Свежая burrata, томаты черри confit, pesto из базилика и balsamic reduction.'],
            ],
            'Solomillo al Pedro Ximénez' => [
                'en' => ['name' => 'Sirloin with Pedro Ximénez', 'description' => 'Beef sirloin, Pedro Ximénez reduction, confit potatoes and seasonal vegetables.'],
                'ru' => ['name' => 'Вырезка с Pedro Ximénez', 'description' => 'Говяжья вырезка, соус Pedro Ximénez, картофель confit и сезонные овощи.'],
            ],
            'Lubina a la espalda' => [
                'en' => ['name' => 'Grilled sea bass', 'description' => 'Wild sea bass, tender garlic soffrito, guindilla pepper and garden vegetables.'],
                'ru' => ['name' => 'Морской судак на гриле', 'description' => 'Дикая лубина, нежный refrito из чеснока, перец guindilla и овощи с огорода.'],
            ],
            'Arroz meloso de setas' => [
                'en' => ['name' => 'Mushroom risotto', 'description' => 'Bomba rice, seasonal mushrooms, Parmigiano Reggiano and truffle oil.'],
                'ru' => ['name' => 'Ризотто с грибами', 'description' => 'Рис bomba, сезонные грибы, Parmigiano Reggiano и масло трюфеля.'],
            ],
            'Tarta de queso' => [
                'en' => ['name' => 'Cheesecake', 'description' => 'Basque style, baked to order. Red berry coulis from the orchard.'],
                'ru' => ['name' => 'Чизкейк', 'description' => 'По-баскски, печётся на заказ. Coulis из красных ягод с огорода.'],
            ],
            'Brownie con helado' => [
                'en' => ['name' => 'Brownie with ice cream', 'description' => '70% chocolate, artisan vanilla ice cream and hazelnut crumble.'],
                'ru' => ['name' => 'Брауни с мороженым', 'description' => 'Шоколад 70 %, ванильное мороженое ручной работы и crumble из фундука.'],
            ],
        ];
    }

    /** @return list<string> */
    public function locales(): array
    {
        return ['en', 'ru'];
    }
}
