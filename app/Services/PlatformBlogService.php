<?php

namespace App\Services;

use App\BlogPost;
use App\BlogPostTranslation;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlatformBlogService
{
    public function __construct(
        private readonly BlogHtmlSanitizer $sanitizer,
        private readonly BlogFeaturedImageService $featuredImages
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data, int $authorUserId): BlogPost
    {
        $post = BlogPost::create([
            'status' => BlogPost::STATUS_DRAFT,
            'author_user_id' => $authorUserId,
        ]);

        $this->applyPostFields($post, $data);
        $this->syncTranslations($post, $data['translations'] ?? []);
        $this->applyFeaturedImage($post, $data);

        return $post->fresh(['translations', 'category']);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(BlogPost $post, array $data): BlogPost
    {
        $this->applyPostFields($post, $data);
        $this->syncTranslations($post, $data['translations'] ?? []);
        $this->applyFeaturedImage($post, $data);

        return $post->fresh(['translations', 'category']);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyPostFields(BlogPost $post, array $data): void
    {
        if (array_key_exists('blog_category_id', $data)) {
            $post->blog_category_id = filled($data['blog_category_id']) ? (int) $data['blog_category_id'] : null;
        }

        $status = (string) ($data['status'] ?? $post->status ?? BlogPost::STATUS_DRAFT);
        $publishedAt = $this->parsePublishedAt($data['published_at'] ?? null);

        if ($status === BlogPost::STATUS_DRAFT) {
            $post->status = BlogPost::STATUS_DRAFT;
            if ($publishedAt) {
                $post->published_at = $publishedAt;
            }
            $post->save();

            return;
        }

        $publishedAt = $publishedAt ?? $post->published_at ?? now();
        $post->published_at = $publishedAt;

        if ($status === BlogPost::STATUS_SCHEDULED || $publishedAt->isFuture()) {
            $post->status = BlogPost::STATUS_SCHEDULED;
        } else {
            $post->status = BlogPost::STATUS_PUBLISHED;
        }

        $post->save();
    }

    /**
     * @param array<string, mixed> $translations
     */
    private function syncTranslations(BlogPost $post, array $translations): void
    {
        $locales = array_keys(config('blog.locales', []));

        foreach ($locales as $locale) {
            $row = $translations[$locale] ?? [];
            if (empty($row['title']) && empty($row['body'])) {
                continue;
            }

            $slug = ! empty($row['slug'])
                ? Str::slug((string) $row['slug'])
                : Str::slug((string) ($row['title'] ?? 'post'));

            $body = (string) ($row['body'] ?? '');
            $format = BlogPostTranslation::FORMAT_MARKDOWN;
            if (str_contains($body, '<') && str_contains($body, '>')) {
                $body = $this->sanitizer->sanitize($body);
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
                    'focus_keyword' => $row['focus_keyword'] ?? null,
                    'faq_schema' => $this->normalizeFaqInput($row['faq_schema'] ?? null),
                ]
            );
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function applyFeaturedImage(BlogPost $post, array $data): void
    {
        if (! empty($data['remove_featured_image'])) {
            $this->featuredImages->clearFeaturedImage($post);

            return;
        }

        $file = $data['featured_image'] ?? null;
        if ($file instanceof UploadedFile) {
            $this->featuredImages->applyFromUpload($post, $file, $data['featured_image_alt'] ?? null);

            return;
        }

        if (array_key_exists('featured_image_alt', $data)) {
            $alt = is_string($data['featured_image_alt']) ? trim($data['featured_image_alt']) : '';
            $post->featured_image_alt = $alt !== '' ? $alt : null;
            $post->save();
        }
    }

    /** @return array<string, mixed>|null */
    public function normalizeFaqInput(mixed $value): ?array
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode(trim($value), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw ValidationException::withMessages([
                    'faq_schema' => ['El JSON de FAQ schema no es válido.'],
                ]);
            }
            $value = $decoded;
        }

        if (! is_array($value) || $value === []) {
            return null;
        }

        return $value;
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
}
