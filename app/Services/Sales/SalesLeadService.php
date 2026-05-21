<?php

namespace App\Services\Sales;

use App\Company;
use App\Services\CompanySlugService;
use App\User;
use Illuminate\Database\Eloquent\Collection;

class SalesLeadService
{
    protected CompanySlugService $slugs;

    public function __construct(CompanySlugService $slugs)
    {
        $this->slugs = $slugs;
    }

    public function activeLeadsFor(User $rep): Collection
    {
        return Company::query()
            ->where('sales_rep_user_id', $rep->id)
            ->whereNull('sales_converted_at')
            ->orderByDesc('updated_at')
            ->get();
    }

    public function findActiveLeadFor(User $rep, int $companyId): Company
    {
        return Company::query()
            ->where('id', $companyId)
            ->where('sales_rep_user_id', $rep->id)
            ->whereNull('sales_converted_at')
            ->firstOrFail();
    }

    public function createLead(User $rep, string $name, ?string $city = null): Company
    {
        $slug = $this->slugs->generateFromName($name);

        return Company::create([
            'name' => $name,
            'slug' => $slug,
            'city' => $city,
            'template' => 'lumiere',
            'menu_type' => 1,
            'enabled' => false,
            'reservation' => false,
            'user_id' => $rep->id,
            'sales_rep_user_id' => $rep->id,
        ]);
    }

    public function demoPhotoSlotsUsed(Company $company): int
    {
        return (int) $company->sections()
            ->with(['products' => function ($query) {
                $query->where('sales_demo_highlight', true);
            }])
            ->get()
            ->sum(function ($section) {
                return $section->products->count();
            });
    }

    public function demoPhotoSlotsRemaining(Company $company): int
    {
        $max = \App\PlatformSetting::salesDemoMaxPhotoProducts();

        return max(0, $max - $this->demoPhotoSlotsUsed($company));
    }

    public function productCount(Company $company): int
    {
        return (int) $company->sections()->withCount('products')->get()->sum('products_count');
    }

    /**
     * Tras login o nueva visita: pantalla de importar carta si aún no hay platos.
     */
    public function importEntryUrl(User $rep): string
    {
        $visit = $this->activeLeadsFor($rep)->first(function (Company $company) {
            return $this->productCount($company) < 1;
        });

        if ($visit) {
            return route('sales.menu-scan.create', $visit->id);
        }

        return route('sales.dashboard');
    }
}
