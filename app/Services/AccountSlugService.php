<?php

namespace App\Services;

use App\Company;
use App\User;
use Illuminate\Support\Str;

class AccountSlugService
{
    /** @var array<int, string> */
    public const RESERVED = CompanySlugService::RESERVED;

    public function normalize(string $value): string
    {
        $slug = Str::slug($value);

        return $slug !== '' ? $slug : 'negocio';
    }

    public function isReserved(string $slug): bool
    {
        return in_array($slug, self::RESERVED, true);
    }

    public function isAvailable(string $slug, ?int $exceptUserId = null): bool
    {
        if ($this->isReserved($slug)) {
            return false;
        }

        $userQuery = User::where('slug', $slug);
        if ($exceptUserId) {
            $userQuery->where('id', '!=', $exceptUserId);
        }
        if ($userQuery->exists()) {
            return false;
        }

        if (Company::where('slug', $slug)->exists()) {
            return false;
        }

        return true;
    }

    public function validateAccountSlug(string $slug, ?int $exceptUserId = null): ?string
    {
        $slug = $this->normalize($slug);

        if (strlen($slug) < 2 || strlen($slug) > 110) {
            return 'La URL del negocio debe tener entre 2 y 110 caracteres.';
        }

        if (! preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return 'Usa solo letras minúsculas, números y guiones.';
        }

        if ($this->isReserved($slug)) {
            return 'Esa URL está reservada. Elige otra.';
        }

        if (! $this->isAvailable($slug, $exceptUserId)) {
            return 'Esa URL ya está en uso. Prueba con otra variante.';
        }

        return null;
    }

    public function generateUnique(string $source, ?int $exceptUserId = null): string
    {
        $base = $this->normalize($source);
        $candidate = $base;
        $i = 2;

        while (! $this->isAvailable($candidate, $exceptUserId)) {
            $candidate = $base . '-' . $i++;
            if ($i > 999) {
                $candidate = $base . '-' . substr(uniqid(), -5);
                break;
            }
        }

        return $candidate;
    }

    public function isPendingPlaceholder(?string $slug): bool
    {
        return $slug !== null && $slug !== '' && preg_match('/^cuenta-pendiente-[a-z0-9-]+$/', $slug) === 1;
    }
}
