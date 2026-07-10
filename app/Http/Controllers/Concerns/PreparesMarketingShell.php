<?php

namespace App\Http\Controllers\Concerns;

use App\BlogPost;
use Illuminate\Http\Request;

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
}
