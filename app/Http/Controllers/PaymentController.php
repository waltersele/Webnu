<?php

namespace App\Http\Controllers;

use App\Mail\OrderShipped;
use App\Services\Billing\TvpikScreenPricing;
use App\Services\Platform\BillingPriceResolver;
use App\Services\Platform\StripePriceService;
use App\User;
use Illuminate\Validation\ValidationException;
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

    public function process_subscription(
        Request $request,
        BillingPriceResolver $priceResolver,
        TvpikScreenPricing $tvpikPricing,
        StripePriceService $stripePrices
    ) {
        try {
            DB::beginTransaction();

            $this->validate($request, [
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'payment_method' => 'required|string',
                'plan_tier' => 'required|in:pro,plus',
                'billing_cycle' => 'required|in:monthly,yearly',
                'tvpik_screens' => 'nullable|integer|min:0|max:20',
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

            $requestedScreens = $request->has('tvpik_screens')
                ? (int) $request->input('tvpik_screens')
                : 0;
            $totalLicensed = $tvpikPricing->normalizeTotalScreens($tier, $requestedScreens);

            if ($requestedScreens > 0 && ! $tvpikPricing->isValidTotalForTier($tier, $requestedScreens)) {
                throw ValidationException::withMessages([
                    'tvpik_screens' => 'Número de pantallas no válido. En Pro el mínimo es 2; en Plus, de 1 a 20.',
                ]);
            }

            $addonCents = $tvpikPricing->addonRecurringCents($totalLicensed, $tier, $cycle);
            if ($addonCents > 0) {
                $stripeInterval = $cycle === 'yearly' ? 'year' : 'month';
                $created = $stripePrices->createTvpikScreensPrice($addonCents, $stripeInterval, [
                    'webnu_tvpik_screens' => (string) $totalLicensed,
                    'webnu_plan_tier' => $tier,
                ]);
                $addonName = config('billing.subscription_names.tvpik_screens', 'planqr_tvpik');
                $user->newSubscription($addonName, $created['price_id'])
                    ->create($request->payment_method);
            }

            $extra = $tvpikPricing->extraScreensBeyondIncluded($tier, $totalLicensed);
            if ($extra > 0) {
                $user->tvpik_extra_screens = max((int) $user->tvpik_extra_screens, $extra);
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
