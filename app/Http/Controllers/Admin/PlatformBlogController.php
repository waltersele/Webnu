<?php

namespace App\Http\Controllers\Admin;

use App\BlogPost;
use App\BlogPostTranslation;
use App\Http\Controllers\Controller;
use App\Services\BlogHtmlSanitizer;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PlatformBlogController extends Controller
{
    public function index()
    {
        $posts = BlogPost::query()
            ->with('translations')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate(20);

        return view('admin.platform.blog.index', compact('posts'));
    }

    public function edit(BlogPost $post)
    {
        $post->load('translations');
        $locales = array_keys(config('blog.locales', []));

        return view('admin.platform.blog.edit', compact('post', 'locales'));
    }

    public function update(Request $request, BlogPost $post, BlogHtmlSanitizer $sanitizer)
    {
        $locales = array_keys(config('blog.locales', []));
        $rules = [];
        foreach ($locales as $locale) {
            $rules["translations.$locale.slug"] = ['nullable', 'string', 'max:120', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'];
            $rules["translations.$locale.title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.excerpt"] = ['nullable', 'string', 'max:1000'];
            $rules["translations.$locale.body"] = ['nullable', 'string'];
            $rules["translations.$locale.meta_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.meta_description"] = ['nullable', 'string', 'max:500'];
        }

        $data = $request->validate($rules);

        foreach ($locales as $locale) {
            $row = $data['translations'][$locale] ?? [];
            if (empty($row['title']) && empty($row['body'])) {
                continue;
            }

            $slug = ! empty($row['slug'])
                ? Str::slug($row['slug'])
                : Str::slug((string) ($row['title'] ?? 'post'));

            $body = (string) ($row['body'] ?? '');
            $format = BlogPostTranslation::FORMAT_MARKDOWN;
            if (str_contains($body, '<') && str_contains($body, '>')) {
                $body = $sanitizer->sanitize($body);
                $format = BlogPostTranslation::FORMAT_HTML;
            }

            BlogPostTranslation::updateOrCreate(
                ['blog_post_id' => $post->id, 'locale' => $locale],
                [
                    'slug' => $slug,
                    'title' => (string) ($row['title'] ?? $slug),
                    'excerpt' => $row['excerpt'] ?? Str::limit(strip_tags($body), 300),
                    'body' => $body,
                    'body_format' => $format,
                    'meta_title' => $row['meta_title'] ?? null,
                    'meta_description' => $row['meta_description'] ?? null,
                ]
            );
        }

        return redirect()
            ->route('admin.platform.blog.edit', $post)
            ->with('flash', 'Artículo actualizado.');
    }

    public function destroy(BlogPost $post)
    {
        $post->delete();

        return redirect()
            ->route('admin.platform.blog.index')
            ->with('flash', 'Artículo eliminado.');
    }

    public function publish(BlogPost $post)
    {
        $post->status = BlogPost::STATUS_PUBLISHED;
        $post->published_at = $post->published_at ?? now();
        $post->save();

        return back()->with('flash', 'Artículo publicado.');
    }

    public function draft(BlogPost $post)
    {
        $post->status = BlogPost::STATUS_DRAFT;
        $post->save();

        return back()->with('flash', 'Artículo en borrador.');
    }
}
