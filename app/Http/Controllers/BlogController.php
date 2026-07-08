<?php

namespace App\Http\Controllers;

use App\BlogPost;
use App\BlogPostTranslation;
use App\Http\Controllers\Concerns\ResolvesBlogLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class BlogController extends Controller
{
    use ResolvesBlogLocale;

    public function redirectToLocale(Request $request)
    {
        $locale = $this->resolveBlogLocale($request);

        return redirect()->route('blog.index', ['locale' => $locale]);
    }

    public function index(Request $request, string $locale): View
    {
        $this->assertBlogLocale($locale);
        App::setLocale($locale);

        $posts = BlogPostTranslation::query()
            ->select('blog_post_translations.*')
            ->join('blog_posts', 'blog_posts.id', '=', 'blog_post_translations.blog_post_id')
            ->where('blog_post_translations.locale', $locale)
            ->where('blog_posts.status', BlogPost::STATUS_PUBLISHED)
            ->whereNotNull('blog_posts.published_at')
            ->where('blog_posts.published_at', '<=', now())
            ->orderByDesc('blog_posts.published_at')
            ->with('post')
            ->paginate(12);

        return view('blog.index', [
            'locale' => $locale,
            'posts' => $posts,
            'blogLocales' => config('blog.locales', []),
            'pageTitle' => __('blog.title'),
            'metaDescription' => __('blog.meta_description'),
        ]);
    }

    public function show(Request $request, string $locale, string $slug): View
    {
        $this->assertBlogLocale($locale);
        App::setLocale($locale);

        $translation = BlogPostTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('post', fn ($q) => $q->published())
            ->with('post.translations')
            ->firstOrFail();

        return view('blog.show', [
            'locale' => $locale,
            'translation' => $translation,
            'post' => $translation->post,
            'alternateTranslations' => $translation->post->translations,
            'blogLocales' => config('blog.locales', []),
            'pageTitle' => $translation->meta_title ?: $translation->title,
            'metaDescription' => $translation->meta_description ?: $translation->excerpt,
            'canonicalUrl' => $translation->publicUrl(),
        ]);
    }
}
