<?php

namespace App\Http\Controllers;

use App\Mail\OrderShipped;
use App\Services\Platform\BillingPriceResolver;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Cashier\Exceptions\IncompletePayment;
use Stripe\Charge;
use Stripe\Customer;
use Stripe\Exception\CardException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function pay_product(Request $request)
    {
        $request->validate([
            'stripeEmail' => 'required|email',
            'stripeToken' => 'required',
        ]);

        try {
            Stripe::setApiKey(config('services.stripe.secret'));
            $customer = Customer::create([
                'email' => $request->stripeEmail,
                'source' => $request->stripeToken,
            ]);
            Charge::create([
                'customer' => $customer->id,
                'amount' => 1000,
                'currency' => 'eur',
            ]);

            return '¡Pago completado correctamente!';
        } catch (Exception $ex) {
            report($ex);

            return redirect()->route('home')->with([
                'failure' => 'Se produjo un error al procesar el pago.',
            ]);
        }
    }

    public function process_subscription(Request $request, BillingPriceResolver $priceResolver)
    {
        try {
            DB::beginTransaction();

            $this->validate($request, [
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'payment_method' => 'required|string',
                'plan_tier' => 'required|in:pro,plus',
                'billing_cycle' => 'required|in:monthly,yearly',
                'tvpik_addon' => 'nullable|in:,screen_1,pack_5',
                'privacy_policy' => 'accepted',
            ]);

            $user = User::create([
                'name' => $request->email,
                'slug' => User::generateUniqueSlug($request->email),
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->createAsStripeCustomer();
            $user->addPaymentMethod($request->payment_method);
            $user->updateDefaultPaymentMethod($request->payment_method);

            $tier = $request->input('plan_tier', 'pro');
            $cycle = $request->input('billing_cycle', 'monthly');
            $priceKey = $tier . '_' . $cycle;
            $priceId = $priceResolver->priceId($priceKey);
            $subscriptionName = config('billing.subscription_names.' . $priceKey)
                ?: config('billing.subscription_names.' . $cycle);

            if (! $priceId) {
                throw new InvalidRequestException('Precio Stripe no configurado para ' . $priceKey . '. Créalo en Plataforma → Facturación o añade STRIPE_PRICE_* al .env.');
            }

            $user->newSubscription($subscriptionName, $priceId)
                ->create($request->payment_method);

            $addon = $request->input('tvpik_addon');
            $addonPriceId = $addon ? $priceResolver->priceId('tvpik_' . $addon) : null;
            if ($addonPriceId) {
                $addonName = config('billing.subscription_names.tvpik_' . $addon);
                if ($addonName) {
                    $user->newSubscription($addonName, $addonPriceId)
                        ->create($request->payment_method);
                }
                if ($addon === 'screen_1') {
                    $user->tvpik_extra_screens = max((int) $user->tvpik_extra_screens, 1);
                } elseif ($addon === 'pack_5') {
                    $user->tvpik_extra_screens = max((int) $user->tvpik_extra_screens, 5);
                }
                $user->save();
            }

            DB::commit();

            Mail::to($user)->send(new OrderShipped($user));

            return redirect()->route('login')->with([
                'success' => 'Suscripción creada correctamente',
            ]);
        } catch (IncompletePayment $exception) {
            DB::rollBack();
            report($exception);

            return redirect()->route('home')->with([
                'failure' => 'Se produjo un error al procesar el pago.',
            ]);
        } catch (InvalidRequestException $exception) {
            DB::rollBack();
            report($exception);

            return redirect()->route('home')->with([
                'failure' => 'Se produjo un error al procesar el pago.',
            ]);
        } catch (CardException $exception) {
            DB::rollBack();
            report($exception);

            return redirect()->route('home')->with([
                'failure' => 'Se produjo un error al procesar el pago.',
            ]);
        } catch (Exception $exception) {
            DB::rollBack();
            report($exception);

            return redirect()->route('home')->with([
                'failure' => 'Se produjo un error al procesar el pago.',
            ]);
        }
    }
}
