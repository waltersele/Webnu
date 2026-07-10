<?php

namespace Tests\Feature;

use App\BlogCategory;
use App\BlogPost;
use App\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentConnectorTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'test-connector-secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['blog.connector.secret' => self::SECRET]);
    }

    public function test_health_returns_ok_without_signature(): void
    {
        $this->getJson('/api/content-connector/health')
            ->assertOk()
            ->assertJson(['status' => 'ok']);
    }

    public function test_posts_list_requires_valid_signature(): void
    {
        $this->getJson('/api/content-connector/posts')
            ->assertUnauthorized()
            ->assertJson(['message' => 'Invalid signature.']);
    }

    public function test_posts_list_returns_published_posts(): void
    {
        $categoryId = BlogCategory::query()->value('id');
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'blog_category_id' => $categoryId,
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'articulo-es',
            'title' => 'Artículo ES',
            'excerpt' => 'Resumen',
            'body' => '<p>Contenido</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
            'faq_schema' => $this->sampleFaqSchema(),
        ]);

        $response = $this->signedGet('/api/content-connector/posts');

        $response->assertOk()
            ->assertJsonPath('posts.0.id', (string) BlogPostTranslation::first()->id)
            ->assertJsonPath('posts.0.slug', 'articulo-es')
            ->assertJsonPath('posts.0.locale', 'es')
            ->assertJsonPath('posts.0.category_id', (string) $categoryId);
    }

    public function test_post_creates_and_publishes_article(): void
    {
        $payload = $this->connectorPayload([
            'title' => 'Hola Webnu',
            'content' => '<p>Primer post.</p>',
            'slug' => 'hola-webnu',
        ]);

        $response = $this->signedPost('/api/content-connector/posts', $payload);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'url'])
            ->assertJsonMissing(['status', 'locale']);

        $translation = BlogPostTranslation::where('slug', 'hola-webnu')->first();
        $response->assertJson([
            'id' => (string) $translation->id,
            'url' => $translation->publicUrl(),
        ]);

        $this->assertDatabaseHas('blog_post_translations', [
            'slug' => 'hola-webnu',
            'locale' => 'es',
            'title' => 'Hola Webnu',
        ]);

        $this->assertDatabaseHas('blog_posts', [
            'status' => BlogPost::STATUS_PUBLISHED,
            'blog_category_id' => (int) $payload['category_id'],
        ]);
    }

    public function test_post_creates_and_publishes_article_with_raw_hex_signature(): void
    {
        $payload = $this->connectorPayload([
            'title' => 'Sonartop post',
            'content' => '<p>Desde Sonartop.</p>',
            'slug' => 'sonartop-post',
        ]);

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            '/api/content-connector/posts',
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CONNECTOR_SIGNATURE' => $this->signRaw($body),
            ]),
            $body
        )->assertCreated()
            ->assertJsonStructure(['id', 'url']);
    }

    public function test_put_updates_existing_post_without_duplicate(): void
    {
        $createPayload = $this->connectorPayload([
            'title' => 'Original',
            'content' => '<p>Original</p>',
            'slug' => 'edit-me',
        ]);

        $createResponse = $this->signedPost('/api/content-connector/posts', $createPayload);
        $postId = $createResponse->json('id');

        $updatePayload = $this->connectorPayload([
            'title' => 'Actualizado',
            'content' => '<p>Actualizado</p>',
            'slug' => 'edit-me',
        ]);

        $this->signedPut("/api/content-connector/posts/{$postId}", $updatePayload)
            ->assertOk()
            ->assertJson([
                'id' => $postId,
            ]);

        $this->assertEquals(1, BlogPostTranslation::count());
        $this->assertDatabaseHas('blog_post_translations', [
            'id' => $postId,
            'title' => 'Actualizado',
            'slug' => 'edit-me',
        ]);
    }

    public function test_posts_list_accepts_raw_hex_signature(): void
    {
        $this->call(
            'GET',
            '/api/content-connector/posts',
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CONNECTOR_SIGNATURE' => $this->signRaw(''),
            ])
        )->assertOk();
    }

    public function test_post_rejects_invalid_signature(): void
    {
        $body = json_encode($this->connectorPayload([
            'slug' => 'test',
        ]), JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            '/api/content-connector/posts',
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CONNECTOR_SIGNATURE' => 'sha256=deadbeef',
            ]),
            $body
        )->assertUnauthorized();
    }

    public function test_group_id_links_translations_to_same_post(): void
    {
        $base = $this->connectorPayload([
            'title' => 'ES title',
            'content' => '<p>ES</p>',
            'slug' => 'mismo-articulo-es',
            'meta' => ['group_id' => 'grp-100'],
        ]);

        $this->signedPost('/api/content-connector/posts', $base)->assertCreated();

        $this->signedPost('/api/content-connector/posts', $this->connectorPayload([
            'title' => 'EN title',
            'content' => '<p>EN</p>',
            'slug' => 'same-article-en',
            'locale' => 'en',
            'meta' => ['group_id' => 'grp-100'],
        ]))->assertCreated();

        $this->assertEquals(1, BlogPost::where('connector_group_id', 'grp-100')->count());
        $this->assertEquals(2, BlogPostTranslation::count());
    }

    public function test_categories_list_returns_configured_categories(): void
    {
        $response = $this->signedGet('/api/content-connector/categories');

        $response->assertOk()
            ->assertJsonStructure(['categories' => [['id', 'name']]])
            ->assertJsonFragment(['name' => 'Cartas digitales']);
    }

    public function test_post_requires_category_id_and_faq_schema(): void
    {
        $this->signedPost('/api/content-connector/posts', [
            'title' => 'Incompleto',
            'content' => '<p>Ok</p>',
            'slug' => 'incompleto',
            'locale' => 'es',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['category_id', 'faq_schema']);
    }

    public function test_post_assigns_category_and_faq_schema(): void
    {
        $categoryId = (string) BlogCategory::query()->value('id');
        $faqSchema = $this->sampleFaqSchema('¿Pregunta demo?');

        $payload = $this->connectorPayload([
            'title' => 'Con categoría',
            'content' => '<p>Texto limpio.</p>',
            'slug' => 'con-categoria',
            'category_id' => $categoryId,
            'faq_schema' => $faqSchema,
        ]);

        $this->signedPost('/api/content-connector/posts', $payload)->assertCreated();

        $translation = BlogPostTranslation::where('slug', 'con-categoria')->firstOrFail();

        $this->assertSame((int) $categoryId, $translation->post->blog_category_id);
        $this->assertSame('¿Pregunta demo?', $translation->faq_schema['mainEntity'][0]['name']);
    }

    public function test_post_strips_embedded_faq_script_from_content(): void
    {
        $payload = $this->connectorPayload([
            'title' => 'Sin JSON visible',
            'content' => '<p>Intro</p><script type="application/ld+json">{"@type":"FAQPage"}</script>',
            'slug' => 'sin-json-visible',
        ]);

        $this->signedPost('/api/content-connector/posts', $payload)->assertCreated();

        $translation = BlogPostTranslation::where('slug', 'sin-json-visible')->firstOrFail();

        $this->assertSame('<p>Intro</p>', $translation->body);
        $this->assertNotNull($translation->faq_schema);
    }

    public function test_unknown_category_id_is_rejected(): void
    {
        $payload = $this->connectorPayload([
            'title' => 'Sin categoría válida',
            'slug' => 'sin-categoria-valida',
            'category_id' => '999999',
        ]);

        $this->signedPost('/api/content-connector/posts', $payload)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['category_id']);
    }

    /** @return array<string, mixed> */
    private function connectorPayload(array $overrides = []): array
    {
        $categoryId = (string) BlogCategory::query()->value('id');

        return array_replace_recursive([
            'title' => 'Test',
            'content' => '<p>Content</p>',
            'slug' => 'test-' . uniqid(),
            'locale' => 'es',
            'category_id' => $categoryId,
            'faq_schema' => $this->sampleFaqSchema(),
        ], $overrides);
    }

    /** @return array<string, mixed> */
    private function sampleFaqSchema(string $question = '¿Pregunta?'): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => [
                [
                    '@type' => 'Question',
                    'name' => $question,
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Respuesta.',
                    ],
                ],
            ],
        ];
    }

    private function signedGet(string $uri)
    {
        return $this->call(
            'GET',
            $uri,
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CONNECTOR_SIGNATURE' => $this->sign(''),
            ])
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function signedPut(string $uri, array $payload)
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        return $this->call(
            'PUT',
            $uri,
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CONNECTOR_SIGNATURE' => $this->sign($body),
            ]),
            $body
        );
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function signedPost(string $uri, array $payload)
    {
        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        return $this->call(
            'POST',
            $uri,
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CONNECTOR_SIGNATURE' => $this->sign($body),
            ]),
            $body
        );
    }

    private function sign(string $body): string
    {
        return 'sha256=' . $this->signRaw($body);
    }

    private function signRaw(string $body): string
    {
        return hash_hmac('sha256', $body, self::SECRET);
    }
}
