<?php

namespace App\Services\Tv;

class TvTemplateRegistry
{
    public function defaultLayout(): string
    {
        return (string) config('tvpik_templates.default', 'menu');
    }

    public function allowedLayouts(): array
    {
        return config('tvpik_templates.layouts', ['menu']);
    }

    public function templates(): array
    {
        return config('tvpik_templates.templates', []);
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
