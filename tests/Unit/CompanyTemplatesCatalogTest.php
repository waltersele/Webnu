<?php

namespace Tests\Unit;

use App\Services\CompanyTemplatesCatalog;
use Tests\TestCase;

class CompanyTemplatesCatalogTest extends TestCase
{
    public function test_gallery_includes_nucleus_and_excludes_soft_retired(): void
    {
        $catalog = new CompanyTemplatesCatalog();
        $keys = $catalog->galleryKeys();

        foreach (['pasion', 'lumiere', 'nocturne', 'ambre', 'swipe', 'rail', 'grid', 'deck', 'harbor', 'temporada', 'catalogo', 'mar'] as $key) {
            $this->assertContains($key, $keys, "Expected {$key} in gallery");
        }

        foreach (['otaku', 'fastfood', 'pizza', 'elegance', 'velvet', 'saffron', 'maison', 'japo', 'asador'] as $key) {
            $this->assertNotContains($key, $keys, "Expected {$key} soft-retired from gallery");
        }
    }

    public function test_legacy_current_template_appears_in_gallery(): void
    {
        $catalog = new CompanyTemplatesCatalog();
        $gallery = $catalog->forGallery('japo');

        $this->assertArrayHasKey('japo', $gallery);
        $this->assertTrue($catalog->isLegacy('japo'));
        $this->assertTrue($catalog->isLegacy('asador'));
        $this->assertFalse($catalog->isInGallery('japo'));
    }

    public function test_tv_orientations_include_landscape_and_portrait(): void
    {
        $catalog = new CompanyTemplatesCatalog();
        $orients = $catalog->tvOrientations('lumiere');

        $this->assertContains('landscape', $orients);
        $this->assertContains('portrait', $orients);
    }
}
