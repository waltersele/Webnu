<?php

namespace App\Services;

use App\BlogCategory;
use App\BlogPost;
use App\BlogPostTranslation;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentConnectorService
{
    public function __construct(
        private readonly BlogHtmlSanitizer $sanitizer,
        private readonly BlogFeaturedImageService $featuredImages
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function listPostsForConnector(): array
    {
        return BlogPostTranslation::query()
            ->with('post.category')
            ->whereHas('post')
            ->get()
            ->sortByDesc(fn (BlogPostTranslation $translation) => $translation->post->published_at?->timestamp ?? 0)
            ->map(fn (BlogPostTranslation $translation) => [
                'id' => (string) $translation->id,
                'slug' => $translation->slug,
                'title' => $translation->title,
                'url' => $translation->publicUrl(),
                'excerpt' => $translation->excerpt ?? '',
                'published_at' => optional($translation->post->published_at)->toIso8601String(),
                'locale' => $translation->locale,
                'category_id' => $translation->post->blog_category_id !== null
                    ? (string) $translation->post->blog_category_id
                    : null,
                'status' => $translation->post->status,
            ])
            ->values()
            ->all();
    }

    /** @return list<array{id: string, name: string}> */
    public function listCategories(): array
    {
        return BlogCategory::query()
            ->orderBy('name')
            ->get()
            ->map(fn (BlogCategory $category) => [
                'id' => (string) $category->id,
                'name' => $category->name,
            ])
            ->values()
            ->all();
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{id: string, url: string}
     */
    public function upsertFromPayload(array $payload): array
    {
        $locale = (string) $payload['locale'];
        $slug = $this->normalizeSlug((string) $payload['slug']);
        $title = (string) $payload['title'];
        $content = $this->sanitizer->sanitize((string) $payload['content']);
        $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
        $faqSchema = $this->normalizeFaqSchema($payload['faq_schema'] ?? null);

        $groupId = $this->resolveGroupId($payload, $meta);
        $post = $this->resolvePost($groupId, $slug, $locale);

        $this->assignCategory($post, $payload['category_id'] ?? null);
        $translation = $this->saveTranslation(
            $post,
            $locale,
            $slug,
            $title,
            $content,
            $payload,
            $meta,
            null,
            $faqSchema
        );

        $this->featuredImages->applyFromPayload($post->fresh(), $payload);
        $this->applyPublicationState(
            $post->fresh(),
            (string) $payload['status'],
            $this->parsePublishedAt($payload['published_at'] ?? null)
        );

        return $this->connectorResponse($translation->fresh(['post']));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array{id: string, url: string}
     */
    public function updateByTranslationId(int|string $id, array $payload): array
    {
        $translation = BlogPostTranslation::query()->find($id);

        if (! $translation) {
            throw new NotFoundHttpException();
        }

        $locale = (string) $payload['locale'];
        if ($translation->locale !== $locale) {
            throw ValidationException::withMessages([
                'locale' => ['Locale does not match the existing post translation.'],
            ]);
        }

        $slug = $this->normalizeSlug((string) $payload['slug']);
        $title = (string) $payload['title'];
        $content = $this->sanitizer->sanitize((string) $payload['content']);
        $meta = is_array($payload['meta'] ?? null) ? $payload['meta'] : [];
        $faqSchema = array_key_exists('faq_schema', $payload)
            ? $this->normalizeFaqSchema($payload['faq_schema'])
            : false;

        $this->assignCategory($translation->post, $payload['category_id'] ?? null);
        $translation = $this->saveTranslation(
            $translation->post,
            $locale,
            $slug,
            $title,
            $content,
            $payload,
            $meta,
            $translation,
            $faqSchema
        );

        $this->featuredImages->applyFromPayload($translation->post->fresh(), $payload);
        $this->applyPublicationState(
            $translation->post->fresh(),
            (string) $payload['status'],
            $this->parsePublishedAt($payload['published_at'] ?? null)
        );

        return $this->connectorResponse($translation->fresh(['post']));
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    private function saveTranslation(
        BlogPost $post,
        string $locale,
        string $slug,
        string $title,
        string $content,
        array $payload,
        array $meta,
        ?BlogPostTranslation $existing = null,
        array|false|null $faqSchema = null
    ): BlogPostTranslation {
        $excerpt = $this->resolveExcerpt($payload, $meta, $content);
        $metaTitle = $this->resolveMetaTitle($payload, $meta, $title);
        $metaDescription = $this->resolveMetaDescription($payload, $meta);
        $focusKeyword = $this->stringOrNull($payload['focus_keyword'] ?? null);

        $attributes = [
            'slug' => $slug,
            'title' => $title,
            'excerpt' => $excerpt,
            'body' => $content,
            'body_format' => BlogPostTranslation::FORMAT_HTML,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
            'focus_keyword' => $focusKeyword,
        ];

        if ($faqSchema !== false) {
            $attributes['faq_schema'] = $faqSchema;
        }

        if ($existing) {
            $existing->fill($attributes);
            $existing->save();

            return $existing->fresh();
        }

        return BlogPostTranslation::updateOrCreate(
            [
                'blog_post_id' => $post->id,
                'locale' => $locale,
            ],
            $attributes
        );
    }

    private function applyPublicationState(BlogPost $post, string $status, ?Carbon $publishedAt): void
    {
        $publishedAt = $publishedAt ?? now();
        $post->published_at = $publishedAt;

        if ($status === BlogPost::STATUS_SCHEDULED || $publishedAt->isFuture()) {
            $post->status = BlogPost::STATUS_SCHEDULED;
        } else {
            $post->status = BlogPost::STATUS_PUBLISHED;
        }

        $post->save();
    }

    /** @return array{id: string, url: string} */
    private function connectorResponse(BlogPostTranslation $translation): array
    {
        return [
            'id' => (string) $translation->id,
            'url' => $translation->publicUrl(),
        ];
    }

    private function normalizeSlug(string $slug): string
    {
        $normalized = Str::slug($slug);

        return $normalized !== '' ? $normalized : 'post';
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    private function resolveGroupId(array $payload, array $meta): ?string
    {
        foreach (['group_id', 'post_id', 'article_id'] as $key) {
            $fromRoot = $this->stringOrNull($payload[$key] ?? null);
            if ($fromRoot !== null) {
                return $fromRoot;
            }
        }

        foreach (['group_id', 'post_id', 'article_id'] as $key) {
            $fromMeta = $this->stringOrNull($meta[$key] ?? null);
            if ($fromMeta !== null) {
                return $fromMeta;
            }
        }

        return null;
    }

    private function resolvePost(?string $groupId, string $slug, string $locale): BlogPost
    {
        if ($groupId) {
            $existing = BlogPost::where('connector_group_id', $groupId)->first();
            if ($existing) {
                return $existing;
            }

            return BlogPost::create([
                'connector_group_id' => $groupId,
                'status' => BlogPost::STATUS_DRAFT,
            ]);
        }

        $translation = BlogPostTranslation::where('locale', $locale)->where('slug', $slug)->first();
        if ($translation) {
            return $translation->post;
        }

        return BlogPost::create([
            'status' => BlogPost::STATUS_DRAFT,
        ]);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    private function resolveExcerpt(array $payload, array $meta, string $content): string
    {
        $fromRoot = $this->stringOrNull($payload['excerpt'] ?? null);
        if ($fromRoot !== null) {
            return Str::limit(strip_tags($fromRoot), 300);
        }

        $fromMeta = $this->metaString($meta, ['excerpt']);
        if ($fromMeta) {
            return Str::limit(strip_tags($fromMeta), 300);
        }

        return Str::limit(trim(strip_tags($content)), 300);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    private function resolveMetaTitle(array $payload, array $meta, string $title): string
    {
        return $this->stringOrNull($payload['meta_title'] ?? null)
            ?? $this->metaString($meta, ['title', 'meta_title'])
            ?? $title;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, mixed> $meta
     */
    private function resolveMetaDescription(array $payload, array $meta): ?string
    {
        return $this->stringOrNull($payload['meta_description'] ?? null)
            ?? $this->metaString($meta, ['description', 'meta_description']);
    }

    /**
     * @param array<string, mixed> $meta
     * @param list<string> $keys
     */
    private function metaString(array $meta, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (! empty($meta[$key]) && is_string($meta[$key])) {
                return $meta[$key];
            }
        }

        return null;
    }

    private function assignCategory(BlogPost $post, mixed $categoryId): void
    {
        if ($categoryId === null || $categoryId === '') {
            return;
        }

        if (! BlogCategory::query()->whereKey((string) $categoryId)->exists()) {
            return;
        }

        $post->blog_category_id = (int) $categoryId;
        $post->save();
    }

    /** @return array<string, mixed>|null */
    private function normalizeFaqSchema(mixed $faqSchema): ?array
    {
        if (! is_array($faqSchema) || $faqSchema === []) {
            return null;
        }

        $type = $faqSchema['@type'] ?? null;
        if ($type !== 'FAQPage' && empty($faqSchema['mainEntity'])) {
            return null;
        }

        return $faqSchema;
    }

    private function parsePublishedAt(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function stringOrNull(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
