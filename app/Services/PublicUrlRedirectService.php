<?php

namespace App\Services;

use App\PublicSlugRedirect;
use Illuminate\Http\Request;

class PublicUrlRedirectService
{
    public function resolveFromRequest(Request $request): ?string
    {
        $path = PublicSlugRedirect::normalizePath($request->path());

        return $this->resolvePath($path);
    }

    public function resolvePath(string $path): ?string
    {
        $path = PublicSlugRedirect::normalizePath($path);

        $redirect = PublicSlugRedirect::where('from_path', $path)->first();
        if (! $redirect) {
            return null;
        }

        $target = '/' . ltrim($redirect->to_path, '/');
        $qs = request()->getQueryString();

        return $target . ($qs ? '?' . $qs : '');
    }

    public function record(
        string $fromPath,
        string $toPath,
        ?int $companyId = null,
        ?int $userId = null,
        ?int $menuId = null,
        int $httpStatus = 301
    ): PublicSlugRedirect {
        $from = PublicSlugRedirect::normalizePath($fromPath);
        $to = PublicSlugRedirect::normalizePath($toPath);

        return PublicSlugRedirect::updateOrCreate(
            ['from_path' => $from],
            [
                'to_path' => $to,
                'company_id' => $companyId,
                'user_id' => $userId,
                'menu_id' => $menuId,
                'http_status' => $httpStatus,
            ]
        );
    }
}
