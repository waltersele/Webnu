<?php

namespace Tests\Feature;

use App\BlogCategory;
use App\BlogPost;
use App\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_hub_returns_200_with_default_locale_content(): void
    {
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'hub-post',
            'title' => 'Post hub',
            'body' => '<p>ES</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
        ]);

        $this->withHeaders(['Accept-Language' => 'en'])
            ->get('/blog')
            ->assertOk()
            ->assertSee('Post hub');
    }

    public function test_blog_index_lists_published_posts(): void
    {
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'mi-post',
            'title' => 'Mi post',
            'excerpt' => 'Resumen',
            'body' => '<p>Contenido</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
        ]);

        $this->get('/es/blog')
            ->assertOk()
            ->assertSee('Mi post');
    }

    public function test_blog_show_displays_article(): void
    {
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'en',
            'slug' => 'hello-webnu',
            'title' => 'Hello Webnu',
            'excerpt' => 'Summary',
            'body' => '<p>Hello world</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
        ]);

        $this->get('/en/blog/hello-webnu')
            ->assertOk()
            ->assertSee('Hello Webnu')
            ->assertSee('Hello world', false);
    }

    public function test_blog_show_renders_faq_schema_in_head(): void
    {
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'faq-head',
            'title' => 'FAQ head',
            'body' => '<p>Solo contenido.</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
            'faq_schema' => [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => [
                    [
                        '@type' => 'Question',
                        'name' => '¿Pregunta SEO?',
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => 'Respuesta SEO.',
                        ],
                    ],
                ],
            ],
        ]);

        $response = $this->get('/es/blog/faq-head');

        $response->assertOk()
            ->assertSee('application/ld+json', false)
            ->assertSee('¿Pregunta SEO?', false)
            ->assertSee('Solo contenido.', false);
    }

    public function test_draft_posts_are_not_public(): void
    {
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_DRAFT,
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'borrador',
            'title' => 'Borrador',
            'body' => '<p>No visible</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
        ]);

        $this->get('/es/blog/borrador')->assertNotFound();
    }

    public function test_invalid_blog_locale_returns_404(): void
    {
        $this->get('/xx/blog')->assertNotFound();
    }

    public function test_category_archive_lists_posts(): void
    {
        $category = BlogCategory::firstOrCreate(
            ['slug' => 'cartas-digitales-archive'],
            ['name' => 'Cartas digitales archive']
        );

        $post = BlogPost::create([
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
            'blog_category_id' => $category->id,
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'en-categoria',
            'title' => 'En categoría',
            'body' => '<p>Contenido</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
        ]);

        $this->get('/es/blog/categoria/cartas-digitales-archive')
            ->assertOk()
            ->assertSee('En categoría')
            ->assertSee('Cartas digitales archive');
    }

    public function test_blog_show_renders_faq_accordion(): void
    {
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->subHour(),
        ]);

        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'faq-ui',
            'title' => 'FAQ UI',
            'body' => '<p>Contenido</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
            'faq_schema' => [
                '@type' => 'FAQPage',
                'mainEntity' => [[
                    '@type' => 'Question',
                    'name' => '¿Visible en UI?',
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => 'Sí, en acordeón.',
                    ],
                ]],
            ],
        ]);

        $this->get('/es/blog/faq-ui')
            ->assertOk()
            ->assertSee('Preguntas frecuentes')
            ->assertSee('¿Visible en UI?')
            ->assertSee('Sí, en acordeón.');
    }
}
