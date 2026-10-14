<?php

namespace Tests\Unit;

use App\Services\Tv\TvTemplateRegistry;
use Tests\TestCase;

class TvTemplateRegistryTest extends TestCase
{
    public function test_default_layout_is_carta_pack_mode(): void
    {
        $registry = new TvTemplateRegistry();

        $this->assertSame('carta', $registry->defaultLayout());
        $this->assertSame('tv.templates.menu', $registry->viewForLayout('carta'));
        $this->assertSame('tv.templates.spotlight', $registry->viewForLayout('destacado'));
    }

    public function test_gallery_prefers_pack_modes_and_keeps_legacy_layouts_resolvable(): void
    {
        $registry = new TvTemplateRegistry();
        $gallery = $registry->galleryTemplates();

        $this->assertArrayHasKey('carta', $gallery);
        $this->assertArrayHasKey('destacado', $gallery);
        $this->assertArrayNotHasKey('menu', $gallery);
        $this->assertSame('menu', $registry->resolveLayout('menu'));
    }

    public function test_orientation_resolve(): void
    {
        $registry = new TvTemplateRegistry();

        $this->assertSame('landscape', $registry->resolveOrientation(null));
        $this->assertSame('portrait', $registry->resolveOrientation('portrait'));
        $this->assertSame('landscape', $registry->resolveOrientation('diagonal'));
    }
}
