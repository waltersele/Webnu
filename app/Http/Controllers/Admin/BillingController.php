<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\UserBillingPresenter;
use Illuminate\Http\Request;
use Stripe\BillingPortal\Session as BillingPortalSession;
use Stripe\Stripe;

class BillingController extends Controller
{
    public function index()
    {
        return redirect()->to(route('admin.settings') . '#plan', 301);
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
            'return_url' => route('admin.settings') . '#plan',
        ]);

        return redirect($session->url);
    }
}
