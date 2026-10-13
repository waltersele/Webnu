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
                ->to(route('admin.settings') . '#plan')
                ->with('flash_warning', 'Elige un plan Pro o Plus para gestionar la facturación en Stripe.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = BillingPortalSession::create([
            'customer' => $user->stripe_id,
            'return_url' => route('admin.settings') . '#plan',
        ]);

        return redirect($session->url);
    }
}
