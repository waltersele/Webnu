<?php

namespace App\Observers;

use App\Section;

class SectionObserver
{
    use TriggersTvpikSync;

    public function saved(Section $section): void
    {
        $this->dispatchTvpikSyncForCompanyId($section->company_id);
    }

    public function deleted(Section $section): void
    {
        $this->dispatchTvpikSyncForCompanyId($section->company_id);
    }
}
