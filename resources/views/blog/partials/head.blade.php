<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="description" content="{{ $metaDescription ?? __('blog.meta_description') }}"/>
<title>{{ $pageTitle ?? __('blog.title') }} — Webnu</title>
@if (!empty($canonicalUrl))
<link rel="canonical" href="{{ $canonicalUrl }}"/>
@endif
@if (!empty($alternateTranslations))
    @foreach($alternateTranslations as $alt)
        <link rel="alternate" hreflang="{{ config('blog.locales.' . $alt->locale . '.hreflang', $alt->locale) }}" href="{{ $alt->publicUrl() }}"/>
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ route('blog.index', ['locale' => config('blog.fallback_locale', 'en')]) }}"/>
@endif
<link rel="icon" type="image/png" href="{{ \App\PlatformSetting::brandUrl('favicon') }}"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
<style>
    body { font-family: 'Inter', sans-serif; }
    h1, h2, h3 { font-family: 'Plus Jakarta Sans', sans-serif; }
    .wn-blog-content h2 { font-size: 1.5rem; font-weight: 700; margin: 1.5rem 0 0.75rem; }
    .wn-blog-content h3 { font-size: 1.25rem; font-weight: 600; margin: 1.25rem 0 0.5rem; }
    .wn-blog-content p { margin: 0.75rem 0; line-height: 1.7; }
    .wn-blog-content ul, .wn-blog-content ol { margin: 0.75rem 0 0.75rem 1.25rem; }
    .wn-blog-content a { color: #004ac6; text-decoration: underline; }
    .wn-blog-content img { max-width: 100%; border-radius: 12px; margin: 1rem 0; }
</style>
