<?php

namespace App\Services\MenuScan;

use App\Contracts\MenuScanProvider;
use App\PlatformSetting;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class GeminiMenuScanProvider implements MenuScanProvider
{
    /** @var Client */
    protected $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? new Client(self::httpClientOptions());
    }

    /**
     * @return array<string, mixed>
     */
    public static function httpClientOptions(): array
    {
        $options = [
            'timeout' => config('menu_scan.gemini.timeout', 90),
            'connect_timeout' => 15,
        ];

        $verifySsl = filter_var(config('menu_scan.gemini.verify_ssl', true), FILTER_VALIDATE_BOOLEAN);
        if (! $verifySsl) {
            $options['verify'] = false;

            return $options;
        }

        $caBundle = config('menu_scan.gemini.ca_bundle');
        if ($caBundle && is_readable($caBundle)) {
            $options['verify'] = $caBundle;
        }

        return $options;
    }

    public function name(): string
    {
        return 'gemini';
    }

    /**
     * Prueba rápida de API key y modelo (superadmin).
     *
     * @return array{ok: bool, message: string}
     */
    public static function testConnection(?string $apiKey = null, ?string $model = null): array
    {
        $apiKey = trim($apiKey ?? (string) PlatformSetting::geminiApiKey());
        $model = PlatformSetting::resolveGeminiModel(trim($model ?? PlatformSetting::geminiModel()));

        if ($apiKey === '') {
            return ['ok' => false, 'message' => 'No hay API key configurada.'];
        }

        try {
            $client = new Client(self::httpClientOptions());
            $baseUrl = rtrim(config('menu_scan.gemini.base_url'), '/');
            $payload = [
                'headers' => ['x-goog-api-key' => $apiKey],
                'json' => [
                    'contents' => [['parts' => [['text' => 'Responde solo: OK']]]],
                ],
            ];

            $lastError = null;
            foreach (PlatformSetting::geminiModelsToTry($model) as $candidate) {
                try {
                    $response = $client->post($baseUrl . '/models/' . $candidate . ':generateContent', $payload);
                    $code = $response->getStatusCode();
                    if ($code >= 200 && $code < 300) {
                        $msg = 'Conexión correcta con el modelo «' . $candidate . '».';
                        if ($candidate !== $model) {
                            $msg .= ' (sustituye a «' . $model . '», que no responde en tu cuenta).';
                        }

                        return ['ok' => true, 'message' => $msg];
                    }
                } catch (ClientException $e) {
                    $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
                    if (self::shouldTryNextModel($status)) {
                        $lastError = $e;
                        continue;
                    }
                    throw $e;
                }
            }

            if ($lastError) {
                throw $lastError;
            }

            return ['ok' => false, 'message' => 'Ningún modelo disponible respondió.'];
        } catch (GuzzleException $e) {
            $provider = new self();
            [$message] = $provider->parseApiError($e);

            return ['ok' => false, 'message' => $message];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function scan(array $absoluteFilePaths): MenuScanResult
    {
        $apiKey = PlatformSetting::geminiApiKey();
        if (! $apiKey) {
            return MenuScanResult::failed('El escaneo con IA no está configurado. El administrador debe añadir la API de Gemini en Plataforma → Escaneo IA.', $this->name());
        }

        if (count($absoluteFilePaths) === 0) {
            return MenuScanResult::failed('No hay archivos para analizar.', $this->name());
        }

        try {
            $parts = $this->buildContentParts($absoluteFilePaths, $apiKey);
            if ($parts === null) {
                return MenuScanResult::failed('No se pudieron leer las imágenes o el PDF.', $this->name());
            }

            $body = $this->generateContentWithFallback($apiKey, PlatformSetting::geminiModel(), $parts, true);

            if ($body === null) {
                $body = $this->generateContentWithFallback($apiKey, PlatformSetting::geminiModel(), $parts, false);
            }

            if ($body === null) {
                return MenuScanResult::failed(
                    'Gemini no respondió tras probar varios modelos y reintentos. Espera 1–2 minutos y vuelve a intentarlo.',
                    $this->name(),
                    'server_error'
                );
            }

            $blockReason = $body['promptFeedback']['blockReason'] ?? null;
            if ($blockReason) {
                return MenuScanResult::failed('Gemini bloqueó el contenido (' . $blockReason . '). Prueba otra foto más clara.', $this->name());
            }

            $text = $this->extractTextFromResponse($body);
            if ($text === null) {
                return MenuScanResult::failed('Gemini no devolvió texto. Prueba con otra imagen o cambia el modelo.', $this->name());
            }

            $decoded = json_decode($text, true);
            if (! is_array($decoded)) {
                $decoded = json_decode($this->extractJsonFromText($text), true);
            }

            if (! is_array($decoded) || ! isset($decoded['sections'])) {
                return MenuScanResult::failed('La respuesta de Gemini no tiene el formato esperado. Prueba gemini-2.0-flash en Plataforma → Escaneo IA.', $this->name());
            }

            $result = MenuScanResult::fromSections($decoded['sections'], $this->name());
            if (! $result->isSuccess()) {
                return MenuScanResult::failed('No se detectaron platos en la carta.', $this->name());
            }

            return $result;
        } catch (GuzzleException $e) {
            Log::warning('Gemini menu scan failed', [
                'message' => $this->sanitizeForLog($e->getMessage()),
                'provider' => $this->name(),
            ]);

            return $this->failedFromException($e);
        } catch (\Throwable $e) {
            Log::warning('Gemini menu scan error', [
                'message' => $e->getMessage(),
                'provider' => $this->name(),
            ]);

            return MenuScanResult::failed('Error al procesar la carta. Inténtalo de nuevo.', $this->name());
        }
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    protected function buildContentParts(array $absoluteFilePaths, string $apiKey): ?array
    {
        $parts = [];
        foreach ($absoluteFilePaths as $path) {
            if (! is_readable($path)) {
                continue;
            }
            $mime = mime_content_type($path) ?: 'application/octet-stream';
            if ($mime === 'application/pdf') {
                $filePart = $this->uploadPdfPart($path, $apiKey);
                if ($filePart === null) {
                    return null;
                }
                $parts[] = $filePart;
            } elseif (strpos($mime, 'image/') === 0) {
                $parts[] = [
                    'inline_data' => [
                        'mime_type' => $mime,
                        'data' => base64_encode((string) file_get_contents($path)),
                    ],
                ];
            }
        }

        if (count($parts) === 0) {
            return null;
        }

        $parts[] = ['text' => $this->buildPrompt()];

        return $parts;
    }

    /**
     * @param array<int, array<string, mixed>> $parts
     * @return array<string, mixed>|null
     */
    protected function generateContentWithFallback(string $apiKey, string $preferredModel, array $parts, bool $withSchema): ?array
    {
        foreach (PlatformSetting::geminiModelsToTry($preferredModel) as $model) {
            try {
                $body = $this->callGenerateContent($apiKey, $model, $parts, $withSchema);
                if ($body !== null) {
                    return $body;
                }
            } catch (ClientException $e) {
                $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
                if (self::shouldTryNextModel($status)) {
                    Log::info('Gemini model unavailable, trying next', ['model' => $model, 'status' => $status]);

                    continue;
                }
                throw $e;
            }
        }

        return null;
    }

    /**
     * @param array<int, array<string, mixed>> $parts
     * @return array<string, mixed>|null
     */
    protected function callGenerateContent(string $apiKey, string $model, array $parts, bool $withSchema): ?array
    {
        $baseUrl = rtrim(config('menu_scan.gemini.base_url'), '/');
        $url = $baseUrl . '/models/' . $model . ':generateContent';

        $generationConfig = [
            'temperature' => 0.2,
        ];
        if ($withSchema) {
            $generationConfig['responseMimeType'] = 'application/json';
            $generationConfig['responseSchema'] = $this->responseSchema();
        } else {
            $generationConfig['responseMimeType'] = 'application/json';
        }

        $maxRetries = max(1, (int) config('menu_scan.gemini.max_retries', 3));
        $delayMs = max(200, (int) config('menu_scan.gemini.retry_delay_ms', 1000));
        $payload = [
            'headers' => $this->authHeaders($apiKey),
            'json' => [
                'contents' => [['parts' => $parts]],
                'generationConfig' => $generationConfig,
            ],
        ];

        $lastException = null;
        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                $response = $this->client->post($url, $payload);

                return json_decode((string) $response->getBody(), true);
            } catch (ClientException $e) {
                $lastException = $e;
                if ($withSchema && $e->hasResponse() && $e->getResponse()->getStatusCode() === 400) {
                    return null;
                }
                $status = $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0;
                if (! self::isRetryableHttpStatus($status) || $attempt >= $maxRetries - 1) {
                    throw $e;
                }
                usleep($delayMs * 1000 * ($attempt + 1));
            }
        }

        if ($lastException) {
            throw $lastException;
        }

        return null;
    }

    protected static function isRetryableHttpStatus(int $status): bool
    {
        return in_array($status, [429, 500, 502, 503, 504], true);
    }

    protected static function shouldTryNextModel(int $status): bool
    {
        return in_array($status, [404, 400], true) || self::isRetryableHttpStatus($status);
    }

    /**
     * @return array<string, string>
     */
    protected function authHeaders(string $apiKey): array
    {
        return ['x-goog-api-key' => $apiKey];
    }

    protected function failedFromException(GuzzleException $e): MenuScanResult
    {
        [$message, $code] = $this->parseApiError($e);

        return MenuScanResult::failed($message, $this->name(), $code);
    }

    /**
     * @return array{0: string, 1: string|null}
     */
    public function parseApiError(GuzzleException $e): array
    {
        if ($e instanceof ConnectException) {
            return [
                'Error de red al conectar con Gemini (SSL o sin internet). Usa run-local.ps1 o configura MENU_SCAN_CA_BUNDLE en .env.',
                'connection_error',
            ];
        }

        if ($e instanceof RequestException && $e->hasResponse()) {
            $status = $e->getResponse()->getStatusCode();
            $body = json_decode((string) $e->getResponse()->getBody(), true);
            $apiMessage = is_array($body) ? ($body['error']['message'] ?? $body['error']['status'] ?? null) : null;
            $apiMessage = $apiMessage ? $this->sanitizeForLog((string) $apiMessage) : null;

            if ($status === 401 || $status === 403) {
                return [
                    'API key de Gemini inválida o sin permiso. Crea una nueva clave en Google AI Studio y actualízala en Plataforma → Escaneo IA.',
                    'auth_error',
                ];
            }
            if ($status === 404) {
                return [
                    'Modelo no disponible en Gemini. En Plataforma → Escaneo IA elige gemini-2.5-flash-lite y pulsa «Probar conexión».',
                    'model_not_found',
                ];
            }
            if ($status >= 500 || $status === 429) {
                return [
                    'Gemini está saturado (HTTP ' . $status . '). Ya se probaron otros modelos y reintentos; espera 1–2 minutos y vuelve a intentarlo.',
                    $status === 429 ? 'quota_exceeded' : 'server_error',
                ];
            }
            if ($apiMessage) {
                return [
                    'Gemini rechazó la petición (HTTP ' . $status . '): ' . $apiMessage,
                    'api_error',
                ];
            }

            return [
                'Error de Gemini (HTTP ' . $status . '). Revisa la API key y el modelo en Plataforma → Escaneo IA.',
                'api_error',
            ];
        }

        $hint = $this->sanitizeForLog($e->getMessage());
        if (stripos($hint, 'SSL') !== false || stripos($hint, 'certificate') !== false) {
            return [
                'Error SSL al conectar con Gemini. Reinicia con run-local.ps1 o añade MENU_SCAN_CA_BUNDLE en .env.',
                'connection_error',
            ];
        }

        return [
            'No se pudo conectar con Gemini. ' . ($hint !== '' ? '(' . mb_substr($hint, 0, 120) . ')' : 'Comprueba la API key en Plataforma → Escaneo IA.'),
            'connection_error',
        ];
    }

    protected function sanitizeForLog(string $text): string
    {
        return (string) preg_replace('/AIza[a-zA-Z0-9_-]{20,}/', '[API_KEY]', $text);
    }

    protected function extractJsonFromText(string $text): string
    {
        if (preg_match('/\{[\s\S]*"sections"[\s\S]*\}/', $text, $m)) {
            return $m[0];
        }

        return $text;
    }

    protected function buildPrompt(): string
    {
        return <<<'PROMPT'
Eres un asistente que digitaliza cartas de restaurante en español.
Devuelve SOLO un JSON válido con esta estructura: {"sections":[{"name":"Nombre sección","products":[{"name":"Plato","description":"","price_unit":"12,50","price_portion":"","allergens":[]}]}]}
Extrae TODAS las secciones y platos visibles. Precios en formato español (12,50) sin €.
En "allergens" incluye solo alérgenos que aparezcan explícitos en la carta (iconos, leyenda o texto): Gluten, Crustáceos, Huevos, Pescados, Cacahuetes, Soja, Lácteos, Frutos secos, Apio, Mostaza, Sésamo, Sulfitos, Altramuz, Moluscos. Array vacío si no hay información.
No inventes platos ni alérgenos. Si no hay precio claro, price_unit vacío.
PROMPT;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function uploadPdfPart(string $absolutePath, string $apiKey): ?array
    {
        $uploadUrl = 'https://generativelanguage.googleapis.com/upload/v1beta/files';
        $displayName = basename($absolutePath);

        try {
            $response = $this->client->post($uploadUrl, [
                'headers' => array_merge($this->authHeaders($apiKey), [
                    'X-Goog-Upload-Protocol' => 'multipart',
                ]),
                'multipart' => [
                    [
                        'name' => 'metadata',
                        'contents' => json_encode(['file' => ['display_name' => $displayName]]),
                        'headers' => ['Content-Type' => 'application/json'],
                    ],
                    [
                        'name' => 'file',
                        'contents' => fopen($absolutePath, 'rb'),
                        'filename' => $displayName,
                    ],
                ],
            ]);

            $data = json_decode((string) $response->getBody(), true);
            $file = $data['file'] ?? null;
            if (! is_array($file)) {
                return null;
            }

            $uri = $file['uri'] ?? null;
            $mime = $file['mimeType'] ?? $file['mime_type'] ?? 'application/pdf';
            if (! $uri) {
                return null;
            }

            $this->waitForFileActive($uri, $apiKey);

            return [
                'file_data' => [
                    'mime_type' => $mime,
                    'file_uri' => $uri,
                ],
            ];
        } catch (\Throwable $e) {
            Log::warning('Gemini PDF upload failed', ['message' => $this->sanitizeForLog($e->getMessage())]);

            return null;
        }
    }

    protected function waitForFileActive(string $fileUri, string $apiKey): void
    {
        $fileName = basename(parse_url($fileUri, PHP_URL_PATH) ?: $fileUri);
        $baseUrl = rtrim(config('menu_scan.gemini.base_url'), '/');
        $url = $baseUrl . '/' . $fileName;

        for ($i = 0; $i < 20; $i++) {
            try {
                $response = $this->client->get($url, ['headers' => $this->authHeaders($apiKey)]);
                $data = json_decode((string) $response->getBody(), true);
                $state = $data['state'] ?? $data['file']['state'] ?? null;
                if ($state === 'ACTIVE' || $state === null) {
                    return;
                }
                if ($state === 'FAILED') {
                    throw new \RuntimeException('El archivo PDF no se procesó en Gemini.');
                }
            } catch (GuzzleException $e) {
                // ignore polling errors
            }
            usleep(500000);
        }
    }

    /**
     * @param array<string, mixed>|null $body
     */
    protected function extractTextFromResponse(?array $body): ?string
    {
        if (! is_array($body)) {
            return null;
        }
        $candidates = $body['candidates'] ?? [];
        foreach ($candidates as $candidate) {
            $parts = $candidate['content']['parts'] ?? [];
            foreach ($parts as $part) {
                if (isset($part['text'])) {
                    return (string) $part['text'];
                }
            }
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    protected function responseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'sections' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string'],
                            'products' => [
                                'type' => 'array',
                                'items' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'name' => ['type' => 'string'],
                                        'description' => ['type' => 'string'],
                                        'price_unit' => ['type' => 'string'],
                                        'price_portion' => ['type' => 'string'],
                                        'allergens' => [
                                            'type' => 'array',
                                            'items' => ['type' => 'string'],
                                        ],
                                    ],
                                    'required' => ['name'],
                                ],
                            ],
                        ],
                        'required' => ['name', 'products'],
                    ],
                ],
            ],
            'required' => ['sections'],
        ];
    }
}
