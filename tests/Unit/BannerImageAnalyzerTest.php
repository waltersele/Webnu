<?php

namespace Tests\Unit;

use App\Services\BannerImageAnalyzer;
use Tests\TestCase;

class BannerImageAnalyzerTest extends TestCase
{
    /** @var BannerImageAnalyzer */
    private $analyzer;

    /** @var string */
    private $tmpDir;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('imagecreatetruecolor')) {
            $this->markTestSkipped('GD extension not available.');
        }

        $this->analyzer = new BannerImageAnalyzer();
        $this->tmpDir = sys_get_temp_dir();
    }

    public function test_bright_image_uses_dark_overlay_and_light_text(): void
    {
        $path = $this->createSolidImage(255, 255, 255);

        $result = $this->analyzer->analyze($path);

        $this->assertSame('dark', $result['overlay_mode']);
        $this->assertSame('light', $result['text_tone']);
        $this->assertGreaterThan(0.55, $result['overlay_strength']);
        @unlink($path);
    }

    public function test_dark_image_uses_light_overlay_and_dark_text(): void
    {
        $path = $this->createSolidImage(20, 20, 20);

        $result = $this->analyzer->analyze($path);

        $this->assertSame('light', $result['overlay_mode']);
        $this->assertSame('dark', $result['text_tone']);
        @unlink($path);
    }

    public function test_crop_region_limits_analysis(): void
    {
        $path = $this->createSplitImage();

        $full = $this->analyzer->analyze($path);
        $cropped = $this->analyzer->analyze($path, ['x' => 0, 'y' => 0, 'w' => 0.5, 'h' => 1]);

        $this->assertNotEquals($full['luminance'], $cropped['luminance']);
        $this->assertGreaterThan($full['luminance'], $cropped['luminance']);
        @unlink($path);
    }

    private function createSolidImage(int $r, int $g, int $b): string
    {
        $im = imagecreatetruecolor(120, 80);
        $color = imagecolorallocate($im, $r, $g, $b);
        imagefilledrectangle($im, 0, 0, 119, 79, $color);
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'banner-test-' . uniqid('', true) . '.png';
        imagepng($im, $path);
        imagedestroy($im);

        return $path;
    }

    private function createSplitImage(): string
    {
        $im = imagecreatetruecolor(200, 100);
        $white = imagecolorallocate($im, 255, 255, 255);
        $black = imagecolorallocate($im, 10, 10, 10);
        imagefilledrectangle($im, 0, 0, 99, 99, $white);
        imagefilledrectangle($im, 100, 0, 199, 99, $black);
        $path = $this->tmpDir . DIRECTORY_SEPARATOR . 'banner-split-' . uniqid('', true) . '.png';
        imagepng($im, $path);
        imagedestroy($im);

        return $path;
    }
}
