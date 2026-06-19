<?php

namespace App\Http\Controllers;

use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $baseUrl = rtrim((string) config('app.url'), '/');
        $lastmod = Carbon::now()->toAtomString();

        $urls = [
            ['loc' => $baseUrl . '/', 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => $baseUrl . '/register', 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => $baseUrl . '/legal/privacidad', 'priority' => '0.5', 'changefreq' => 'yearly'],
            ['loc' => $baseUrl . '/legal/terminos', 'priority' => '0.5', 'changefreq' => 'yearly'],
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($urls as $url) {
            $xml .= "  <url>\n";
            $xml .= '    <loc>' . htmlspecialchars($url['loc'], ENT_XML1) . "</loc>\n";
            $xml .= '    <lastmod>' . $lastmod . "</lastmod>\n";
            $xml .= '    <changefreq>' . $url['changefreq'] . "</changefreq>\n";
            $xml .= '    <priority>' . $url['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= "</urlset>\n";

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
