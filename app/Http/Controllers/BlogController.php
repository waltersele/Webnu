<?php

namespace App\Http\Controllers;

use App\BlogCategory;
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
            'languageContext' => 'index',
            'pageTitle' => __('blog.title') . ' — Webnu',
            'metaDescription' => __('blog.meta_description'),
            'canonicalUrl' => $canonicalAtHub
                ? route('blog.hub')
                : route('blog.index', ['locale' => $locale]),
            'alternateLocaleUrls' => $this->blogAlternateLocaleUrls(),
            'blogFeaturedImage' => fn (?BlogPost $post, int $index = 0) => $this->blogFeaturedImage($post, $index),
            'blogFeaturedImageAlt' => fn (?BlogPost $post, ?BlogPostTranslation $translation) => $this->blogFeaturedImageAlt($post, $translation),
            'blogReadingTime' => fn (?string $html) => $this->blogReadingTimeMinutes($html),
        ]));
    }

    public function category(Request $request, string $locale, string $categorySlug): View
    {
        $this->assertBlogLocale($locale);
        App::setLocale($locale);

        $category = BlogCategory::query()->where('slug', $categorySlug)->firstOrFail();

        $posts = BlogPostTranslation::query()
            ->select('blog_post_translations.*')
            ->join('blog_posts', 'blog_posts.id', '=', 'blog_post_translations.blog_post_id')
            ->where('blog_post_translations.locale', $locale)
            ->where('blog_posts.blog_category_id', $category->id)
            ->whereHas('post', fn ($q) => $q->publiclyVisible())
            ->orderByDesc('blog_posts.published_at')
            ->with('post.category')
            ->paginate(12);

        $pageTitle = __('blog.category_title', ['name' => $category->name]) . ' — Webnu';

        return view('blog.category', array_merge($this->blogShellData($request), [
            'locale' => $locale,
            'category' => $category,
            'posts' => $posts,
            'blogLocales' => config('blog.locales', []),
            'languageContext' => 'category',
            'categorySlug' => $category->slug,
            'pageTitle' => $pageTitle,
            'metaDescription' => __('blog.category_description', ['name' => $category->name]),
            'canonicalUrl' => $category->publicUrl($locale),
            'alternateLocaleUrls' => $this->blogAlternateLocaleUrls(null, $category->slug),
            'collectionSchema' => $this->categoryCollectionSchema($category, $locale, $posts),
            'blogFeaturedImage' => fn (?BlogPost $post, int $index = 0) => $this->blogFeaturedImage($post, $index),
            'blogFeaturedImageAlt' => fn (?BlogPost $post, ?BlogPostTranslation $translation) => $this->blogFeaturedImageAlt($post, $translation),
            'blogReadingTime' => fn (?string $html) => $this->blogReadingTimeMinutes($html),
        ]));
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
        $featuredImage = $this->blogFeaturedImage($post, (int) $post->id);

        return view('blog.show', array_merge($this->blogShellData($request), [
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
            'preview' => false,
        ]));
    }

    /** @return array<string, mixed> */
    private function categoryCollectionSchema(BlogCategory $category, string $locale, $posts): array
    {
        $items = [];
        foreach ($posts as $translation) {
            /** @var BlogPostTranslation $translation */
            $items[] = [
                '@type' => 'ListItem',
                'url' => $translation->publicUrl(),
                'name' => $translation->title,
            ];
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $category->name,
            'url' => $category->publicUrl($locale),
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $items,
            ],
        ];
    }
}
