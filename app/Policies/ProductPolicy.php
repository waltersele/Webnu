<?php

namespace App\Policies;

use App\Product;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProductPolicy
{
    use HandlesAuthorization;

    public function update(User $user, Product $product)
    {
        $product->loadMissing('section.company');

        if (!$product->section || !$product->section->company) {
            return false;
        }

        return (int) $product->section->company->user_id === (int) $user->id;
    }

    public function delete(User $user, Product $product)
    {
        return $this->update($user, $product);
    }
}
