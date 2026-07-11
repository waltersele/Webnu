<?php

namespace App\Http\Controllers;

use App\BlogPost;
use App\BlogPostTranslation;
use App\Http\Controllers\Concerns\PreparesMarketingShell;
use App\Http\Controllers\Concerns\ResolvesBlogLocale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\View;

class BlogController extends Controller
{
    use PreparesMarketingShell;
    use ResolvesBlogLocale;

    /** Hub canónico del blog (200). Sonartop y crawlers deben usar /blog o /es/blog. */
    public function hub(Request $request): View
    {
        return $this->index($request, config('blog.default', 'es'), canonicalAtHub: true);
    }

    public function index(Request $request, string $locale, bool $canonicalAtHub = false): View
    {
        $this->assertBlogLocale($locale);
        App::setLocale($locale);

        $posts = BlogPostTranslation::query()
            ->select('blog_post_translations.*')
            ->join('blog_posts', 'blog_posts.id', '=', 'blog_post_translations.blog_post_id')
            ->where('blog_post_translations.locale', $locale)
            ->whereHas('post', fn ($q) => $q->publiclyVisible())
            ->orderByDesc('blog_posts.published_at')
            ->with('post.category')
            ->paginate(12);

        return view('blog.index', array_merge($this->blogShellData($request), [
            'locale' => $locale,
            'posts' => $posts,
            'blogLocales' => config('blog.locales', []),
            'pageTitle' => __('blog.title') . ' — Webnu',
            'metaDescription' => __('blog.meta_description'),
            'canonicalUrl' => $canonicalAtHub
                ? route('blog.hub')
                : route('blog.index', ['locale' => $locale]),
            'alternateLocaleUrls' => $this->blogAlternateLocaleUrls(),
            'blogFeaturedImage' => fn (?BlogPost $post, int $index = 0) => $this->blogFeaturedImage($post, $index),
            'blogReadingTime' => fn (?string $html) => $this->blogReadingTimeMinutes($html),
        ]));
    }

    /** @return array<string, string> */
    private function blogAlternateLocaleUrls(): array
    {
        $urls = [];
        foreach (array_keys(config('blog.locales', [])) as $code) {
            $urls[$code] = route('blog.index', ['locale' => $code]);
        }

        return $urls;
    }

    public function show(Request $request, string $locale, string $slug): View
    {
        $this->assertBlogLocale($locale);
        App::setLocale($locale);

        $translation = BlogPostTranslation::query()
            ->where('locale', $locale)
            ->where('slug', $slug)
            ->whereHas('post', fn ($q) => $q->publiclyVisible())
            ->with(['post.translations', 'post.category'])
            ->firstOrFail();

        $post = $translation->post;

        return view('blog.show', array_merge($this->blogShellData($request), [
            'locale' => $locale,
            'translation' => $translation,
            'post' => $post,
            'alternateTranslations' => $post->translations,
            'blogLocales' => config('blog.locales', []),
            'pageTitle' => ($translation->meta_title ?: $translation->title) . ' — Webnu',
            'metaDescription' => $translation->meta_description ?: $translation->excerpt,
            'canonicalUrl' => $translation->publicUrl(),
            'featuredImage' => $this->blogFeaturedImage($post, (int) $post->id),
            'featuredImageAlt' => $post->featured_image_alt ?: $translation->title,
            'readingTimeMinutes' => $this->blogReadingTimeMinutes($translation->body),
            'faqSchema' => $translation->faq_schema,
        ]));
    }
}
