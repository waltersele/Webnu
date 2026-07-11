<?php

namespace Tests\Feature;

use App\BlogCategory;
use App\BlogPost;
use App\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
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

    public function test_posts_list_returns_posts_with_status(): void
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

        $this->signedGet('/api/content-connector/posts')
            ->assertOk()
            ->assertJsonPath('posts.0.id', (string) BlogPostTranslation::first()->id)
            ->assertJsonPath('posts.0.status', BlogPost::STATUS_PUBLISHED)
            ->assertJsonPath('posts.0.category_id', (string) $categoryId);
    }

    public function test_sonartop_signature_example(): void
    {
        config(['blog.connector.secret' => 'mi-secreto-compartido']);

        $body = '{"title":"Hola mundo","locale":"es"}';
        $signature = 'e016a6376c8235b2529074b8f346e13d328475abd309d44447aa36c73170ad16';

        $this->call(
            'POST',
            '/api/content-connector/posts',
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CONNECTOR_SIGNATURE' => $signature,
            ]),
            $body
        )->assertStatus(422);

        config(['blog.connector.secret' => self::SECRET]);
    }

    public function test_post_creates_and_publishes_article(): void
    {
        $payload = $this->connectorPayload([
            'title' => 'Hola Webnu',
            'content' => '<p>Primer post.</p>',
            'slug' => 'hola-webnu',
            'excerpt' => 'Resumen root',
            'meta_title' => 'SEO title',
            'meta_description' => 'SEO description',
            'focus_keyword' => 'cartas qr',
        ]);

        $response = $this->signedPost('/api/content-connector/posts', $payload);

        $response->assertCreated()
            ->assertJsonStructure(['id', 'url']);

        $translation = BlogPostTranslation::where('slug', 'hola-webnu')->firstOrFail();
        $this->assertSame('Resumen root', $translation->excerpt);
        $this->assertSame('SEO title', $translation->meta_title);
        $this->assertSame('cartas qr', $translation->focus_keyword);
        $this->assertSame(BlogPost::STATUS_PUBLISHED, $translation->post->status);
    }

    public function test_post_without_category_and_faq_succeeds(): void
    {
        $payload = $this->connectorPayload([
            'slug' => 'sin-categoria-faq',
            'category_id' => null,
            'faq_schema' => null,
        ]);
        unset($payload['category_id'], $payload['faq_schema']);

        $this->signedPost('/api/content-connector/posts', $payload)->assertCreated();

        $translation = BlogPostTranslation::where('slug', 'sin-categoria-faq')->firstOrFail();
        $this->assertNull($translation->post->blog_category_id);
        $this->assertNull($translation->faq_schema);
    }

    public function test_post_with_scheduled_status_is_not_public_yet(): void
    {
        $payload = $this->connectorPayload([
            'slug' => 'programado',
            'status' => 'scheduled',
            'published_at' => now()->addDay()->toIso8601String(),
        ]);

        $this->signedPost('/api/content-connector/posts', $payload)->assertCreated();

        $this->get('/es/blog/programado')->assertNotFound();

        $post = BlogPostTranslation::where('slug', 'programado')->firstOrFail()->post;
        $this->assertSame(BlogPost::STATUS_SCHEDULED, $post->status);
    }

    public function test_publish_scheduled_command_makes_post_visible(): void
    {
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_SCHEDULED,
            'published_at' => now()->subMinute(),
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'ya-programado',
            'title' => 'Ya programado',
            'body' => '<p>Hola</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
        ]);

        Artisan::call('blog:publish-scheduled');

        $post->refresh();
        $this->assertSame(BlogPost::STATUS_PUBLISHED, $post->status);
        $this->get('/es/blog/ya-programado')->assertOk();
    }

    public function test_post_stores_featured_image_from_base64(): void
    {
        $png = base64_encode(base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        ));

        $payload = $this->connectorPayload([
            'slug' => 'con-imagen',
            'featured_image_base64' => $png,
            'featured_image_mime' => 'image/png',
            'featured_image_alt' => 'Imagen de prueba',
        ]);

        $this->signedPost('/api/content-connector/posts', $payload)->assertCreated();

        $post = BlogPostTranslation::where('slug', 'con-imagen')->firstOrFail()->post;
        $this->assertNotNull($post->featured_image);
        $this->assertStringStartsWith('img/blog/', $post->featured_image);
        $this->assertSame('Imagen de prueba', $post->featured_image_alt);
    }

    public function test_put_updates_existing_post_without_duplicate(): void
    {
        $createPayload = $this->connectorPayload([
            'title' => 'Original',
            'slug' => 'edit-me',
        ]);

        $postId = $this->signedPost('/api/content-connector/posts', $createPayload)->json('id');

        $updatePayload = $this->connectorPayload([
            'title' => 'Actualizado',
            'slug' => 'edit-me',
        ]);
        unset($updatePayload['faq_schema']);

        $this->signedPut("/api/content-connector/posts/{$postId}", $updatePayload)
            ->assertOk()
            ->assertJson(['id' => $postId]);

        $translation = BlogPostTranslation::findOrFail($postId);
        $this->assertSame('Actualizado', $translation->title);
        $this->assertNotNull($translation->faq_schema);
    }

    public function test_post_creates_with_raw_hex_signature(): void
    {
        $payload = $this->connectorPayload(['slug' => 'sonartop-post']);
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
        )->assertCreated();
    }

    public function test_post_rejects_invalid_signature(): void
    {
        $body = json_encode($this->connectorPayload(['slug' => 'test']), JSON_THROW_ON_ERROR);

        $this->call(
            'POST',
            '/api/content-connector/posts',
            [],
            [],
            [],
            $this->transformHeadersToServerVars([
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT' => 'application/json',
                'HTTP_X_CONNECTOR_SIGNATURE' => 'deadbeef',
            ]),
            $body
        )->assertUnauthorized();
    }

    public function test_group_id_links_translations_to_same_post(): void
    {
        $this->signedPost('/api/content-connector/posts', $this->connectorPayload([
            'slug' => 'mismo-articulo-es',
            'group_id' => 'grp-100',
        ]))->assertCreated();

        $this->signedPost('/api/content-connector/posts', $this->connectorPayload([
            'slug' => 'same-article-en',
            'locale' => 'en',
            'group_id' => 'grp-100',
        ]))->assertCreated();

        $this->assertEquals(1, BlogPost::where('connector_group_id', 'grp-100')->count());
    }

    public function test_categories_list_returns_configured_categories(): void
    {
        $this->signedGet('/api/content-connector/categories')
            ->assertOk()
            ->assertJsonFragment(['name' => 'Cartas digitales']);
    }

    public function test_unknown_category_id_is_rejected(): void
    {
        $this->signedPost('/api/content-connector/posts', $this->connectorPayload([
            'slug' => 'bad-category',
            'category_id' => '999999',
        ]))->assertStatus(422);
    }

    public function test_post_strips_embedded_faq_script_from_content(): void
    {
        $payload = $this->connectorPayload([
            'slug' => 'sin-json-visible',
            'content' => '<p>Intro</p><script type="application/ld+json">{"@type":"FAQPage"}</script>',
        ]);

        $this->signedPost('/api/content-connector/posts', $payload)->assertCreated();

        $translation = BlogPostTranslation::where('slug', 'sin-json-visible')->firstOrFail();
        $this->assertSame('<p>Intro</p>', $translation->body);
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
            'status' => 'published',
            'published_at' => now()->subHour()->toIso8601String(),
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
                'HTTP_X_CONNECTOR_SIGNATURE' => $this->signRaw(''),
            ])
        );
    }

    /** @param array<string, mixed> $payload */
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
                'HTTP_X_CONNECTOR_SIGNATURE' => $this->signRaw($body),
            ]),
            $body
        );
    }

    /** @param array<string, mixed> $payload */
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
                'HTTP_X_CONNECTOR_SIGNATURE' => $this->signRaw($body),
            ]),
            $body
        );
    }

    private function signRaw(string $body): string
    {
        return hash_hmac('sha256', $body, self::SECRET);
    }
}
