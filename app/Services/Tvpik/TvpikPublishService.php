<?php

namespace App\Services\Tvpik;

use App\Company;
use App\Services\MenuSyncService;
use App\TvpikScreenLink;
use App\User;
use Illuminate\Support\Facades\Log;

class TvpikPublishService
{
    protected $api;
    protected $menuSync;

    public function __construct(TvpikApiClient $api, MenuSyncService $menuSync)
    {
        $this->api = $api;
        $this->menuSync = $menuSync;
    }

    public function publishScreen(
        User $user,
        Company $company,
        string $screenId,
        string $screenName,
        string $templateKey,
        ?string $galleryId = null,
        ?int $menuId = null
    ): TvpikScreenLink {
        $templates = config('tvpik_templates.templates', []);
        if (! isset($templates[$templateKey])) {
            $templateKey = config('tvpik_templates.default', 'menu');
        }

        $publishUrl = $this->menuSync->tvUrlForTemplate($company, $templateKey);
        if ($menuId) {
            $separator = str_contains($publishUrl, '?') ? '&' : '?';
            $publishUrl .= $separator . 'menu=' . $menuId;
        }
        $syncVersion = $this->menuSync->syncVersion($company);

        $link = TvpikScreenLink::updateOrCreate(
            [
                'user_id' => $user->id,
                'tvpik_screen_id' => $screenId,
            ],
            [
                'company_id' => $company->id,
                'tvpik_screen_name' => $screenName,
                'tvpik_gallery_id' => $galleryId,
                'template_key' => $templateKey,
                'menu_id' => $menuId,
            ]
        );

        try {
            $this->api->publish($user, $screenId, $company->slug, $templateKey, $publishUrl);
            $link->published_url = $publishUrl;
            $link->sync_version = $syncVersion;
            $link->last_synced_at = now();
            $link->last_error = null;
            $link->save();
        } catch (\Throwable $e) {
            Log::warning('TVPik publish failed', [
                'screen_id' => $screenId,
                'company_id' => $company->id,
                'error' => $e->getMessage(),
            ]);
            $link->published_url = $publishUrl;
            $link->sync_version = $syncVersion;
            $link->last_error = $e->getMessage();
            $link->save();

            if ($this->api->isConfigured()) {
                throw $e;
            }
        }

        return $link;
    }

    public function publishAllForCompany(User $user, Company $company): int
    {
        $links = TvpikScreenLink::where('user_id', $user->id)
            ->where('company_id', $company->id)
            ->get();

        $count = 0;
        foreach ($links as $link) {
            $this->publishScreen(
                $user,
                $company,
                $link->tvpik_screen_id,
                $link->tvpik_screen_name ?? $link->tvpik_screen_id,
                $link->template_key,
                $link->tvpik_gallery_id,
                $link->menu_id
            );
            $count++;
        }

        return $count;
    }

    public function syncIfLinked(Company $company): void
    {
        $currentVersion = $this->menuSync->syncVersion($company);

        $links = TvpikScreenLink::where('company_id', $company->id)->get();

        foreach ($links as $link) {
            if ($link->sync_version === $currentVersion && $link->last_synced_at) {
                continue;
            }

            $user = $link->user;
            if (! $user) {
                continue;
            }

            try {
                $this->publishScreen(
                    $user,
                    $company,
                    $link->tvpik_screen_id,
                    $link->tvpik_screen_name ?? $link->tvpik_screen_id,
                    $link->template_key,
                    $link->tvpik_gallery_id,
                    $link->menu_id
                );
            } catch (\Throwable $e) {
                Log::warning('TVPik auto-sync skipped', ['link_id' => $link->id, 'error' => $e->getMessage()]);
            }
        }
    }
}
