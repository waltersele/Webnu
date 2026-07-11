<?php

namespace App\Http\Controllers\Admin;

use App\BlogCategory;
use App\BlogPost;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\PreparesMarketingShell;
use App\Http\Requests\Admin\PlatformBlogPostRequest;
use App\Services\PlatformBlogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class PlatformBlogController extends Controller
{
    use PreparesMarketingShell;

    public function index(Request $request)
    {
        $query = BlogPost::query()
            ->with(['translations', 'category'])
            ->orderByDesc('published_at')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('category_id')) {
            $query->where('blog_category_id', (int) $request->input('category_id'));
        }

        if ($request->filled('q')) {
            $term = '%' . $request->string('q') . '%';
            $query->whereHas('translations', function ($q) use ($term) {
                $q->where('locale', 'es')->where('title', 'like', $term);
            });
        }

        $posts = $query->paginate(20)->withQueryString();
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.platform.blog.index', compact('posts', 'categories'));
    }

    public function create()
    {
        $locales = array_keys(config('blog.locales', []));
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.platform.blog.create', compact('locales', 'categories'));
    }

    public function store(PlatformBlogPostRequest $request, PlatformBlogService $service)
    {
        $post = $service->create($request->validated(), (int) $request->user()->id);

        return redirect()
            ->route('admin.platform.blog.edit', $post)
            ->with('flash', 'Artículo creado.');
    }

    public function edit(BlogPost $post)
    {
        $post->load('translations', 'category');
        $locales = array_keys(config('blog.locales', []));
        $categories = BlogCategory::orderBy('name')->get();

        return view('admin.platform.blog.edit', compact('post', 'locales', 'categories'));
    }

    public function update(PlatformBlogPostRequest $request, BlogPost $post, PlatformBlogService $service)
    {
        $service->update($post, $request->validated());

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

    public function preview(Request $request, BlogPost $post, string $locale): View
    {
        $locales = array_keys(config('blog.locales', []));
        abort_unless(in_array($locale, $locales, true), 404);

        $post->load(['translations', 'category']);
        App::setLocale($locale);

        $translation = $post->translationFor($locale);
        abort_if(! $translation, 404);

        $featuredImage = $this->blogFeaturedImage($post, (int) $post->id);

        return view('blog.show', array_merge($this->blogShellData($request), $this->blogSidebarData($locale, $post), [
            'locale' => $locale,
            'translation' => $translation,
            'post' => $post,
            'alternateTranslations' => $post->translations,
            'blogLocales' => config('blog.locales', []),
            'languageContext' => 'show',
            'pageTitle' => ($translation->meta_title ?: $translation->title) . ' — Webnu',
            'metaDescription' => $translation->meta_description ?: $translation->excerpt,
            'metaKeywords' => $translation->focus_keyword,
            'canonicalUrl' => $translation->publicUrl(),
            'featuredImage' => $featuredImage,
            'featuredImageAlt' => $this->blogFeaturedImageAlt($post, $translation),
            'ogImage' => $this->absoluteImageUrl($featuredImage),
            'ogType' => 'article',
            'readingTimeMinutes' => $this->blogReadingTimeMinutes($translation->body),
            'faqSchema' => $translation->faq_schema,
            'blogPostingSchema' => $this->blogPostingSchema($translation, $post, $featuredImage),
            'breadcrumbSchema' => $this->blogBreadcrumbSchema($locale, $translation, $post->category),
            'preview' => true,
            'noindex' => true,
        ]));
    }
}
