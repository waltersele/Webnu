<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAccountBillingInfoRequest;
use App\Http\Requests\UpdateAccountProfileRequest;
use App\Services\UserPlanService;
use App\Services\Platform\UserBillingPresenter;
use Illuminate\Http\Request;

class AccountSettingsController extends Controller
{
    public function index(UserBillingPresenter $presenter)
    {
        $user = auth()->user();
        $plans = app(UserPlanService::class);
        $planPresentation = $plans->planPresentation($user);
        $tier = $plans->tier($user);

        return view('admin.settings.index', [
            'user' => $user,
            'statusLabel' => $presenter->publicStatusLabel($user),
            'statusBadgeClass' => $presenter->statusBadgeClass($user),
            'planLabel' => $presenter->effectivePlanLabel($user),
            'stripePlanLabel' => $presenter->stripeSubscriptionLabel($user),
            'subscription' => $user->primarySubscription(),
            'hasAccess' => $user->hasActiveSubscription(),
            'planPresentation' => $planPresentation,
            'planKey' => $plans->planKey($user),
            'tier' => $tier,
            'featureFlags' => $plans->featureFlags($user),
            'planFeatureList' => $presenter->planFeatureList($user),
            'planComparison' => $presenter->planComparison($user),
            'cardSummary' => $presenter->cardSummary($user),
            'invoices' => $presenter->invoices($user)->take(6),
        ]);
    }

    public function updateProfile(UpdateAccountProfileRequest $request)
    {
        $user = $request->user();
        $user->fill($request->only(['name', 'phone']));
        $user->save();

        return redirect()
            ->route('admin.settings')
            ->with('flash', 'Datos personales actualizados.');
    }

    public function updateBillingInfo(UpdateAccountBillingInfoRequest $request)
    {
        $user = $request->user();
        $user->fill($request->only([
            'legal_name',
            'tax_id',
            'billing_address',
            'billing_postal_code',
            'billing_city',
            'billing_country',
        ]));
        $user->save();

        if ($user->stripe_id) {
            $this->syncStripeCustomer($user);
        }

        return redirect()
            ->to(route('admin.settings') . '#facturacion')
            ->with('flash', 'Datos de facturación guardados.');
    }

    protected function syncStripeCustomer($user): void
    {
        try {
            $payload = ['name' => $user->legal_name ?: $user->name];
            $address = array_filter([
                'line1' => $user->billing_address,
                'postal_code' => $user->billing_postal_code,
                'city' => $user->billing_city,
                'country' => $user->billing_country ?: 'ES',
            ]);
            if (!empty($address)) {
                $payload['address'] = $address;
            }
            $user->updateStripeCustomer($payload);
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
