<?php

namespace App\Services\MenuTranslation;

use App\PlatformSetting;
use App\Services\MenuScan\GeminiMenuScanProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GeminiMenuTranslationProvider
{
    /** @var Client */
    protected $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client(GeminiMenuScanProvider::httpClientOptions());
    }

    public function name(): string
    {
        return 'gemini';
    }

    /**
     * @param array<int, array{type: string, id: int, name: string, description?: string}> $items
     * @return array<int, array{id: int, type: string, name: string, description?: string}>|null
     */
    public function translateBatch(array $items, string $fromLocale, string $toLocale): ?array
    {
        $apiKey = PlatformSetting::geminiApiKey();
        if ($apiKey === '') {
            return null;
        }

        $fromLabel = config('menu_locales.supported.' . $fromLocale . '.native', $fromLocale);
        $toLabel = config('menu_locales.supported.' . $toLocale . '.native', $toLocale);

        $payloadItems = array_map(function ($item) {
            $row = [
                'id' => $item['id'],
                'type' => $item['type'],
                'name' => $item['name'],
            ];
            if ($item['type'] === 'product') {
                $row['description'] = $item['description'] ?? '';
            }

            return $row;
        }, $items);

        $prompt = $this->buildPrompt($fromLabel, $toLabel, $payloadItems);
        $parts = [['text' => $prompt]];

        $preferred = PlatformSetting::geminiModel();
        foreach (PlatformSetting::geminiModelsToTry($preferred) as $model) {
            try {
                $body = $this->callGenerateContent($apiKey, $model, $parts);
                $text = $this->extractTextFromResponse($body);
                if ($text === null) {
                    continue;
                }

                $decoded = json_decode($this->extractJsonFromText($text), true);
                if (! is_array($decoded) || ! isset($decoded['items']) || ! is_array($decoded['items'])) {
                    continue;
                }

                return $decoded['items'];
            } catch (ClientException $e) {
                $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
                if (! in_array($status, [404, 400, 429, 500, 502, 503, 504], true)) {
                    throw $e;
                }
            } catch (GuzzleException $e) {
                Log::warning('Gemini translation failed', ['message' => $e->getMessage()]);
            }
        }

        return null;
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    protected function buildPrompt(string $fromLabel, string $toLabel, array $items): string
    {
        $json = json_encode(['items' => $items], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Eres un traductor experto en cartas de restaurante y hostelería.
Traduce del {$fromLabel} al {$toLabel} los textos del JSON siguiente.
Reglas:
- Devuelve SOLO JSON válido: {"items":[{"id":1,"type":"section|product","name":"...","description":"..."}]}
- Mantén el mismo id y type de cada elemento.
- Para type "section" solo traduce name (sin description).
- Para type "product" traduce name y description (description puede ser vacío).
- Conserva nombres propios de platos regionales cuando sean marca (ej. "pa amb tomàquet" puede quedar igual con descripción traducida).
- No traduzcas precios ni símbolos €.
- Tono natural para comensales internacionales en un restaurante.
- No añadas ni elimines elementos.

JSON:
{$json}
PROMPT;
    }

    /**
     * @param array<int, array<string, mixed>> $parts
     * @return array<string, mixed>|null
     */
    protected function callGenerateContent(string $apiKey, string $model, array $parts): ?array
    {
        $baseUrl = rtrim(config('menu_scan.gemini.base_url'), '/');
        $url = $baseUrl . '/models/' . $model . ':generateContent';

        $response = $this->client->post($url, [
            'headers' => ['x-goog-api-key' => $apiKey],
            'json' => [
                'contents' => [['parts' => $parts]],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'responseMimeType' => 'application/json',
                ],
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    /**
     * @param array<string, mixed>|null $body
     */
    protected function extractTextFromResponse(?array $body): ?string
    {
        if (! is_array($body)) {
            return null;
        }

        foreach ($body['candidates'] ?? [] as $candidate) {
            foreach ($candidate['content']['parts'] ?? [] as $part) {
                if (isset($part['text'])) {
                    return (string) $part['text'];
                }
            }
        }

        return null;
    }

    protected function extractJsonFromText(string $text): string
    {
        if (preg_match('/\{[\s\S]*"items"[\s\S]*\}/', $text, $matches)) {
            return $matches[0];
        }

        return $text;
    }
}
