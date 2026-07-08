<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BlogPostTranslation extends Model
{
    public const FORMAT_HTML = 'html';

    public const FORMAT_MARKDOWN = 'markdown';

    protected $fillable = [
        'blog_post_id',
        'locale',
        'slug',
        'title',
        'excerpt',
        'body',
        'body_format',
        'meta_title',
        'meta_description',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(BlogPost::class, 'blog_post_id');
    }

    public function publicUrl(): string
    {
        return route('blog.show', ['locale' => $this->locale, 'slug' => $this->slug]);
    }

    public function renderedBody(): string
    {
        if ($this->body_format === self::FORMAT_MARKDOWN) {
            return Str::markdown($this->body);
        }

        return $this->body;
    }
}
