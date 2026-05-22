<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PlatformSetting;
use App\Services\MenuScan\GeminiMenuScanProvider;
use App\Services\Platform\PlatformIntegrationsConfigurator;
use App\Services\Platform\PlatformMailConfigurator;
use App\Services\Platform\PlatformSettingsService;
use App\Services\Platform\PlatformStripeConfigurator;
use App\Services\Platform\StripeConnectionTester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PlatformSettingsController extends Controller
{
    public function edit(PlatformSettingsService $settings)
    {
        $this->authorize('platform.access');

        return view('admin.platform.settings', [
            'geminiConfigured' => $settings->hasGeminiApiKey(),
            'geminiKeyHint' => $settings->geminiApiKeyHint(),
            'geminiModel' => $settings->geminiModel(),
            'recommendedModels' => config('menu_scan.recommended_models', []),
            'mail' => $settings->mailSettingsForForm(),
            'contact' => $settings->contactSettingsForForm(),
            'integrations' => $settings->integrationsSettingsForForm(),
        ]);
    }

    public function update(Request $request, PlatformSettingsService $settings)
    {
        $this->authorize('platform.access');

        $request->validate([
            'gemini_api_key' => 'nullable|string|min:20|max:500',
            'gemini_model' => 'nullable|string|max:64',
            'clear_gemini_key' => 'nullable|boolean',
            'mail_mailer' => 'nullable|string|in:smtp,log,sendmail',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:500',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
            'clear_mail_password' => 'nullable|boolean',
            'contact_leads_email' => 'required|email|max:255',
            'contact_suggestions_email' => 'required|email|max:255',
            'contact_public_email' => 'required|email|max:255',
            'stripe_key' => 'nullable|string|max:255',
            'stripe_secret' => 'nullable|string|max:500',
            'stripe_webhook_secret' => 'nullable|string|max:500',
            'clear_stripe_secret' => 'nullable|boolean',
            'clear_stripe_webhook_secret' => 'nullable|boolean',
            'tvpik_api_url' => 'nullable|url|max:500',
            'tvpik_web_url' => 'nullable|url|max:500',
            'tvpik_app_key' => 'nullable|string|max:500',
            'clear_tvpik_app_key' => 'nullable|boolean',
            'tvpik_stub_screens' => 'nullable|boolean',
            'digital_signage_app_key' => 'nullable|string|max:500',
            'clear_digital_signage_app_key' => 'nullable|boolean',
            'digital_signage_only_enabled' => 'nullable|boolean',
        ]);

        if ($request->boolean('clear_gemini_key')) {
            $settings->clearGeminiApiKey();
        } elseif ($request->filled('gemini_api_key')) {
            $settings->updateGemini($request->get('gemini_api_key'), $request->get('gemini_model'));
        } elseif ($request->filled('gemini_model')) {
            $settings->updateGemini(null, $request->get('gemini_model'));
        }

        if ($request->boolean('clear_mail_password')) {
            $settings->clearMailPassword();
        }

        $settings->updateMail($request->only([
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ]));

        $settings->updateContact($request->only([
            'contact_leads_email',
            'contact_suggestions_email',
            'contact_public_email',
        ]));

        $settings->updateIntegrations(array_merge($request->only([
            'stripe_key',
            'stripe_secret',
            'stripe_webhook_secret',
            'tvpik_api_url',
            'tvpik_web_url',
            'tvpik_app_key',
            'digital_signage_app_key',
        ]), [
            'clear_stripe_secret' => $request->boolean('clear_stripe_secret'),
            'clear_stripe_webhook_secret' => $request->boolean('clear_stripe_webhook_secret'),
            'clear_tvpik_app_key' => $request->boolean('clear_tvpik_app_key'),
            'clear_digital_signage_app_key' => $request->boolean('clear_digital_signage_app_key'),
            'tvpik_stub_screens' => $request->boolean('tvpik_stub_screens'),
            'digital_signage_only_enabled' => $request->boolean('digital_signage_only_enabled'),
        ]));

        app(PlatformMailConfigurator::class)->apply();
        app(PlatformStripeConfigurator::class)->apply();
        app(PlatformIntegrationsConfigurator::class)->apply();

        return redirect()
            ->route('admin.platform.settings')
            ->with('flash', 'Configuración de plataforma guardada.');
    }

    public function testGemini(Request $request, PlatformSettingsService $settings)
    {
        $this->authorize('platform.access');

        $apiKey = $request->filled('gemini_api_key')
            ? trim($request->get('gemini_api_key'))
            : $settings->geminiApiKey();
        $model = $request->filled('gemini_model')
            ? PlatformSetting::resolveGeminiModel(trim($request->get('gemini_model')))
            : $settings->geminiModel();

        $result = GeminiMenuScanProvider::testConnection($apiKey, $model);

        return redirect()
            ->route('admin.platform.settings')
            ->with($result['ok'] ? 'flash' : 'flash_warning', $result['message']);
    }

    public function testMail(Request $request, PlatformMailConfigurator $mailConfigurator)
    {
        $this->authorize('platform.access');

        $data = $request->validate([
            'test_email' => 'required|email|max:255',
            'mail_mailer' => 'nullable|string|in:smtp,log,sendmail',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:500',
            'mail_encryption' => 'nullable|in:tls,ssl',
            'mail_from_address' => 'nullable|email|max:255',
            'mail_from_name' => 'nullable|string|max:255',
        ]);

        $mailConfigurator->apply($request->only([
            'mail_mailer',
            'mail_host',
            'mail_port',
            'mail_username',
            'mail_password',
            'mail_encryption',
            'mail_from_address',
            'mail_from_name',
        ]));

        try {
            Mail::raw(
                'Correo de prueba enviado desde el panel de superadmin de Webnu. Si lo recibes, la configuración SMTP es correcta.',
                function ($message) use ($data) {
                    $message->to($data['test_email'])
                        ->subject('Prueba de correo — Webnu');
                }
            );
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->route('admin.platform.settings')
                ->with('flash_warning', 'No se pudo enviar el correo de prueba: ' . $e->getMessage());
        }

        return redirect()
            ->route('admin.platform.settings')
            ->with('flash', 'Correo de prueba enviado a ' . $data['test_email'] . '.');
    }

    public function testStripe(Request $request, StripeConnectionTester $tester, PlatformStripeConfigurator $stripeConfigurator)
    {
        $this->authorize('platform.access');

        $secret = $request->filled('stripe_secret')
            ? trim($request->get('stripe_secret'))
            : PlatformSetting::stripeSecret();

        if ($request->filled('stripe_key')) {
            config(['services.stripe.key' => trim($request->get('stripe_key'))]);
        }
        if ($secret) {
            config(['services.stripe.secret' => $secret]);
        } else {
            $stripeConfigurator->apply();
        }

        $result = $tester->test($secret);

        return redirect()
            ->route('admin.platform.settings')
            ->with($result['ok'] ? 'flash' : 'flash_warning', $result['message']);
    }
}
