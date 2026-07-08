<?php

namespace Tests\Feature;

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
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'articulo-es',
            'title' => 'Artículo ES',
            'excerpt' => 'Resumen',
            'body' => '<p>Contenido</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
        ]);

        $response = $this->signedGet('/api/content-connector/posts');

        $response->assertOk()
            ->assertJsonPath('posts.0.slug', 'articulo-es')
            ->assertJsonPath('posts.0.locale', 'es');
    }

    public function test_post_creates_and_publishes_article(): void
    {
        $payload = [
            'title' => 'Hola Webnu',
            'content' => '<p>Primer post.</p>',
            'slug' => 'hola-webnu',
            'locale' => 'es',
        ];

        $response = $this->signedPost('/api/content-connector/posts', $payload);

        $response->assertCreated()
            ->assertJson([
                'status' => 'published',
                'locale' => 'es',
            ]);

        $this->assertDatabaseHas('blog_post_translations', [
            'slug' => 'hola-webnu',
            'locale' => 'es',
            'title' => 'Hola Webnu',
        ]);

        $this->assertDatabaseHas('blog_posts', [
            'status' => BlogPost::STATUS_PUBLISHED,
        ]);
    }

    public function test_post_rejects_invalid_signature(): void
    {
        $body = json_encode([
            'title' => 'Test',
            'content' => '<p>x</p>',
            'slug' => 'test',
            'locale' => 'es',
        ], JSON_THROW_ON_ERROR);

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
        $base = [
            'title' => 'ES title',
            'content' => '<p>ES</p>',
            'slug' => 'mismo-articulo-es',
            'locale' => 'es',
            'meta' => ['group_id' => 'grp-100'],
        ];

        $this->signedPost('/api/content-connector/posts', $base)->assertCreated();

        $this->signedPost('/api/content-connector/posts', [
            'title' => 'EN title',
            'content' => '<p>EN</p>',
            'slug' => 'same-article-en',
            'locale' => 'en',
            'meta' => ['group_id' => 'grp-100'],
        ])->assertCreated();

        $this->assertEquals(1, BlogPost::where('connector_group_id', 'grp-100')->count());
        $this->assertEquals(2, BlogPostTranslation::count());
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
        return 'sha256=' . hash_hmac('sha256', $body, self::SECRET);
    }
}
