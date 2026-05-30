<?php

namespace Tests\Feature;

use App\Company;
use App\Product;
use App\Section;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MenuHeroSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalogo_template_renders_logo_chip_in_header(): void
    {
        $company = $this->createMenuCompany('catalogo', true);

        $response = $this->get(route('public.company', ['companySlug' => $company->slug]));

        $response->assertOk();
        $response->assertSee('wn-menu-hero__logo-chip', false);
        $response->assertSee('wn-modern-header', false);
    }

    public function test_storeheader_persists_banner_metadata(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'name' => 'Banner test',
            'slug' => 'banner-' . uniqid(),
            'template' => 'lumiere',
            'menu_type' => 1,
            'enabled' => true,
            'user_id' => $user->id,
        ]);

        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension not available.');
        }

        $im = imagecreatetruecolor(160, 90);
        $white = imagecolorallocate($im, 240, 240, 240);
        imagefilledrectangle($im, 0, 0, 159, 89, $white);
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'upload-banner.png';
        imagepng($im, $tmp);
        imagedestroy($im);

        $file = new UploadedFile($tmp, 'banner.png', 'image/png', null, true);

        $response = $this->actingAs($user)->postJson(
            route('admin.companies.storeheader', $company),
            ['background_header' => $file]
        );

        $response->assertOk();
        $response->assertJsonStructure(['success', 'url', 'overlay_mode', 'hero_ratio']);

        $company->refresh();
        $this->assertNotNull($company->header_luminance);
        $this->assertNotNull($company->header_overlay_mode);
        $this->assertNotNull($company->header_overlay_strength);

        @unlink($tmp);
    }

    public function test_header_crop_endpoint_validates_and_saves(): void
    {
        $user = User::factory()->create();
        $company = Company::create([
            'name' => 'Crop test',
            'slug' => 'crop-' . uniqid(),
            'template' => 'basic',
            'menu_type' => 1,
            'enabled' => true,
            'user_id' => $user->id,
            'background_header' => 'negocios/test.jpg',
        ]);

        $response = $this->actingAs($user)->patchJson(
            route('admin.companies.updateheadercrop', $company),
            ['x' => 0.1, 'y' => 0.2, 'w' => 0.6, 'h' => 0.5]
        );

        $response->assertOk();
        $response->assertJsonPath('success', true);

        $company->refresh();
        $this->assertSame(0.1, $company->header_crop['x']);
        $this->assertSame(0.5, $company->header_crop['h']);
    }

    private function createMenuCompany(string $template, bool $withLogo): Company
    {
        $user = User::factory()->create(['slug' => 'owner-' . uniqid()]);

        $company = Company::create([
            'name' => 'Hero test',
            'slug' => 'hero-' . uniqid(),
            'template' => $template,
            'menu_type' => 1,
            'enabled' => true,
            'user_id' => $user->id,
            'logo' => $withLogo ? 'negocios/test-logo.png' : null,
            'logo_chip_variant' => $withLogo ? 'light' : null,
        ]);

        $section = Section::create([
            'name' => 'Carta',
            'order' => 1,
            'enabled' => true,
            'company_id' => $company->id,
        ]);

        Product::create([
            'name' => 'Plato',
            'description' => 'Test',
            'price_unit' => '9,00',
            'individual_sale' => true,
            'order' => 1,
            'enabled' => true,
            'section_id' => $section->id,
        ]);

        return $company;
    }
}
