<?php

namespace App\Observers;

use App\Company;

class CompanyObserver
{
    use TriggersTvpikSync;

    public function updated(Company $company): void
    {
        $this->dispatchTvpikSyncForCompanyId($company->id);
    }
}
