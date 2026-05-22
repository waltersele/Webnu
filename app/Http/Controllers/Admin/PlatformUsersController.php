<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\UserBillingPresenter;
use App\Services\UserPlanService;
use App\User;
use Illuminate\Http\Request;

class PlatformUsersController extends Controller
{
    public function index(UserBillingPresenter $presenter)
    {
        $this->authorize('platform.access');

        $users = User::withBillingSummary()
            ->orderByDesc('created_at')
            ->paginate(25);

        return view('admin.platform.users.index', compact('users', 'presenter'));
    }

    public function show(User $user, UserBillingPresenter $presenter, UserPlanService $plans)
    {
        $this->authorize('platform.access');

        $user->load(['companies' => function ($query) {
            $query->orderBy('name');
        }]);

        return view('admin.platform.users.show', [
            'user' => $user,
            'presenter' => $presenter,
            'invoices' => $presenter->invoices($user),
            'planPresentation' => $plans->planPresentation($user),
            'planTiers' => config('plans.tiers', []),
            'effectivePlanKey' => $plans->planKey($user),
        ]);
    }

    public function updateBilling(Request $request, User $user)
    {
        $this->authorize('platform.access');

        $tierKeys = implode(',', array_keys(config('plans.tiers', [])));

        $request->validate([
            'plan' => 'required|string|in:' . $tierKeys,
            'tvpik_extra_screens' => 'nullable|integer|min:0|max:100',
        ]);

        $user->plan = $request->input('plan');
        $user->tvpik_extra_screens = (int) $request->input('tvpik_extra_screens', 0);
        $user->save();

        return redirect()
            ->route('admin.platform.users.show', $user)
            ->with('flash', 'Plan y pantallas TVPik actualizados.');
    }

    public function grantSuperAdmin(User $user)
    {
        $this->authorize('platform.access');

        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }

        return redirect()
            ->route('admin.platform.users.show', $user)
            ->with('flash', 'Rol super-admin asignado.');
    }

    public function cancelSubscription(User $user)
    {
        $this->authorize('platform.access');

        $subscription = $user->primarySubscription();
        if ($subscription && $subscription->stripe_status !== 'canceled') {
            $subscription->cancel();
        }

        return redirect()
            ->route('admin.platform.users.show', $user)
            ->with('flash', 'Suscripción programada para cancelarse al final del periodo.');
    }

    public function resumeSubscription(User $user)
    {
        $this->authorize('platform.access');

        $subscription = $user->primarySubscription();
        if ($subscription && $subscription->onGracePeriod()) {
            $subscription->resume();
        }

        return redirect()
            ->route('admin.platform.users.show', $user)
            ->with('flash', 'Suscripción reanudada.');
    }
}
