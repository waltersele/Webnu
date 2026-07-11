<?php

namespace App\Services;

use App\BlogPost;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BlogFeaturedImageService
{
    /**
     * @param array<string, mixed> $payload
     */
    public function applyFromPayload(BlogPost $post, array $payload): void
    {
        $alt = $this->stringOrNull($payload['featured_image_alt'] ?? null);
        $path = null;

        $url = $this->stringOrNull($payload['featured_image_url'] ?? null);
        if ($url !== null) {
            $path = $this->storeFromUrl($url, $post->id);
        } elseif ($this->stringOrNull($payload['featured_image_base64'] ?? null) !== null) {
            $path = $this->storeFromBase64(
                (string) $payload['featured_image_base64'],
                (string) $payload['featured_image_mime'],
                $post->id
            );
        }

        if ($path === null && $alt === null) {
            return;
        }

        if ($path !== null) {
            $post->featured_image = $path;
        }

        if ($alt !== null) {
            $post->featured_image_alt = $alt;
        }

        $post->save();
    }

    public function applyFromUpload(BlogPost $post, UploadedFile $file, ?string $alt = null): void
    {
        $mime = (string) $file->getMimeType();
        $this->assertAllowedMime($mime);

        $bytes = file_get_contents($file->getRealPath());
        if ($bytes === false) {
            throw ValidationException::withMessages([
                'featured_image' => ['No se pudo leer la imagen subida.'],
            ]);
        }

        $this->assertMaxSize(strlen($bytes));
        $post->featured_image = $this->writeImage($bytes, $mime, $post->id);

        if ($alt !== null) {
            $alt = trim($alt);
            $post->featured_image_alt = $alt !== '' ? $alt : null;
        }

        $post->save();
    }

    public function clearFeaturedImage(BlogPost $post): void
    {
        $path = trim((string) $post->featured_image);
        if ($path !== '' && ! filter_var($path, FILTER_VALIDATE_URL)) {
            $fullPath = public_path(ltrim($path, '/'));
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
        }

        $post->featured_image = null;
        $post->featured_image_alt = null;
        $post->save();
    }

    private function storeFromUrl(string $url, int $postId): string
    {
        $response = Http::timeout(20)
            ->withHeaders(['User-Agent' => 'WebnuContentConnector/1.0'])
            ->get($url);

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'featured_image_url' => ['No se pudo descargar featured_image_url.'],
            ]);
        }

        $mime = $response->header('Content-Type');
        $mime = is_string($mime) ? trim(explode(';', $mime)[0]) : '';
        $this->assertAllowedMime($mime);

        $bytes = $response->body();
        $this->assertMaxSize(strlen($bytes));

        return $this->writeImage($bytes, $mime, $postId);
    }

    private function storeFromBase64(string $base64, string $mime, int $postId): string
    {
        $this->assertAllowedMime($mime);

        $decoded = base64_decode($base64, true);
        if ($decoded === false) {
            throw ValidationException::withMessages([
                'featured_image_base64' => ['featured_image_base64 no es base64 válido.'],
            ]);
        }

        $this->assertMaxSize(strlen($decoded));

        return $this->writeImage($decoded, $mime, $postId);
    }

    private function writeImage(string $bytes, string $mime, int $postId): string
    {
        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'jpg',
        };

        $dir = trim((string) config('blog.featured_image.storage_dir', 'img/blog'), '/');
        $filename = 'post-' . $postId . '-' . Str::random(8) . '.' . $extension;
        $relativePath = $dir . '/' . $filename;

        $fullPath = public_path($relativePath);
        $directory = dirname($fullPath);
        if (! is_dir($directory) && ! mkdir($directory, 0755, true) && ! is_dir($directory)) {
            throw ValidationException::withMessages([
                'featured_image_url' => ['No se pudo crear el directorio de imágenes del blog.'],
            ]);
        }

        if (file_put_contents($fullPath, $bytes) === false) {
            throw ValidationException::withMessages([
                'featured_image_url' => ['No se pudo guardar la imagen destacada.'],
            ]);
        }

        return $relativePath;
    }

    private function assertAllowedMime(string $mime): void
    {
        $allowed = config('blog.featured_image.allowed_mimes', []);
        if (! in_array($mime, $allowed, true)) {
            throw ValidationException::withMessages([
                'featured_image_mime' => ['Tipo MIME de imagen no permitido.'],
            ]);
        }
    }

    private function assertMaxSize(int $size): void
    {
        $max = (int) config('blog.featured_image.max_bytes', 5 * 1024 * 1024);
        if ($size > $max) {
            throw ValidationException::withMessages([
                'featured_image_url' => ['La imagen destacada supera el tamaño máximo permitido.'],
            ]);
        }
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
