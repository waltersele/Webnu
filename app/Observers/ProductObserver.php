<?php

namespace App\Observers;

use App\Product;

class ProductObserver
{
    use TriggersTvpikSync;

    public function saved(Product $product): void
    {
        $section = $product->section;
        if ($section) {
            $this->dispatchTvpikSyncForCompanyId($section->company_id);
        }
    }

    public function deleted(Product $product): void
    {
        $section = $product->section;
        if ($section) {
            $this->dispatchTvpikSyncForCompanyId($section->company_id);
        }
    }
}
