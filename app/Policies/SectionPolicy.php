<?php

namespace App\Policies;

use App\Section;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SectionPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Section $section)
    {
        $section->loadMissing('company');

        return $section->company
            && (int) $section->company->user_id === (int) $user->id;
    }

    public function delete(User $user, Section $section)
    {
        return $this->update($user, $section);
    }
}
