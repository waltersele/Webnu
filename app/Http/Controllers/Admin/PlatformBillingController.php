<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Platform\BillingPriceResolver;
use App\Services\Platform\PlatformStripeConfigurator;
use App\Services\Platform\StripePriceService;
use Illuminate\Http\Request;

class PlatformBillingController extends Controller
{
    public function index(StripePriceService $prices, BillingPriceResolver $resolver)
    {
        $this->authorize('platform.access');

        return view('admin.platform.billing.index', [
            'catalog' => $prices->catalogStatus(),
            'stripeConfigured' => $prices->stripeConfigured(),
            'stripeDashboardUrl' => config('platform.stripe_dashboard_customer_url'),
            'settingsUrl' => route('admin.platform.settings'),
        ]);
    }

    public function createPrice(Request $request, StripePriceService $prices)
    {
        $this->authorize('platform.access');

        $request->validate([
            'catalog_key' => 'required|string|in:' . implode(',', array_keys(config('billing.price_catalog', []))),
        ]);

        try {
            $result = $prices->createPrice($request->input('catalog_key'));

            return redirect()
                ->route('admin.platform.billing.index')
                ->with('flash', 'Precio creado en Stripe: ' . $result['price_id']);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.platform.billing.index')
                ->with('flash_warning', 'No se pudo crear el precio: ' . $e->getMessage());
        }
    }

    public function recreatePrice(Request $request, StripePriceService $prices)
    {
        $this->authorize('platform.access');

        $request->validate([
            'catalog_key' => 'required|string|in:' . implode(',', array_keys(config('billing.price_catalog', []))),
        ]);

        try {
            $result = $prices->recreatePrice($request->input('catalog_key'));

            return redirect()
                ->route('admin.platform.billing.index')
                ->with('flash', 'Nuevo precio en Stripe: ' . $result['price_id'] . ' (suscripciones antiguas siguen con el price anterior).');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.platform.billing.index')
                ->with('flash_warning', $e->getMessage());
        }
    }

    public function saveAmount(Request $request, StripePriceService $prices)
    {
        $this->authorize('platform.access');

        $request->validate([
            'catalog_key' => 'required|string|in:' . implode(',', array_keys(config('billing.price_catalog', []))),
            'amount_eur' => 'required|numeric|min:0.01|max:99999',
        ]);

        try {
            $cents = (int) round((float) $request->input('amount_eur') * 100);
            $prices->saveAmountCents($request->input('catalog_key'), $cents);

            return redirect()
                ->route('admin.platform.billing.index')
                ->with('flash', 'Importe guardado. Si ya hay un Price ID, usa «Recrear en Stripe» para aplicar el nuevo importe a nuevas suscripciones.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.platform.billing.index')
                ->with('flash_warning', $e->getMessage());
        }
    }

    public function createAllPrices(StripePriceService $prices)
    {
        $this->authorize('platform.access');

        try {
            $results = $prices->createAllMissing();
            $created = 0;
            $failed = 0;
            foreach ($results as $row) {
                if (! empty($row['ok']) && empty($row['skipped'])) {
                    $created++;
                } elseif (empty($row['ok'])) {
                    $failed++;
                }
            }

            $message = "Precios creados: {$created}.";
            if ($failed > 0) {
                $message .= " Errores: {$failed}. Revisa el log.";
            }

            return redirect()
                ->route('admin.platform.billing.index')
                ->with($failed > 0 ? 'flash_warning' : 'flash', $message);
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.platform.billing.index')
                ->with('flash_warning', $e->getMessage());
        }
    }

    public function clearCatalog(Request $request, StripePriceService $prices, PlatformStripeConfigurator $stripeConfigurator)
    {
        $this->authorize('platform.access');

        $prices->clearStripeCatalog();
        $stripeConfigurator->apply();

        return redirect()
            ->route('admin.platform.billing.index')
            ->with('flash', 'Catálogo Stripe local borrado. Configura las claves de tu cuenta nueva y pulsa «Crear todos los que falten».');
    }

    public function savePriceId(Request $request, StripePriceService $prices)
    {
        $this->authorize('platform.access');

        $request->validate([
            'catalog_key' => 'required|string|in:' . implode(',', array_keys(config('billing.price_catalog', []))),
            'price_id' => 'required|string|max:255',
        ]);

        try {
            $prices->savePriceId($request->input('catalog_key'), $request->input('price_id'));

            return redirect()
                ->route('admin.platform.billing.index')
                ->with('flash', 'ID de precio guardado.');
        } catch (\Throwable $e) {
            return redirect()
                ->route('admin.platform.billing.index')
                ->with('flash_warning', $e->getMessage());
        }
    }
}
