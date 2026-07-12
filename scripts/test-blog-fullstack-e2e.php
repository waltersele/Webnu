<?php

/**
 * E2E full-stack blog + Content Connector usando el secreto real de la app.
 * Uso: php scripts/test-blog-fullstack-e2e.php [base_url]
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$baseUrl = rtrim($argv[1] ?? getenv('WEBNU_BASE_URL') ?: 'http://127.0.0.1:8765', '/');
$secret = (string) (config('blog.connector.secret') ?? '');

if ($secret === '') {
    fwrite(STDERR, "ERROR: Content Connector sin secreto (Admin → Plataforma → Configuración o CONTENT_CONNECTOR_SECRET en .env)\n");
    exit(2);
}

$results = [];
$failed = 0;
$slug = null;
$categorySlug = null;

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
        CURLOPT_TIMEOUT => 45,
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

fwrite(STDOUT, "=== E2E FULL-STACK BLOG + SONARTOP ===\n");
fwrite(STDOUT, "Base: $baseUrl\n");
fwrite(STDOUT, 'Secreto: ' . (strlen($secret) >= 8 ? substr($secret, 0, 4) . '…' : '(corto)') . "\n\n");

// --- API Content Connector ---
$r = request('GET', $baseUrl . '/api/content-connector/health', null, null);
$health = json_decode($r['body'], true);
check('API health → 200 ok', $r['status'] === 200 && ($health['status'] ?? null) === 'ok', 'HTTP ' . $r['status']);

$r = request('GET', $baseUrl . '/api/content-connector/categories', null, sign('', $secret));
$categoriesPayload = json_decode($r['body'], true);
$categories = $categoriesPayload['categories'] ?? [];
check('API categories → 200 + slug', $r['status'] === 200 && count($categories) > 0 && isset($categories[0]['slug']), count($categories) . ' cats');
$categoryId = (string) ($categories[0]['id'] ?? '');
$categorySlug = (string) ($categories[0]['slug'] ?? '');

$slug = 'e2e-fullstack-' . bin2hex(random_bytes(4));
$faqSchema = [
    '@context' => 'https://schema.org',
    '@type' => 'FAQPage',
    'mainEntity' => [[
        '@type' => 'Question',
        'name' => '¿E2E full-stack OK?',
        'acceptedAnswer' => ['@type' => 'Answer', 'text' => 'Sí, blog + conector verificados.'],
    ]],
];
$postBody = json_encode([
    'title' => 'E2E Full Stack ' . date('Y-m-d H:i:s'),
    'content' => '<p>Contenido E2E full-stack.</p>',
    'slug' => $slug,
    'locale' => 'es',
    'status' => 'published',
    'published_at' => gmdate('c', time() - 3600),
    'excerpt' => 'Resumen E2E',
    'meta_title' => 'SEO E2E Full',
    'meta_description' => 'Meta E2E',
    'focus_keyword' => 'cartas qr',
    'category_id' => $categoryId,
    'faq_schema' => $faqSchema,
], JSON_UNESCAPED_UNICODE);

$r = request('POST', $baseUrl . '/api/content-connector/posts', $postBody, sign($postBody, $secret));
$created = json_decode($r['body'], true);
$postId = (string) ($created['id'] ?? '');
check('API POST → 201 id+url', $r['status'] === 201 && $postId !== '', 'HTTP ' . $r['status'] . ' id=' . $postId);

$r = request('GET', $baseUrl . '/api/content-connector/posts', null, sign('', $secret));
$postsPayload = json_decode($r['body'], true);
$found = null;
foreach ($postsPayload['posts'] ?? [] as $row) {
    if (($row['slug'] ?? null) === $slug) {
        $found = $row;
        break;
    }
}
check('API GET /posts sync', $found !== null && ($found['status'] ?? '') === 'published', 'status=' . ($found['status'] ?? ''));

$putBody = json_encode([
    'title' => 'E2E Full Stack actualizado',
    'content' => '<p>Contenido actualizado full-stack.</p>',
    'slug' => $slug,
    'locale' => 'es',
    'status' => 'published',
    'published_at' => gmdate('c', time() - 3600),
    'category_id' => $categoryId,
    'faq_schema' => $faqSchema,
], JSON_UNESCAPED_UNICODE);
$r = request('PUT', $baseUrl . '/api/content-connector/posts/' . $postId, $putBody, sign($putBody, $secret));
check('API PUT → 200', $r['status'] === 200, 'HTTP ' . $r['status']);

// --- Front público ---
$articleUrl = $baseUrl . '/es/blog/' . $slug;
$r = request('GET', $articleUrl, null, null);
$html = $r['body'];
check('Front artículo → 200', $r['status'] === 200, $articleUrl);
check('Front título actualizado', str_contains($html, 'E2E Full Stack actualizado'));
check('Front breadcrumbs', str_contains($html, 'wn-blog-breadcrumbs'));
check('Front sidebar', str_contains($html, 'wn-blog-sidebar'));
check('Front Últimos artículos', str_contains($html, 'Últimos artículos') || str_contains($html, 'Latest articles'));
check('Front FAQ acordeón', str_contains($html, '¿E2E full-stack OK?'));
check('Front BreadcrumbList JSON-LD', str_contains($html, 'BreadcrumbList'));
check('Front BlogPosting JSON-LD', str_contains($html, 'BlogPosting'));
check('Front og:type article', str_contains($html, 'og:type') && str_contains($html, 'article'));

if ($categorySlug !== '') {
    $catUrl = $baseUrl . '/es/blog/categoria/' . $categorySlug;
    $r = request('GET', $catUrl, null, null);
    check('Front archivo categoría → 200', $r['status'] === 200, $catUrl);
    check('Front categoría lista artículo E2E', str_contains($r['body'], 'E2E Full Stack actualizado'));
}

$r = request('GET', $baseUrl . '/es/blog', null, null);
check('Front índice blog → 200', $r['status'] === 200);
check('Front índice incluye artículo', str_contains($r['body'], 'E2E Full Stack actualizado'));

$r = request('GET', $baseUrl . '/blog', null, null);
check('Front hub /blog → 200', $r['status'] === 200);

fwrite(STDOUT, PHP_EOL . '--- RESUMEN ---' . PHP_EOL);
fwrite(STDOUT, sprintf("Checks: %d OK, %d FAIL\n", count($results) - $failed, $failed));
if ($slug) {
    fwrite(STDOUT, "Artículo de prueba: $articleUrl\n");
}

exit($failed > 0 ? 1 : 0);
