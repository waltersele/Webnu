<?php

namespace App\Policies;

use App\Company;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Company $company)
    {
        return $company->canBeManagedBy($user);
    }

    public function update(User $user, Company $company)
    {
        return $company->canBeManagedBy($user);
    }

    public function delete(User $user, Company $company)
    {
        if ($company->isActiveSalesLead() && (int) $company->sales_rep_user_id === (int) $user->id) {
            return true;
        }

        return (int) $company->user_id === (int) $user->id;
    }
}
