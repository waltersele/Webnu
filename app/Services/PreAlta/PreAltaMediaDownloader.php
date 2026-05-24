<?php

namespace App\Services\PreAlta;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PreAltaMediaDownloader
{
    /**
     * @param array<int, array<string, mixed>> $sections
     * @return array{sections: array, manifest: array<string, string>}
     */
    public function process(int $registrationId, array $sections, ?string $logoUrl): array
    {
        $disk = Storage::disk('pre_alta');
        $baseDir = (string) $registrationId;
        $manifest = [];

        if ($logoUrl) {
            $saved = $this->downloadTo($disk, $baseDir . '/negocios', 'logo', $logoUrl);
            if ($saved) {
                $manifest['logo'] = $saved;
            }
        }

        foreach ($sections as $si => $section) {
            foreach ($section['products'] ?? [] as $pi => $product) {
                $url = $product['image_url'] ?? null;
                if (! $url) {
                    continue;
                }
                $key = "s{$si}_p{$pi}";
                $saved = $this->downloadTo($disk, $baseDir . '/productos', $key, $url);
                if ($saved) {
                    $manifest[$key] = $saved;
                    $sections[$si]['products'][$pi]['_staging_image'] = $saved;
                }
                unset($sections[$si]['products'][$pi]['image_url']);
            }
        }

        return [
            'sections' => $sections,
            'manifest' => $manifest,
        ];
    }

    protected function downloadTo($disk, string $directory, string $basename, string $url): ?string
    {
        try {
            $response = Http::timeout((int) config('pre_alta.image_download_timeout', 30))
                ->withHeaders(['User-Agent' => 'Webnu-PreAlta/1.0'])
                ->get($url);

            if (! $response->successful()) {
                Log::warning('Pre-Alta: descarga HTTP fallida', ['url' => $url, 'status' => $response->status()]);

                return null;
            }

            $body = $response->body();
            $maxBytes = (int) config('pre_alta.max_image_bytes', 8 * 1024 * 1024);
            if (strlen($body) > $maxBytes) {
                Log::warning('Pre-Alta: imagen demasiado grande', ['url' => $url]);

                return null;
            }

            $contentType = strtolower((string) $response->header('Content-Type', 'image/jpeg'));
            $contentType = strtok($contentType, ';') ?: 'image/jpeg';
            $allowed = config('pre_alta.allowed_image_mimes', []);
            if ($allowed && ! in_array($contentType, $allowed, true)) {
                Log::warning('Pre-Alta: MIME no permitido', ['url' => $url, 'mime' => $contentType]);

                return null;
            }

            $extension = $this->extensionForMime($contentType);
            $filename = Str::slug($basename) . '-' . Str::random(8) . '.' . $extension;
            $relativePath = trim($directory, '/') . '/' . $filename;

            $disk->put($relativePath, $body);

            return $relativePath;
        } catch (\Throwable $e) {
            Log::warning('Pre-Alta: error descargando imagen', [
                'url' => $url,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    protected function extensionForMime(string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        return $map[$mime] ?? 'jpg';
    }

    public function absolutePath(string $relativePath): string
    {
        return Storage::disk('pre_alta')->path($relativePath);
    }
}
