<?php

namespace App\Services;

use App\Company;
use App\User;
use Illuminate\Support\Str;

class CompanySlugService
{
    /** @var array<int, string> */
    public const RESERVED = [
        'admin',
        'api',
        'billing',
        'carta',
        'demo',
        'landing',
        'login',
        'platform',
        'preview',
        'register',
        'activar',
        'pre-alta',
        'webnu',
        'www',
    ];

    public function normalize(string $value): string
    {
        $slug = Str::slug($value);

        return $slug !== '' ? $slug : 'restaurante';
    }

    public function isReserved(string $slug): bool
    {
        return in_array($slug, self::RESERVED, true);
    }

    public function isAvailable(string $slug, ?int $exceptCompanyId = null): bool
    {
        if ($this->isReserved($slug)) {
            return false;
        }

        $query = Company::where('slug', $slug);
        if ($exceptCompanyId) {
            $query->where('id', '!=', $exceptCompanyId);
        }

        if ($query->exists()) {
            return false;
        }

        if (User::where('slug', $slug)->exists()) {
            return false;
        }

        return true;
    }

    /** Slug temporal hasta que el usuario elija la URL en onboarding. */
    public function generatePlaceholderSlug(?int $exceptCompanyId = null): string
    {
        do {
            $slug = 'pendiente-' . strtolower(Str::random(10));
        } while (! $this->isAvailable($slug, $exceptCompanyId));

        return $slug;
    }

    public function isPlaceholderSlug(?string $slug): bool
    {
        return $slug !== null && $slug !== '' && preg_match('/^pendiente-[a-z0-9-]+$/', $slug) === 1;
    }

    public function isAutoCartaSlug(?string $slug): bool
    {
        return $slug !== null && preg_match('/^carta(-\d+)?$/', $slug) === 1;
    }

    /**
     * Genera una URL limpia única a partir del nombre (y opcionalmente la ciudad).
     * Si se pasa $ownerSlug y el slug calculado coincide con el del propietario,
     * se prefiere "carta" (o variantes) para evitar URLs feas tipo
     * /carta/maria-garcia/maria-garcia.
     */
    public function generateFromName(string $name, ?string $city = null, ?int $exceptCompanyId = null, ?string $ownerSlug = null): string
    {
        $base = $this->normalize($name);

        // Evitar duplicar el slug del propietario: si coinciden, partimos de "carta".
        if ($ownerSlug !== null && $ownerSlug !== '' && $base === $ownerSlug) {
            $base = 'carta';
        }

        $candidates = [$base];

        if ($city) {
            $citySlug = Str::slug($city);
            if ($citySlug !== '' && $citySlug !== $base) {
                $candidates[] = $base . '-' . $citySlug;
            }
        }

        foreach ($candidates as $candidate) {
            if ($this->isAvailable($candidate, $exceptCompanyId)) {
                return $candidate;
            }
        }

        for ($i = 2; $i <= 999; $i++) {
            $candidate = $base . '-' . $i;
            if ($this->isAvailable($candidate, $exceptCompanyId)) {
                return $candidate;
            }
        }

        return $base . '-' . substr(uniqid(), -5);
    }

    /**
     * Valida un slug personalizado. Devuelve mensaje de error o null si es válido.
     */
    public function validateCustomSlug(string $slug, ?int $exceptCompanyId = null): ?string
    {
        $slug = $this->normalize($slug);

        if (strlen($slug) < 2 || strlen($slug) > 64) {
            return 'La URL debe tener entre 2 y 64 caracteres.';
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return 'Usa solo letras minúsculas, números y guiones.';
        }

        if ($this->isReserved($slug)) {
            return 'Esa URL está reservada. Elige otra.';
        }

        if (! $this->isAvailable($slug, $exceptCompanyId)) {
            return 'Esa URL ya está en uso. Prueba con otra variante.';
        }

        return null;
    }

    public function publicPath(string $slug, ?string $ownerSlug = null): string
    {
        if ($ownerSlug !== null && $ownerSlug !== '') {
            return '/carta/' . $ownerSlug . '/' . $slug;
        }
        return '/carta/' . $slug;
    }
}
