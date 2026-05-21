<?php

namespace App\Observers;

use App\Company;
use App\Jobs\SyncCompanyToTvpikScreens;
use App\Product;
use App\Section;
use App\TvpikScreenLink;

trait TriggersTvpikSync
{
    protected function dispatchTvpikSyncForCompanyId(?int $companyId): void
    {
        if (! $companyId) {
            return;
        }

        if (! TvpikScreenLink::where('company_id', $companyId)->exists()) {
            return;
        }

        SyncCompanyToTvpikScreens::dispatch($companyId);
    }
}
