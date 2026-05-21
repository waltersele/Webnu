<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductVideoOptimizer
{
    /**
     * Guarda el vídeo en public/img/productos y lo comprime si FFmpeg está disponible.
     *
     * @return string Ruta relativa (ej. productos/abc.mp4)
     */
    public function storeOptimized(UploadedFile $file): string
    {
        $relative = $file->store('productos');

        return $this->optimizeRelativePath($relative);
    }

    public function optimizeRelativePath(string $relativePath): string
    {
        if (! config('product_media.ffmpeg_enabled', true)) {
            return $relativePath;
        }

        $fullPath = public_path('img/' . ltrim($relativePath, '/'));
        if (! is_file($fullPath)) {
            return $relativePath;
        }

        $ffmpeg = $this->resolveFfmpegBinary();
        if ($ffmpeg === null) {
            return $relativePath;
        }

        $maxSeconds = (int) config('product_media.max_video_seconds', 30);
        $maxHeight = (int) config('product_media.tv_max_height', 720);
        $maxWidth = (int) config('product_media.tv_max_width', 1280);
        $stripAudio = (bool) config('product_media.tv_strip_audio', true);
        $crf = (int) config('product_media.tv_crf', 28);

        $tempName = 'productos/' . Str::random(32) . '.mp4';
        $tempPath = public_path('img/' . $tempName);

        $dir = dirname($tempPath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $scale = sprintf(
            "scale='min(%d,iw)':min(%d,ih):force_original_aspect_ratio=decrease",
            $maxWidth,
            $maxHeight
        );

        $cmd = [
            $ffmpeg,
            '-y',
            '-i', $fullPath,
            '-t', (string) $maxSeconds,
            '-vf', $scale,
            '-c:v', 'libx264',
            '-profile:v', 'baseline',
            '-level', '3.1',
            '-preset', 'fast',
            '-crf', (string) $crf,
            '-movflags', '+faststart',
        ];

        if ($stripAudio) {
            $cmd[] = '-an';
        } else {
            $cmd = array_merge($cmd, ['-c:a', 'aac', '-b:a', '64k', '-ac', '1']);
        }

        $cmd[] = $tempPath;

        $result = $this->runProcess($cmd);
        if ($result !== 0 || ! is_file($tempPath)) {
            Log::info('ProductVideoOptimizer: FFmpeg no aplicado, se conserva el original.', [
                'path' => $relativePath,
                'exit' => $result,
            ]);
            @unlink($tempPath);

            return $relativePath;
        }

        $originalSize = filesize($fullPath) ?: 0;
        $newSize = filesize($tempPath) ?: 0;

        if ($newSize > 0 && ($originalSize === 0 || $newSize < $originalSize * 0.98)) {
            @unlink($fullPath);
            rename($tempPath, $fullPath);

            return $relativePath;
        }

        @unlink($tempPath);

        return $relativePath;
    }

    protected function resolveFfmpegBinary(): ?string
    {
        $configured = trim((string) config('product_media.ffmpeg_path', 'ffmpeg'));
        $candidates = array_filter(array_unique([
            $configured,
            'ffmpeg',
            'C:\\ffmpeg\\bin\\ffmpeg.exe',
            'C:\\laragon\\bin\\ffmpeg\\ffmpeg.exe',
        ]));

        foreach ($candidates as $bin) {
            if ($this->binaryWorks($bin)) {
                return $bin;
            }
        }

        return null;
    }

    protected function binaryWorks(string $bin): bool
    {
        $cmd = [$bin, '-version'];

        return $this->runProcess($cmd) === 0;
    }

    /**
     * @param  array<int, string>  $command
     */
    protected function runProcess(array $command): int
    {
        if (! function_exists('proc_open')) {
            return 1;
        }

        $descriptor = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = @proc_open($command, $descriptor, $pipes, null, null, ['bypass_shell' => true]);
        if (! is_resource($process)) {
            return 1;
        }

        fclose($pipes[0]);
        stream_get_contents($pipes[1]);
        stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($process);
    }
}
