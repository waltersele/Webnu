<?php

namespace App\Http\Controllers;

use App\MenuPreRegistration;
use App\Services\PreAlta\PreAltaMediaDownloader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class PreAltaPreviewController extends Controller
{
    public function show(string $slug)
    {
        $registration = MenuPreRegistration::pending()
            ->where('public_slug', $slug)
            ->first();

        if (! $registration || $registration->isExpired()) {
            abort(404);
        }

        $sections = $registration->menu_json['sections'] ?? [];
        $manifest = $registration->media_manifest ?? [];

        return view('pre-alta.preview', [
            'registration' => $registration,
            'sections' => $sections,
            'manifest' => $manifest,
            'mediaBaseUrl' => url('/pre-alta/media/' . $registration->id),
        ]);
    }

    public function media(int $id, Request $request, PreAltaMediaDownloader $mediaDownloader)
    {
        $registration = MenuPreRegistration::pending()->find($id);
        if (! $registration || $registration->isExpired()) {
            abort(404);
        }

        $path = $request->query('path', '');
        $path = str_replace('\\', '/', $path);
        if ($path === '' || strpos($path, '..') !== false || strpos($path, '/') === 0) {
            abort(400);
        }

        $manifest = $registration->media_manifest ?? [];
        $allowed = in_array($path, $manifest, true);
        if (! $allowed) {
            abort(404);
        }

        $absolute = $mediaDownloader->absolutePath($path);
        if (! is_file($absolute)) {
            abort(404);
        }

        $mime = mime_content_type($absolute) ?: 'image/jpeg';

        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
        ]);
    }
}
