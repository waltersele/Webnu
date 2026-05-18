<?php

namespace App\Http\Controllers;

use App\Mail\OrderShipped;
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

    public function process_subscription(Request $request)
    {
        try {
            DB::beginTransaction();

            $this->validate($request, [
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'payment_method' => 'required|string',
                'subscription' => 'required|in:1,2',
                'privacy_policy' => 'accepted',
            ]);

            $user = User::create([
                'name' => $request->email,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $user->createAsStripeCustomer();
            $user->addPaymentMethod($request->payment_method);
            $user->updateDefaultPaymentMethod($request->payment_method);

            $subscriptionType = (int) $request->subscription;
            $priceId = $subscriptionType === 1
                ? config('billing.stripe_prices.monthly')
                : config('billing.stripe_prices.yearly');
            $subscriptionName = $subscriptionType === 1
                ? config('billing.subscription_names.monthly')
                : config('billing.subscription_names.yearly');

            $user->newSubscription($subscriptionName, $priceId)
                ->create($request->payment_method);

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
