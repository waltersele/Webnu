<?php

namespace App\Services\PreAlta;

use App\MenuPreRegistration;
use Illuminate\Support\Str;

class PreAltaIdentityService
{
    public function generatePublicSlug(): string
    {
        do {
            $slug = 'pa-' . Str::lower(Str::random(12));
        } while (MenuPreRegistration::where('public_slug', $slug)->exists());

        return $slug;
    }

    /**
     * @return array{plain: string, hash: string}
     */
    public function generateClaimToken(): array
    {
        $plain = bin2hex(random_bytes(32));

        return [
            'plain' => $plain,
            'hash' => MenuPreRegistration::hashClaimToken($plain),
        ];
    }
}
