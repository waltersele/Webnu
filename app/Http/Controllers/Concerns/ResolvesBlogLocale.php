<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait ResolvesBlogLocale
{
    /** @return list<string> */
    protected function blogLocales(): array
    {
        return array_keys(config('blog.locales', []));
    }

    protected function resolveBlogLocale(Request $request): string
    {
        $supported = $this->blogLocales();
        $fallback = config('blog.fallback_locale', 'en');

        $cookieName = config('landing.cookie_name', 'webnu_landing_lang');
        $cookie = $request->cookie($cookieName);
        if (is_string($cookie) && in_array($cookie, $supported, true)) {
            return $cookie;
        }

        $accept = $request->header('Accept-Language');
        if (is_string($accept) && trim($accept) !== '') {
            $preferred = $request->getPreferredLanguage($supported);
            if (is_string($preferred) && in_array($preferred, $supported, true)) {
                return $preferred;
            }
        }

        return config('blog.default', 'es');
    }

    protected function assertBlogLocale(string $locale): void
    {
        if (! in_array($locale, $this->blogLocales(), true)) {
            abort(404);
        }
    }
}
