<?php

namespace App\Http\Controllers;

class RobotsController extends Controller
{
    public function __invoke()
    {
        $sitemapUrl = rtrim((string) config('app.url'), '/') . '/sitemap.xml';

        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /login',
            'Disallow: /register',
            'Disallow: /auth/',
            'Disallow: /comercial',
            'Disallow: /pre-alta',
            'Disallow: /activar',
            'Disallow: /integrations/',
            'Disallow: /stripe/',
            'Disallow: /password/',
            'Sitemap: ' . $sitemapUrl,
        ];

        return response(implode("\n", $lines) . "\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
