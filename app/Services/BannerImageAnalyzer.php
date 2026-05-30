<?php

namespace App\Services;

/**
 * Analyses a banner/header image to derive overlay strength and text tone
 * for the public menu hero. Supports optional normalized crop regions.
 */
class BannerImageAnalyzer
{
    public const MAX_SAMPLES = 2500;

    /**
     * @param  array{x?:float,y?:float,w?:float,h?:float}|null  $crop  Normalized 0–1 region
     *
     * @return array{
     *     luminance: float|null,
     *     overlay_mode: string,
     *     overlay_strength: float,
     *     dominant_hex: string|null,
     *     text_tone: string
     * }
     */
    public function analyze(string $absolutePath, ?array $crop = null): array
    {
        $fallback = [
            'luminance'         => null,
            'overlay_mode'      => 'dark',
            'overlay_strength'  => 0.72,
            'dominant_hex'      => null,
            'text_tone'         => 'light',
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

        [$x0, $y0, $x1, $y1] = $this->resolveBounds($w, $h, $crop);

        $regionW = max(1, $x1 - $x0);
        $regionH = max(1, $y1 - $y0);
        $totalPixels = $regionW * $regionH;
        $targetSamples = min(self::MAX_SAMPLES, max(64, $totalPixels));
        $step = max(1, (int) floor(sqrt($totalPixels / $targetSamples)));

        $lumSum = 0.0;
        $lumCount = 0;
        $rSum = 0;
        $gSum = 0;
        $bSum = 0;
        $opaqueCount = 0;

        for ($y = $y0; $y < $y1; $y += $step) {
            for ($x = $x0; $x < $x1; $x += $step) {
                $rgba = imagecolorat($im, $x, $y);
                $alpha = ($rgba >> 24) & 0x7F;
                $r = ($rgba >> 16) & 0xFF;
                $g = ($rgba >> 8) & 0xFF;
                $b = $rgba & 0xFF;

                if ($alpha >= 96) {
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

        if ($lumCount === 0) {
            return $fallback;
        }

        $luminance = round(max(0.0, min(1.0, $lumSum / $lumCount)), 3);
        $rAvg = (int) round($rSum / $opaqueCount);
        $gAvg = (int) round($gSum / $opaqueCount);
        $bAvg = (int) round($bSum / $opaqueCount);
        $dominantHex = sprintf('#%02x%02x%02x', $rAvg, $gAvg, $bAvg);

        $overlay = $this->resolveOverlay($luminance);

        return [
            'luminance'        => $luminance,
            'overlay_mode'     => $overlay['mode'],
            'overlay_strength' => $overlay['strength'],
            'dominant_hex'     => $dominantHex,
            'text_tone'        => $overlay['text_tone'],
        ];
    }

    /**
     * @param  array{x?:float,y?:float,w?:float,h?:float}|null  $crop
     *
     * @return array{0:int,1:int,2:int,3:int}
     */
    protected function resolveBounds(int $w, int $h, ?array $crop): array
    {
        if ($crop === null || ! isset($crop['w'], $crop['h'])) {
            return [0, 0, $w, $h];
        }

        $x = max(0.0, min(1.0, (float) ($crop['x'] ?? 0)));
        $y = max(0.0, min(1.0, (float) ($crop['y'] ?? 0)));
        $cw = max(0.05, min(1.0, (float) $crop['w']));
        $ch = max(0.05, min(1.0, (float) $crop['h']));

        $x0 = (int) floor($x * $w);
        $y0 = (int) floor($y * $h);
        $x1 = (int) min($w, ceil(($x + $cw) * $w));
        $y1 = (int) min($h, ceil(($y + $ch) * $h));

        if ($x1 <= $x0) {
            $x1 = min($w, $x0 + 1);
        }
        if ($y1 <= $y0) {
            $y1 = min($h, $y0 + 1);
        }

        return [$x0, $y0, $x1, $y1];
    }

    /**
     * @return array{mode:string,strength:float,text_tone:string}
     */
    protected function resolveOverlay(float $luminance): array
    {
        if ($luminance >= 0.58) {
            return [
                'mode'       => 'dark',
                'strength'   => round(min(0.92, 0.55 + ($luminance - 0.58) * 0.85), 3),
                'text_tone'  => 'light',
            ];
        }

        if ($luminance <= 0.42) {
            return [
                'mode'       => 'light',
                'strength'   => round(min(0.88, 0.50 + (0.42 - $luminance) * 0.75), 3),
                'text_tone'  => 'dark',
            ];
        }

        return [
            'mode'       => 'dark',
            'strength'   => 0.68,
            'text_tone'  => 'light',
        ];
    }
}
