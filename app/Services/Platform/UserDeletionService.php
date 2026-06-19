<?php

namespace App\Services\Platform;

use App\User;
use Illuminate\Support\Facades\Auth;

class UserDeletionService
{
    public function delete(User $user, ?User $actor = null): void
    {
        if ($user->isSuperAdmin()) {
            throw new \InvalidArgumentException('No se puede eliminar una cuenta superadmin desde el panel.');
        }

        $subscription = $user->primarySubscription();
        if ($subscription && $subscription->stripe_status !== 'canceled') {
            try {
                $subscription->cancelNow();
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $user->subscriptions()->delete();
        $user->roles()->detach();
        if (method_exists($user, 'permissions')) {
            $user->permissions()->detach();
        }

        $userId = $user->id;
        $isSelf = $actor && (int) $actor->id === (int) $userId;

        if (! $user->delete()) {
            throw new \RuntimeException('No se pudo eliminar la cuenta.');
        }

        if ($isSelf && Auth::id() === $userId) {
            Auth::logout();
        }
    }
}
