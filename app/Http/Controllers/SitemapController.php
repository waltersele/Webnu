<?php

namespace App\Http\Controllers;

use App\BlogPostTranslation;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        $lastmod = Carbon::now()->toAtomString();

        $urls = [
            ['loc' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'weekly', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/register', 'priority' => '0.8', 'changefreq' => 'monthly', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/legal/privacidad', 'priority' => '0.5', 'changefreq' => 'yearly', 'lastmod' => $lastmod],
            ['loc' => $baseUrl . '/legal/terminos', 'priority' => '0.5', 'changefreq' => 'yearly', 'lastmod' => $lastmod],
        ];

        foreach (array_keys(config('blog.locales', [])) as $locale) {
            $urls[] = [
                'loc' => route('blog.index', ['locale' => $locale]),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        BlogPostTranslation::query()
            ->whereHas('post', fn ($q) => $q->published())
            ->orderBy('locale')
            ->orderBy('slug')
            ->chunk(200, function ($translations) use (&$urls) {
                foreach ($translations as $translation) {
                    $urls[] = [
                        'loc' => $translation->publicUrl(),
                        'changefreq' => 'monthly',
                        'priority' => '0.7',
                        'lastmod' => optional($translation->updated_at)->toAtomString(),
                    ];
                }
            });

        $xml = view('sitemap.xml', ['urls' => $urls])->render();

        return response($xml, 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
