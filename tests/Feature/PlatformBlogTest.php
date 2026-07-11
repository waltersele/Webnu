<?php

namespace Tests\Feature;

use App\BlogCategory;
use App\BlogPost;
use App\BlogPostTranslation;
use App\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlatformBlogTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(): User
    {
        config(['platform.super_admin_emails' => ['super@test.com']]);

        return User::create([
            'name' => 'Super Admin',
            'email' => 'super@test.com',
            'password' => Hash::make('password'),
            'onboarding_completed_at' => now(),
        ]);
    }

    public function test_superadmin_can_create_blog_post_with_category_and_faq(): void
    {
        $admin = $this->superAdmin();
        $category = BlogCategory::firstOrCreate(
            ['slug' => 'cartas-digitales-test'],
            ['name' => 'Cartas digitales test']
        );

        $response = $this->actingAs($admin)->post(route('admin.platform.blog.store'), [
            'blog_category_id' => $category->id,
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->format('Y-m-d\TH:i'),
            'translations' => [
                'es' => [
                    'slug' => 'post-manual',
                    'title' => 'Post manual',
                    'excerpt' => 'Resumen',
                    'body' => '<p>Contenido manual</p>',
                    'meta_title' => 'Meta título',
                    'meta_description' => 'Meta descripción',
                    'focus_keyword' => 'carta digital',
                    'faq_schema' => json_encode([
                        '@type' => 'FAQPage',
                        'mainEntity' => [[
                            '@type' => 'Question',
                            'name' => '¿Pregunta admin?',
                            'acceptedAnswer' => [
                                '@type' => 'Answer',
                                'text' => 'Respuesta admin.',
                            ],
                        ]],
                    ]),
                ],
            ],
        ]);

        $response->assertRedirect();

        $post = BlogPost::first();
        $this->assertNotNull($post);
        $this->assertSame($category->id, $post->blog_category_id);
        $this->assertSame(BlogPost::STATUS_PUBLISHED, $post->status);

        $translation = BlogPostTranslation::where('slug', 'post-manual')->first();
        $this->assertNotNull($translation);
        $this->assertSame('carta digital', $translation->focus_keyword);
        $this->assertNotNull($translation->faq_schema);
    }

    public function test_superadmin_can_upload_featured_image(): void
    {
        $admin = $this->superAdmin();

        $response = $this->actingAs($admin)->post(route('admin.platform.blog.store'), [
            'status' => BlogPost::STATUS_PUBLISHED,
            'published_at' => now()->format('Y-m-d\TH:i'),
            'featured_image' => UploadedFile::fake()->image('cover.jpg'),
            'featured_image_alt' => 'Portada del artículo',
            'translations' => [
                'es' => [
                    'slug' => 'con-imagen',
                    'title' => 'Con imagen',
                    'body' => '<p>Texto</p>',
                ],
            ],
        ]);

        $response->assertRedirect();

        $post = BlogPost::first();
        $this->assertNotNull($post->featured_image);
        $this->assertSame('Portada del artículo', $post->featured_image_alt);
    }

    public function test_superadmin_can_manage_categories(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)
            ->post(route('admin.platform.blog.categories.store'), [
                'name' => 'Nueva categoría',
                'slug' => 'nueva-categoria',
            ])
            ->assertRedirect(route('admin.platform.blog.categories.index'));

        $this->assertDatabaseHas('blog_categories', [
            'name' => 'Nueva categoría',
            'slug' => 'nueva-categoria',
        ]);
    }

    public function test_superadmin_can_preview_draft_post(): void
    {
        $admin = $this->superAdmin();

        $post = BlogPost::create(['status' => BlogPost::STATUS_DRAFT]);
        BlogPostTranslation::create([
            'blog_post_id' => $post->id,
            'locale' => 'es',
            'slug' => 'borrador-preview',
            'title' => 'Borrador preview',
            'body' => '<p>Solo preview</p>',
            'body_format' => BlogPostTranslation::FORMAT_HTML,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.platform.blog.preview', [$post, 'es']))
            ->assertOk()
            ->assertSee('Borrador preview')
            ->assertSee('Vista previa', false);
    }

    public function test_non_superadmin_cannot_access_blog_admin(): void
    {
        $user = User::create([
            'name' => 'Cliente',
            'email' => 'cliente@test.com',
            'password' => Hash::make('password'),
            'onboarding_completed_at' => now(),
        ]);

        $this->actingAs($user)
            ->get(route('admin.platform.blog.index'))
            ->assertForbidden();
    }
}
