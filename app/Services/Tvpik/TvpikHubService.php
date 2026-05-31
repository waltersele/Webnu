<?php

namespace App\Services\Tvpik;

use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class TvpikHubService
{
    protected TvpikApiClient $api;

    public function __construct(TvpikApiClient $api)
    {
        $this->api = $api;
    }

    /**
     * Conecta silenciosamente con TVPik si el usuario tiene plan Pantallas.
     * Idempotente: reutiliza token existente o llama bootstrap.
     */
    public function ensureConnected(User $user): bool
    {
        if ($user->isTvpikConnected()) {
            return true;
        }

        if (! $this->api->isConfigured()) {
            return false;
        }

        if (! $user->api_token) {
            $user->api_token = Str::random(80);
            $user->save();
        }

        try {
            $result = $this->api->bootstrap($user);
        } catch (\Throwable $e) {
            Log::warning('TVPik hub bootstrap failed', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }

        $token = $result['tvpik_token'] ?? $result['token'] ?? null;
        if (! $token) {
            return false;
        }

        $user->tvpik_api_token = $token;
        $user->tvpik_connected_at = now();
        $user->tvpik_org_id = isset($result['org_id']) ? (string) $result['org_id'] : null;
        $user->save();

        return true;
    }
}
