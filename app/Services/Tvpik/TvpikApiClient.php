<?php

namespace App\Services\Tvpik;

use App\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TvpikApiClient
{
    public function isConfigured(): bool
    {
        return config('tvpik.api_url') !== '';
    }

    /**
     * @return array{screens: array<int, array>, raw: mixed}
     */
    /**
     * @return array<string, mixed>
     */
    public function bootstrap(User $user): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('TVPik API no configurada.');
        }

        $response = Http::timeout(config('tvpik.timeout', 15))
            ->acceptJson()
            ->withHeaders($this->bootstrapHeaders($user))
            ->post(config('tvpik.api_url') . $this->path('bootstrap'), [
                'base_url' => rtrim(config('app.url'), '/'),
                'app_key' => config('tvpik.app_key'),
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo activar Pantallas TVPik: ' . $response->body());
        }

        return $response->json() ?? [];
    }

    /**
     * @return array<string, mixed>
     */
    public function createScreen(User $user, string $name): array
    {
        if (! $this->isConfigured()) {
            return $this->stubCreateScreen($name);
        }

        $response = $this->request($user)->post($this->path('screens'), [
            'name' => $name,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo crear la pantalla: ' . $response->body());
        }

        $data = $response->json();

        return $data['screen'] ?? $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function updateScreen(User $user, string $screenId, string $name): array
    {
        if (! $this->isConfigured()) {
            return ['id' => $screenId, 'name' => $name, 'online' => false];
        }

        $response = $this->request($user)->patch($this->path('screens') . '/' . rawurlencode($screenId), [
            'name' => $name,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo renombrar la pantalla: ' . $response->body());
        }

        $data = $response->json();

        return $data['screen'] ?? $data;
    }

    public function deleteScreen(User $user, string $screenId): void
    {
        if (! $this->isConfigured()) {
            return;
        }

        $response = $this->request($user)->delete($this->path('screens') . '/' . rawurlencode($screenId));

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo eliminar la pantalla: ' . $response->body());
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function pairScreen(User $user, string $screenId, string $code): array
    {
        if (! $this->isConfigured()) {
            return ['id' => $screenId, 'online' => true, 'code' => strtoupper($code)];
        }

        $response = $this->request($user)->post(
            $this->path('screens') . '/' . rawurlencode($screenId) . '/pair',
            ['code' => $code]
        );

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo emparejar la TV: ' . $response->body());
        }

        $data = $response->json();

        return $data['screen'] ?? $data;
    }

    public function listScreens(User $user): array
    {
        if (! $this->isConfigured()) {
            return ['screens' => $this->stubScreens(), 'raw' => null];
        }

        if (config('tvpik.stub_screens')) {
            return ['screens' => $this->stubScreens(), 'raw' => null];
        }

        $response = $this->request($user)->get($this->path('screens'));

        if (! $response->successful()) {
            throw new RuntimeException('No se pudieron cargar las pantallas TVPik: ' . $response->body());
        }

        $data = $response->json();
        $screens = $data['screens'] ?? $data['data'] ?? [];

        return ['screens' => is_array($screens) ? $screens : [], 'raw' => $data];
    }

    /**
     * @return array<string, mixed>
     */
    public function publish(User $user, string $screenId, string $companySlug, string $templateKey, string $publishUrl): array
    {
        if (! $this->isConfigured()) {
            return [
                'ok' => true,
                'message' => 'TVPik API no configurada; URL guardada localmente.',
                'published_url' => $publishUrl,
            ];
        }

        $response = $this->request($user)->post($this->path('publish'), [
            'screen_id' => $screenId,
            'company_slug' => $companySlug,
            'template_key' => $templateKey,
            'publish_url' => $publishUrl,
            'webnu_api_token' => $user->api_token,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Error al publicar en TVPik: ' . $response->body());
        }

        return $response->json() ?? ['ok' => true];
    }

    public function connect(User $user, string $tvpikToken): array
    {
        if (! $this->isConfigured()) {
            return ['ok' => true, 'org_id' => null];
        }

        $response = Http::timeout(config('tvpik.timeout', 15))
            ->acceptJson()
            ->withHeaders($this->baseHeaders($user, false))
            ->post(config('tvpik.api_url') . $this->path('connect'), [
                'tvpik_token' => $tvpikToken,
                'webnu_token' => $user->api_token,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('No se pudo conectar con TVPik.');
        }

        return $response->json() ?? [];
    }

    protected function request(User $user)
    {
        return Http::timeout(config('tvpik.timeout', 15))
            ->acceptJson()
            ->withHeaders($this->baseHeaders($user, true));
    }

    protected function bootstrapHeaders(User $user): array
    {
        $headers = [];

        if ($appKey = config('tvpik.app_key')) {
            $headers['X-Digital-Signage-Key'] = $appKey;
            $headers['X-Webnu-App-Key'] = $appKey;
        }

        if ($user->api_token) {
            $headers['X-Webnu-Token'] = $user->api_token;
        }

        return $headers;
    }

    protected function baseHeaders(User $user, bool $includeTvpikToken): array
    {
        $headers = [];

        if ($appKey = config('tvpik.app_key')) {
            $headers['X-Digital-Signage-Key'] = $appKey;
            $headers['X-Webnu-App-Key'] = $appKey;
        }

        if ($includeTvpikToken && $user->tvpik_api_token) {
            $headers['Authorization'] = 'Bearer ' . $user->plainTvpikApiToken();
        }

        if ($user->api_token) {
            $headers['X-Webnu-Token'] = $user->api_token;
        }

        return $headers;
    }

    protected function path(string $key): string
    {
        return config('tvpik.paths.' . $key, '/' . $key);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function stubScreens(): array
    {
        return [
            [
                'id' => 'demo-bar',
                'name' => 'Pantalla barra (demo)',
                'code' => 'DEMO01',
                'status' => 'pending',
                'online' => false,
                'gallery_id' => null,
            ],
            [
                'id' => 'demo-sala',
                'name' => 'Pantalla comedor (demo)',
                'code' => 'DEMO02',
                'status' => 'pending',
                'online' => false,
                'gallery_id' => null,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function stubCreateScreen(string $name): array
    {
        return [
            'id' => 'demo-' . substr(md5($name . microtime()), 0, 8),
            'name' => $name,
            'code' => 'STUB01',
            'status' => 'pending',
            'online' => false,
            'gallery_id' => null,
        ];
    }
}
