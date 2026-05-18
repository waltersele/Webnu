<?php

namespace App\Services;

use App\Allergen;
use Illuminate\Support\Str;

class AllergenCatalogService
{
    public function sync(): void
    {
        $catalog = config('allergens.catalog', []);
        $dir = public_path('img/alergenos');

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        foreach ($catalog as $slug => $item) {
            Allergen::updateOrCreate(
                ['name' => $item['name']],
                ['image' => 'alergenos/' . $slug . '.svg']
            );
        }
    }

    public static function metaFor(Allergen $allergen): array
    {
        $slug = self::slugFromAllergen($allergen);
        $catalog = config('allergens.catalog', []);

        if ($slug && isset($catalog[$slug])) {
            return array_merge(['slug' => $slug], $catalog[$slug]);
        }

        return [
            'slug' => $slug ?: Str::slug($allergen->name),
            'name' => $allergen->name,
            'color' => '#78909C',
            'icon' => 'ri-alert-line',
            'abbr' => Str::upper(Str::substr($allergen->name, 0, 2)),
        ];
    }

    public static function slugFromAllergen(Allergen $allergen): ?string
    {
        if (!$allergen->image) {
            return null;
        }

        return pathinfo($allergen->image, PATHINFO_FILENAME);
    }

    protected function writeIcon(string $dir, string $slug, string $color, string $abbr): void
    {
        $path = $dir . DIRECTORY_SEPARATOR . $slug . '.svg';
        $safeAbbr = htmlspecialchars($abbr, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $safeColor = preg_match('/^#[0-9A-Fa-f]{6}$/', $color) ? $color : '#78909C';

        $svg = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" role="img" aria-hidden="true">'
            . '<circle cx="24" cy="24" r="24" fill="' . $safeColor . '"/>'
            . '<text x="24" y="30" font-family="Inter,Arial,sans-serif" font-size="13" font-weight="700" fill="#ffffff" text-anchor="middle">'
            . $safeAbbr
            . '</text></svg>';

        file_put_contents($path, $svg);
    }
}
