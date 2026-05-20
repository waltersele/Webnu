<?php

namespace App\Services;

use App\Company;
use Illuminate\Support\Str;

class CompanySlugService
{
    /** @var array<int, string> */
    protected const RESERVED = [
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

        return ! $query->exists();
    }

    /**
     * Genera una URL limpia única a partir del nombre (y opcionalmente la ciudad).
     */
    public function generateFromName(string $name, ?string $city = null, ?int $exceptCompanyId = null): string
    {
        $base = $this->normalize($name);
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

    public function publicPath(string $slug): string
    {
        return '/carta/' . $slug;
    }
}
