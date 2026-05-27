<?php

namespace App\Services;

/**
 * Analyses a logo image with PHP-GD to estimate the best background variant
 * for the logo "chip" that wraps it in the public menu hero.
 *
 * The analyser samples the image on a grid (capped at ~2.500 points) and
 * computes:
 *   - luminance: weighted average in [0,1] using Rec. 709 coefficients.
 *   - has_solid_bg: true when very few sampled pixels are (semi)transparent,
 *     meaning the logo already provides its own background and we should not
 *     add another one behind it.
 *   - dominant_hex: average colour of the non transparent pixels as #rrggbb.
 *   - chip_variant: one of 'light' | 'dark' | 'glass'. 'light' (white bg) for
 *     dark logos, 'dark' (dark translucent bg) for light/white logos, 'glass'
 *     for mid luminance logos or logos that already carry their own bg.
 */
class LogoColorAnalyzer
{
    /** Maximum number of samples to read from the image. */
    public const MAX_SAMPLES = 2500;

    /**
     * @param string $absolutePath Absolute path to a readable image file.
     *
     * @return array{
     *     luminance: float|null,
     *     has_solid_bg: bool|null,
     *     dominant_hex: string|null,
     *     chip_variant: string
     * }
     */
    public function analyze(string $absolutePath): array
    {
        $fallback = [
            'luminance'    => null,
            'has_solid_bg' => null,
            'dominant_hex' => null,
            'chip_variant' => 'glass',
        ];

        if (! function_exists('imagecreatefromstring') || ! is_readable($absolutePath)) {
            return $fallback;
        }

        $binary = @file_get_contents($absolutePath);
        if ($binary === false || $binary === '') {
            return $fallback;
        }

        $im = @imagecreatefromstring($binary);
        if ($im === false) {
            return $fallback;
        }

        @imagealphablending($im, false);
        @imagesavealpha($im, true);

        $w = imagesx($im);
        $h = imagesy($im);

        if ($w <= 0 || $h <= 0) {
            imagedestroy($im);
            return $fallback;
        }

        $totalPixels  = $w * $h;
        $targetSamples = min(self::MAX_SAMPLES, max(64, $totalPixels));
        $step = max(1, (int) floor(sqrt($totalPixels / $targetSamples)));

        $lumSum        = 0.0;
        $lumCount      = 0;
        $rSum          = 0;
        $gSum          = 0;
        $bSum          = 0;
        $opaqueCount   = 0;
        $transparentCount = 0;
        $sampledCount  = 0;

        for ($y = 0; $y < $h; $y += $step) {
            for ($x = 0; $x < $w; $x += $step) {
                $rgba = imagecolorat($im, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                $sampledCount++;

                if ($alpha >= 96) {
                    $transparentCount++;
                    continue;
                }

                $opaqueCount++;
                $rSum += $r;
                $gSum += $g;
                $bSum += $b;

                $lumSum += (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255.0;
                $lumCount++;
            }
        }

        imagedestroy($im);

        if ($lumCount === 0 || $sampledCount === 0) {
            return $fallback;
        }

        $luminance = $lumSum / $lumCount;
        $luminance = round(max(0.0, min(1.0, $luminance)), 3);

        $transparencyRatio = $transparentCount / max(1, $sampledCount);
        $hasSolidBg = $transparencyRatio < 0.05;

        $rAvg = (int) round($rSum / $opaqueCount);
        $gAvg = (int) round($gSum / $opaqueCount);
        $bAvg = (int) round($bSum / $opaqueCount);
        $dominantHex = sprintf('#%02x%02x%02x', $rAvg, $gAvg, $bAvg);

        $chipVariant = $this->resolveVariant($luminance, $hasSolidBg);

        return [
            'luminance'    => $luminance,
            'has_solid_bg' => $hasSolidBg,
            'dominant_hex' => $dominantHex,
            'chip_variant' => $chipVariant,
        ];
    }

    /**
     * Decide the chip background variant from luminance + solid-bg presence.
     *
     *  - Logo with its own solid bg -> 'glass' to keep it neutral and avoid a
     *    visible square behind it.
     *  - Dark logo (luminance < 0.45) -> 'light' chip (whiteish background).
     *  - Bright/white logo (luminance > 0.70) -> 'dark' chip (dim background).
     *  - Anything in between -> 'glass'.
     */
    protected function resolveVariant(float $luminance, bool $hasSolidBg): string
    {
        if ($hasSolidBg) {
            return 'glass';
        }

        if ($luminance < 0.45) {
            return 'light';
        }

        if ($luminance > 0.70) {
            return 'dark';
        }

        return 'glass';
    }
}
