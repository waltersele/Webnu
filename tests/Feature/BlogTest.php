<?php

namespace Tests\Feature;

use App\BlogPost;
use App\BlogPostTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_redirects_to_default_locale(): void
    {
        $this->withHeaders(['Accept-Language' => 'es'])
            ->get('/blog')
            ->assertRedirect(route('blog.index', ['locale' => 'es']));
    }

    public function test_blog_redirect_honors_accept_language(): void
    {
        $this->withHeaders(['Accept-Language' => 'en'])
            ->get('/blog')
            ->assertRedirect(route('blog.index', ['locale' => 'en']));
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
        $this->get('/de/blog')->assertNotFound();
    }
}
