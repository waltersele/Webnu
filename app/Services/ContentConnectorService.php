<?php

namespace App\Services;

use App\BlogPost;
use App\BlogPostTranslation;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContentConnectorService
{
    public function __construct(
        private readonly BlogHtmlSanitizer $sanitizer
    ) {
    }

    /** @return array<int, array<string, mixed>> */
    public function listPublished(): array
    {
        return BlogPostTranslation::query()
            ->with('post')
            ->whereHas('post', fn ($q) => $q->published())
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

        $groupId = $this->resolveGroupId($meta);
        $post = $this->resolvePost($groupId, $slug, $locale);

        $translation = $this->saveTranslation($post, $locale, $slug, $title, $content, $meta);
        $this->ensurePublished($post);

        return $this->connectorResponse($translation);
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

        $translation = $this->saveTranslation(
            $translation->post,
            $locale,
            $slug,
            $title,
            $content,
            $meta,
            $translation
        );
        $this->ensurePublished($translation->post);

        return $this->connectorResponse($translation);
    }

    /** @param array<string, mixed> $meta */
    private function saveTranslation(
        BlogPost $post,
        string $locale,
        string $slug,
        string $title,
        string $content,
        array $meta,
        ?BlogPostTranslation $existing = null
    ): BlogPostTranslation {
        $excerpt = $this->buildExcerpt($content, $meta);
        $metaTitle = $this->metaString($meta, ['title', 'meta_title']) ?? $title;
        $metaDescription = $this->metaString($meta, ['description', 'meta_description']);

        $attributes = [
            'slug' => $slug,
            'title' => $title,
            'excerpt' => $excerpt,
            'body' => $content,
            'body_format' => BlogPostTranslation::FORMAT_HTML,
            'meta_title' => $metaTitle,
            'meta_description' => $metaDescription,
        ];

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

    private function ensurePublished(BlogPost $post): void
    {
        if ($post->status !== BlogPost::STATUS_PUBLISHED) {
            $post->status = BlogPost::STATUS_PUBLISHED;
            $post->published_at = now();
            $post->save();
        }
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

    /** @param array<string, mixed> $meta */
    private function resolveGroupId(array $meta): ?string
    {
        foreach (['group_id', 'post_id', 'article_id'] as $key) {
            if (! empty($meta[$key]) && is_string($meta[$key])) {
                return $meta[$key];
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

    /** @param array<string, mixed> $meta */
    private function buildExcerpt(string $content, array $meta): string
    {
        $fromMeta = $this->metaString($meta, ['excerpt']);
        if ($fromMeta) {
            return Str::limit(strip_tags($fromMeta), 300);
        }

        return Str::limit(trim(strip_tags($content)), 300);
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
}
