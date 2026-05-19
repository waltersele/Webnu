<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\UserBillingPresenter;
use Illuminate\Http\Request;
use Stripe\BillingPortal\Session as BillingPortalSession;
use Stripe\Stripe;

class BillingController extends Controller
{
    public function index(UserBillingPresenter $presenter)
    {
        $user = auth()->user();

        return view('admin.billing.index', [
            'user' => $user,
            'statusLabel' => $presenter->statusLabel($user),
            'statusBadgeClass' => $presenter->statusBadgeClass($user),
            'planLabel' => $presenter->planLabel($user),
            'subscription' => $user->primarySubscription(),
            'hasAccess' => $user->hasActiveSubscription(),
        ]);
    }

    public function portal(Request $request)
    {
        $user = $request->user();

        if (! $user->stripe_id) {
            return redirect()
                ->route('welcome')
                ->with('flash_warning', 'Crea una suscripción para activar tu cuenta.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = BillingPortalSession::create([
            'customer' => $user->stripe_id,
            'return_url' => route('admin.billing'),
        ]);

        return redirect($session->url);
    }
}
