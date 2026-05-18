<?php

namespace App\Services;

use App\Company;
use App\Section;
use Illuminate\Support\Collection;

class MenuService
{
    public function sectionsForCompany(Company $company): Collection
    {
        $sections = Section::with(['products.allergens'])
            ->orderBy('order')
            ->where('company_id', $company->id)
            ->get();

        return $sections->map(function ($section) {
            $section->setRelation(
                'products',
                $section->products->sortBy('order')->values()
            );

            return $section;
        });
    }

    public function themeViewName(Company $company): string
    {
        $templates = ['basic', 'pasion', 'oriental', 'visual'];

        if (in_array($company->template, $templates, true)) {
            return 'themes.' . $company->template;
        }

        $company->template = 'basic';

        return 'themes.basic';
    }
}
