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
        return (int) $company->user_id === (int) $user->id;
    }

    public function update(User $user, Company $company)
    {
        return (int) $company->user_id === (int) $user->id;
    }

    public function delete(User $user, Company $company)
    {
        return (int) $company->user_id === (int) $user->id;
    }
}
