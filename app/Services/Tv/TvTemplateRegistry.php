<?php

namespace App\Services\Tv;

class TvTemplateRegistry
{
    public function defaultLayout(): string
    {
        return (string) config('tvpik_templates.default', 'carta');
    }

    public function allowedLayouts(): array
    {
        return config('tvpik_templates.layouts', ['carta', 'menu']);
    }

    public function templates(): array
    {
        return config('tvpik_templates.templates', []);
    }

    /**
     * Plantillas visibles en el picker admin (pack modes + galería).
     *
     * @return array<string, array<string, mixed>>
     */
    public function galleryTemplates(): array
    {
        $out = [];
        foreach ($this->templates() as $key => $tpl) {
            if (! is_array($tpl)) {
                continue;
            }
            $inGallery = array_key_exists('gallery', $tpl) ? (bool) $tpl['gallery'] : true;
            if ($inGallery || ! empty($tpl['pack_mode'])) {
                $out[$key] = $tpl;
            }
        }

        uasort($out, function ($a, $b) {
            $ap = ! empty($a['pack_mode']) ? 0 : 1;
            $bp = ! empty($b['pack_mode']) ? 0 : 1;

            return $ap <=> $bp;
        });

        return $out;
    }

    public function template(string $key): ?array
    {
        $templates = $this->templates();

        return $templates[$key] ?? null;
    }

    public function resolveLayout(?string $layout): string
    {
        $layout = $layout ?: $this->defaultLayout();
        $allowed = $this->allowedLayouts();

        if (! in_array($layout, $allowed, true)) {
            return $this->defaultLayout();
        }

        return $layout;
    }

    public function resolveOrientation(?string $orientation): string
    {
        $orientation = $orientation ?: (string) config('tvpik_templates.default_orientation', 'landscape');
        $allowed = config('tvpik_templates.orientations', ['landscape', 'portrait']);

        if (! in_array($orientation, $allowed, true)) {
            return (string) config('tvpik_templates.default_orientation', 'landscape');
        }

        return $orientation;
    }

    public function viewForLayout(string $layout): string
    {
        $template = $this->templateByLayout($layout);

        if ($template && ! empty($template['view'])) {
            return $template['view'];
        }

        return 'tv.templates.' . $layout;
    }

    public function templateByLayout(string $layout): ?array
    {
        foreach ($this->templates() as $template) {
            if (($template['layout'] ?? null) === $layout) {
                return $template;
            }
        }

        return $this->template($layout);
    }

    public function rotateSeconds(string $layout): int
    {
        $template = $this->templateByLayout($layout);

        return max(4, (int) ($template['rotate_seconds'] ?? 12));
    }

    public function showHeader(string $layout): bool
    {
        $template = $this->templateByLayout($layout);

        return (bool) ($template['show_header'] ?? true);
    }
}
