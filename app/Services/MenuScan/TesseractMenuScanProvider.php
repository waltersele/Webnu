<?php

namespace App\Services\MenuScan;

use App\Contracts\MenuScanProvider;

class TesseractMenuScanProvider implements MenuScanProvider
{
    public function name(): string
    {
        return 'tesseract';
    }

    public function scan(array $absoluteFilePaths): MenuScanResult
    {
        if (count($absoluteFilePaths) === 0) {
            return MenuScanResult::failed('No hay archivos para analizar.', $this->name());
        }

        if (! $this->isAvailable()) {
            return MenuScanResult::failed(
                'Tesseract OCR no está instalado. Consulta docs/MENU-SCAN.md.',
                $this->name()
            );
        }

        $binary = config('menu_scan.tesseract.binary', 'tesseract');
        $lang = config('menu_scan.tesseract.lang', 'spa');
        $allText = '';

        foreach ($absoluteFilePaths as $path) {
            if (! is_readable($path)) {
                continue;
            }
            $mime = mime_content_type($path) ?: '';
            if ($mime === 'application/pdf') {
                $text = $this->ocrPdf($path, $binary, $lang);
            } elseif (strpos($mime, 'image/') === 0) {
                $text = $this->ocrImage($path, $binary, $lang);
            } else {
                continue;
            }
            if ($text !== '') {
                $allText .= $text . "\n";
            }
        }

        if (trim($allText) === '') {
            return MenuScanResult::failed('No se pudo extraer texto de las imágenes.', $this->name());
        }

        $sections = MenuLineParser::parse($allText);
        $result = MenuScanResult::fromSections($sections, $this->name(), true);
        $result->warnings[] = 'Resultado generado por OCR local; revisa nombres y precios con cuidado.';

        if (! $result->isSuccess()) {
            return MenuScanResult::failed('El OCR no detectó platos con precios claros.', $this->name());
        }

        return $result;
    }

    public function isAvailable(): bool
    {
        $binary = config('menu_scan.tesseract.binary', 'tesseract');
        if (PHP_OS_FAMILY === 'Windows') {
            $check = @shell_exec('where ' . escapeshellarg($binary) . ' 2>nul');

            return $check !== null && trim($check) !== '';
        }
        $check = @shell_exec('command -v ' . escapeshellarg($binary) . ' 2>/dev/null');

        return $check !== null && trim($check) !== '';
    }

    protected function ocrImage(string $path, string $binary, string $lang): string
    {
        $cmd = sprintf(
            '%s %s stdout -l %s 2>/dev/null',
            escapeshellcmd($binary),
            escapeshellarg($path),
            escapeshellarg($lang)
        );
        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = sprintf(
                '%s %s stdout -l %s 2>nul',
                escapeshellcmd($binary),
                escapeshellarg($path),
                escapeshellarg($lang)
            );
        }

        return trim((string) @shell_exec($cmd));
    }

    protected function ocrPdf(string $path, string $binary, string $lang): string
    {
        if (! class_exists(\Imagick::class)) {
            return '';
        }

        try {
            $imagick = new \Imagick();
            $imagick->setResolution(150, 150);
            $imagick->readImage($path);
            $text = '';
            foreach ($imagick as $page) {
                $page->setImageFormat('png');
                $tmp = tempnam(sys_get_temp_dir(), 'wnscan');
                if ($tmp === false) {
                    continue;
                }
                $png = $tmp . '.png';
                $page->writeImage($png);
                $text .= $this->ocrImage($png, $binary, $lang) . "\n";
                @unlink($png);
                @unlink($tmp);
            }
            $imagick->clear();

            return $text;
        } catch (\Throwable $e) {
            return '';
        }
    }
}
