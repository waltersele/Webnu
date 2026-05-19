<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PlatformSetting;
use App\Services\MenuScan\GeminiMenuScanProvider;
use App\Services\Platform\PlatformSettingsService;
use Illuminate\Http\Request;

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
        ]);
    }

    public function update(Request $request, PlatformSettingsService $settings)
    {
        $this->authorize('platform.access');

        $request->validate([
            'gemini_api_key' => 'nullable|string|min:20|max:500',
            'gemini_model' => 'nullable|string|max:64',
            'clear_gemini_key' => 'nullable|boolean',
        ]);

        if ($request->boolean('clear_gemini_key')) {
            $settings->clearGeminiApiKey();
        } elseif ($request->filled('gemini_api_key')) {
            $settings->updateGemini($request->get('gemini_api_key'), $request->get('gemini_model'));
        } elseif ($request->filled('gemini_model')) {
            $settings->updateGemini(null, $request->get('gemini_model'));
        }

        return redirect()
            ->route('admin.platform.settings')
            ->with('flash', 'Configuración de escaneo guardada.');
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
}
