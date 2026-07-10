<?php

namespace App\Services;

class BlogHtmlSanitizer
{
    public function sanitize(string $html): string
    {
        $html = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html) ?? $html;
        $html = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $html) ?? $html;
        $html = preg_replace('/<!\[CDATA\[(.*?)\]\]>/is', '$1', $html) ?? $html;
        $html = str_replace(']]>', '', $html);

        $allowed = config('blog.allowed_html_tags', '');

        return strip_tags($html, $allowed);
    }
}
