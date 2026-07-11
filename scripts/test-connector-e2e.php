<?php

/**
 * E2E Content Connector + blog público (simula flujo Sonartop).
 * Uso: php scripts/test-connector-e2e.php [base_url]
 */

declare(strict_types=1);

$baseUrl = rtrim($argv[1] ?? getenv('WEBNU_BASE_URL') ?: 'http://127.0.0.1:8000', '/');
$secret = getenv('CONTENT_CONNECTOR_SECRET') ?: 'test-connector-secret';

$results = [];
$failed = 0;

function sign(string $body, string $secret): string
{
    return hash_hmac('sha256', $body, $secret);
}

function request(string $method, string $url, ?string $body, ?string $signature): array
{
    $ch = curl_init($url);
    $headers = ['Accept: application/json'];
    if ($body !== null) {
        $headers[] = 'Content-Type: application/json';
    }
    if ($signature !== null) {
        $headers[] = 'X-Connector-Signature: ' . $signature;
    }

    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_POSTFIELDS => $body,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status' => $status,
        'body' => is_string($response) ? $response : '',
    ];
}

function check(string $label, bool $ok, string $detail = ''): void
{
    global $results, $failed;
    $results[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail];
    if (! $ok) {
        $failed++;
    }
    $icon = $ok ? 'PASS' : 'FAIL';
    fwrite(STDOUT, sprintf("[%s] %s%s\n", $icon, $label, $detail !== '' ? ' — ' . $detail : ''));
}

$r = request('GET', $baseUrl . '/api/content-connector/health', null, null);
$health = json_decode($r['body'], true);
check('GET /health → 200 ok', $r['status'] === 200 && ($health['status'] ?? null) === 'ok', 'HTTP ' . $r['status']);

$r = request('GET', $baseUrl . '/api/content-connector/categories', null, sign('', $secret));
$categoriesPayload = json_decode($r['body'], true);
$categories = $categoriesPayload['categories'] ?? [];
check('GET /categories → 200 con lista', $r['status'] === 200 && count($categories) > 0, count($categories) . ' categorías');
$categoryId = (string) ($categories[0]['id'] ?? '');

$r = request('POST', $baseUrl . '/api/content-connector/posts', '{}', null);
check('POST /posts sin firma → 401', $r['status'] === 401, 'HTTP ' . $r['status']);

$badBody = json_encode([
    'title' => 'Test',
    'content' => '<p>x</p>',
    'slug' => 'e2e-incomplete',
    'locale' => 'es',
], JSON_UNESCAPED_UNICODE);
$r = request('POST', $baseUrl . '/api/content-connector/posts', $badBody, sign($badBody, $secret));
check('POST sin status/published_at → 422', $r['status'] === 422, 'HTTP ' . $r['status']);

$slug = 'e2e-sonartop-' . bin2hex(random_bytes(4));
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [
        [
            '@type' => 'Question',
            'name' => '¿Funciona el conector E2E?',
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => 'Sí, publicación completa verificada.',
            ],
        ],
    ],
];
$postBody = json_encode([
    'title' => 'Test E2E Sonartop ' . date('Y-m-d H:i:s'),
    'content' => '<p>Párrafo E2E.</p><script type="application/ld+json">{"@type":"FAQPage"}</script>',
    'slug' => $slug,
    'locale' => 'es',
    'status' => 'published',
    'published_at' => gmdate('c', time() - 3600),
    'excerpt' => 'Resumen E2E',
    'meta_title' => 'SEO E2E',
    'meta_description' => 'Descripción SEO E2E',
    'focus_keyword' => 'cartas qr',
    'category_id' => $categoryId,
    'faq_schema' => $faqSchema,
], JSON_UNESCAPED_UNICODE);
$r = request('POST', $baseUrl . '/api/content-connector/posts', $postBody, sign($postBody, $secret));
$created = json_decode($r['body'], true);
$postId = (string) ($created['id'] ?? '');
check('POST artículo completo → 201 id+url', $r['status'] === 201 && $postId !== '', 'id=' . $postId);

$r = request('GET', $baseUrl . '/api/content-connector/posts', null, sign('', $secret));
$postsPayload = json_decode($r['body'], true);
$found = null;
foreach ($postsPayload['posts'] ?? [] as $row) {
    if (($row['slug'] ?? null) === $slug) {
        $found = $row;
        break;
    }
}
check('GET /posts incluye status y category_id', $found !== null && ($found['status'] ?? '') === 'published', 'category_id=' . ($found['category_id'] ?? 'null'));

$putBody = json_encode([
    'title' => 'Test E2E actualizado',
    'content' => '<p>Contenido actualizado.</p>',
    'slug' => $slug,
    'locale' => 'es',
    'status' => 'published',
    'published_at' => gmdate('c', time() - 3600),
    'category_id' => $categoryId,
    'faq_schema' => $faqSchema,
], JSON_UNESCAPED_UNICODE);
$r = request('PUT', $baseUrl . '/api/content-connector/posts/' . $postId, $putBody, sign($putBody, $secret));
check('PUT /posts/{id} → 200', $r['status'] === 200, 'HTTP ' . $r['status']);

$html = request('GET', $baseUrl . '/es/blog/' . $slug, null, null)['body'];
check('Artículo público con título actualizado', str_contains($html, 'Test E2E actualizado'));
check('FAQ JSON-LD en head', str_contains($html, 'application/ld+json') && str_contains($html, '¿Funciona el conector E2E?'));

fwrite(STDOUT, PHP_EOL . '--- RESUMEN E2E ---' . PHP_EOL);
fwrite(STDOUT, sprintf("Base URL: %s\n", $baseUrl));
fwrite(STDOUT, sprintf("Checks: %d OK, %d FAIL\n", count($results) - $failed, $failed));

exit($failed > 0 ? 1 : 0);
