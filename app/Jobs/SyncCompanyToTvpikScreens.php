<?php

namespace App\Jobs;

use App\Company;
use App\Services\Tvpik\TvpikPublishService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncCompanyToTvpikScreens implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    /** @var int */
    public $companyId;

    public function __construct(int $companyId)
    {
        $this->companyId = $companyId;
    }

    public function handle(TvpikPublishService $publishService): void
    {
        $company = Company::find($this->companyId);
        if (! $company) {
            return;
        }

        $publishService->syncIfLinked($company);
    }
}
