<?php

namespace App\Services;

/**
 * Cat�logo de plantillas QR: galer�a nueva vs legacy-en-uso vs soft-retire.
 * No borra claves; filtra qu� se ofrece a altas nuevas.
 */
class CompanyTemplatesCatalog
{
    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        $templates = config('company_templates.templates', []);

        return is_array($templates) ? $templates : [];
    }

    public function meta(string $key): ?array
    {
        $all = $this->all();

        return isset($all[$key]) && is_array($all[$key]) ? $all[$key] : null;
    }

    public function isInGallery(string $key): bool
    {
        $meta = $this->meta($key);

        return is_array($meta) && ($meta['gallery'] ?? true);
    }

    public function isLegacy(string $key): bool
    {
        $meta = $this->meta($key);

        return is_array($meta) && ! empty($meta['legacy']);
    }

    /**
     * Plantillas visibles en galer�a de selecci�n.
     * Incluye siempre $currentKey para que un negocio legacy siga viendo la suya.
     *
     * @return array<string, array<string, mixed>>
     */
    public function forGallery(?string $currentKey = null): array
    {
        $out = [];
        foreach ($this->all() as $key => $meta) {
            if (! is_array($meta) || empty($meta['label'])) {
                continue;
            }
            $inGallery = (bool) ($meta['gallery'] ?? true);
            if ($inGallery || ($currentKey !== null && $key === $currentKey)) {
                $out[$key] = $meta;
            }
        }

        return $out;
    }

    /**
     * Solo galer�a nueva (onboarding / altas), sin forzar legacy.
     *
     * @return array<string, array<string, mixed>>
     */
    public function forOnboarding(): array
    {
        return $this->forGallery(null);
    }

    /** @return list<string> */
    public function galleryKeys(): array
    {
        return array_keys($this->forOnboarding());
    }

    /**
     * Orientaciones TV del pack (landscape / portrait).
     *
     * @return list<string>
     */
    public function tvOrientations(string $templateKey): array
    {
        $meta = $this->meta($templateKey);
        $orientations = $meta['tv_orientations'] ?? ['landscape', 'portrait'];

        return array_values(array_filter($orientations, 'is_string'));
    }

    public function tvSkinKey(string $templateKey): string
    {
        $meta = $this->meta($templateKey);

        return is_array($meta) && ! empty($meta['tv_skin'])
            ? (string) $meta['tv_skin']
            : $templateKey;
    }
}
