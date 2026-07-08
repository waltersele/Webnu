<?php

namespace App\Services;

class BlogHtmlSanitizer
{
    public function sanitize(string $html): string
    {
        $allowed = config('blog.allowed_html_tags', '');

        return strip_tags($html, $allowed);
    }
}
