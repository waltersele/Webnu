<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\UserBillingPresenter;
use App\Services\UserPlanService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $data = $request->validate([
            'plan' => 'required|string|in:' . $tierKeys,
            'tvpik_extra_screens' => 'nullable|integer|min:0|max:100',
            'manual_plan_key' => 'nullable|string|in:' . $tierKeys,
            'manual_plan_until' => 'nullable|date|after:today',
            'manual_plan_note' => 'nullable|string|max:1000',
        ]);

        $user->plan = $data['plan'];
        $user->tvpik_extra_screens = (int) ($data['tvpik_extra_screens'] ?? 0);

        $manualKey = $data['manual_plan_key'] ?? null;
        if ($manualKey === null || $manualKey === '') {
            $user->manual_plan_key = null;
            $user->manual_plan_until = null;
            $user->manual_plan_note = null;
        } else {
            $user->manual_plan_key = $manualKey;
            $user->manual_plan_until = ! empty($data['manual_plan_until'])
                ? \Illuminate\Support\Carbon::parse($data['manual_plan_until'])->endOfDay()
                : null;
            $user->manual_plan_note = $data['manual_plan_note'] ?? null;
        }

        $user->save();

        return redirect()
            ->route('admin.platform.users.show', $user)
            ->with('flash', 'Plan y configuración actualizados.');
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

    public function impersonate(Request $request, User $user)
    {
        $this->authorize('platform.access');

        $admin = $request->user();
        if (! $admin || ! $admin->isSuperAdmin()) {
            abort(403);
        }

        if ($admin->id === $user->id) {
            return redirect()
                ->route('admin.platform.users.show', $user)
                ->with('flash', 'Ya estás operando como este usuario.');
        }

        $request->session()->put('impersonator_id', $admin->id);
        Auth::loginUsingId($user->id);

        return redirect()->route('admin.dashboard')
            ->with('flash', 'Has entrado como ' . ($user->name ?: $user->email) . '.');
    }

    public function stopImpersonating(Request $request)
    {
        $impersonatorId = (int) $request->session()->pull('impersonator_id', 0);
        if ($impersonatorId <= 0) {
            return redirect()->route('admin.dashboard');
        }

        Auth::loginUsingId($impersonatorId);

        return redirect()
            ->route('admin.platform.users.index')
            ->with('flash', 'Has vuelto a tu cuenta de superadmin.');
    }
}
