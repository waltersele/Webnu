<?php

namespace App\Http\Controllers\Concerns;

use App\BlogCategory;
use App\BlogPost;
use App\BlogPostTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait PreparesMarketingShell
{
    /** @return array<string, mixed> */
    protected function marketingShellData(Request $request): array
    {
        $user = $request->user();
        $displayName = '';
        if ($user) {
            $fullName = trim((string) ($user->name ?? ''));
            if ($fullName !== '') {
                $parts = preg_split('/\s+/', $fullName);
                $displayName = $parts[0] ?? $fullName;
            }
        }

        return [
            'isLoggedIn' => (bool) $user,
            'loginUrl' => route('login'),
            'registerUrl' => route('register'),
            'panelUrl' => route('admin.dashboard'),
            'settingsUrl' => route('admin.settings'),
            'logoutUrl' => route('logout'),
            'userDisplayName' => $displayName,
            'landingLocales' => config('landing.locales', []),
            'homeUrl' => route('home'),
            'languageSelectorPartial' => 'landing.partials.language-selector',
            'navActive' => null,
        ];
    }

    /** @return array<string, mixed> */
    protected function blogShellData(Request $request): array
    {
        return array_merge($this->marketingShellData($request), [
            'languageSelectorPartial' => 'blog.partials.language-selector',
            'navActive' => 'blog',
        ]);
    }

    protected function blogFeaturedImage(?BlogPost $post, int $index = 0): string
    {
        if ($post && $post->featured_image) {
            $img = trim((string) $post->featured_image);
            if ($img !== '') {
                if (filter_var($img, FILTER_VALIDATE_URL)) {
                    return $img;
                }

                return asset(ltrim($img, '/'));
            }
        }

        $defaults = [
            'img/productos/brasa-solomillo.jpg',
            'img/productos/cocktail-negroni.jpg',
            'img/productos/brasa-burrata.jpg',
            'img/productos/fuego-tonkotsu.jpg',
            'img/productos/brasa-brownie.jpg',
        ];

        return asset($defaults[$index % count($defaults)]);
    }

    protected function blogReadingTimeMinutes(?string $html): int
    {
        $text = trim(strip_tags($html ?? ''));
        if ($text === '') {
            return 1;
        }

        $words = str_word_count($text);

        return max(1, (int) ceil($words / 200));
    }

    protected function blogFeaturedImageAlt(?BlogPost $post, ?BlogPostTranslation $translation): string
    {
        if ($post && filled($post->featured_image_alt)) {
            return (string) $post->featured_image_alt;
        }

        return $translation?->title ?? '';
    }

    protected function absoluteImageUrl(string $url): string
    {
        if (\Illuminate\Support\Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        return url($url);
    }

    /** @return array<string, mixed> */
    protected function blogPostingSchema(BlogPostTranslation $translation, BlogPost $post, string $imageUrl): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => $translation->title,
            'description' => $translation->meta_description ?: $translation->excerpt,
            'datePublished' => optional($post->published_at)->toIso8601String(),
            'dateModified' => optional($post->updated_at)->toIso8601String(),
            'image' => $this->absoluteImageUrl($imageUrl),
            'url' => $translation->publicUrl(),
            'inLanguage' => $translation->locale,
        ];
    }

    /** @return array<string, string> */
    protected function blogAlternateLocaleUrls(?BlogPost $post = null, ?string $categorySlug = null): array
    {
        $urls = [];
        foreach (array_keys(config('blog.locales', [])) as $code) {
            if ($categorySlug) {
                $urls[$code] = route('blog.category', ['locale' => $code, 'categorySlug' => $categorySlug]);
            } elseif ($post) {
                $translation = $post->translationFor($code);
                $urls[$code] = $translation
                    ? $translation->publicUrl()
                    : route('blog.index', ['locale' => $code]);
            } else {
                $urls[$code] = route('blog.index', ['locale' => $code]);
            }
        }

        return $urls;
    }

    /** @return array<string, mixed> */
    protected function blogSidebarData(string $locale, BlogPost $currentPost): array
    {
        return [
            'sidebarLatestPosts' => $this->blogSidebarLatestPosts($locale, $currentPost),
            'sidebarRelatedPosts' => $this->blogSidebarRelatedPosts($locale, $currentPost),
            'sidebarCategories' => $this->blogSidebarCategories($locale),
        ];
    }

    /** @return Collection<int, BlogPostTranslation> */
    protected function blogSidebarLatestPosts(string $locale, BlogPost $currentPost): Collection
    {
        return BlogPostTranslation::query()
            ->select('blog_post_translations.*')
            ->join('blog_posts', 'blog_posts.id', '=', 'blog_post_translations.blog_post_id')
            ->where('blog_post_translations.locale', $locale)
            ->where('blog_post_translations.blog_post_id', '!=', $currentPost->id)
            ->whereHas('post', fn ($q) => $q->publiclyVisible())
            ->orderByDesc('blog_posts.published_at')
            ->with('post.category')
            ->limit(5)
            ->get();
    }

    /** @return Collection<int, BlogPostTranslation> */
    protected function blogSidebarRelatedPosts(string $locale, BlogPost $currentPost): Collection
    {
        if (! $currentPost->blog_category_id) {
            return collect();
        }

        return BlogPostTranslation::query()
            ->select('blog_post_translations.*')
            ->join('blog_posts', 'blog_posts.id', '=', 'blog_post_translations.blog_post_id')
            ->where('blog_post_translations.locale', $locale)
            ->where('blog_posts.blog_category_id', $currentPost->blog_category_id)
            ->where('blog_post_translations.blog_post_id', '!=', $currentPost->id)
            ->whereHas('post', fn ($q) => $q->publiclyVisible())
            ->orderByDesc('blog_posts.published_at')
            ->with('post.category')
            ->limit(4)
            ->get();
    }

    /** @return Collection<int, BlogCategory> */
    protected function blogSidebarCategories(string $locale): Collection
    {
        return BlogCategory::query()
            ->whereHas('posts', function ($q) use ($locale) {
                $q->publiclyVisible()
                    ->whereHas('translations', fn ($t) => $t->where('locale', $locale));
            })
            ->withCount(['posts as posts_count' => function ($q) use ($locale) {
                $q->publiclyVisible()
                    ->whereHas('translations', fn ($t) => $t->where('locale', $locale));
            }])
            ->orderBy('name')
            ->get();
    }

    /** @return array<string, mixed> */
    protected function blogBreadcrumbSchema(string $locale, BlogPostTranslation $translation, ?BlogCategory $category = null): array
    {
        $items = [
            [
                '@type' => 'ListItem',
                'position' => 1,
                'name' => __('blog.breadcrumb_blog'),
                'item' => route('blog.index', ['locale' => $locale]),
            ],
        ];

        $position = 2;
        if ($category) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position,
                'name' => $category->name,
                'item' => $category->publicUrl($locale),
            ];
            $position++;
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => $position,
            'name' => $translation->title,
            'item' => $translation->publicUrl(),
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }
}
