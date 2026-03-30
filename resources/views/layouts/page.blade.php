{{-- resources/views/layouts/page.blade.php --}}
@php
    use Illuminate\Support\Str;

    // ✅ IMPORTANT: layout can be used by 404 etc (no $page passed)
    $page = $page ?? null;

    // Settings (cached) — NEW keys
    $siteSettings = cache()->remember('site.settings.public', 300, function () {
        if (class_exists(\App\Models\Setting::class)) {
            return \App\Models\Setting::query()
                ->whereIn('key', [
                    'site_name',
                    'site_subtitle',
                    'site_description',
                    'site_keywords',
                    'site_logo_url',
                    'site_og_image',
                    'site_twitter',
                    'site_theme_color',
                    'meta_robots',
                ])
                ->pluck('value', 'key')
                ->toArray();
        }
        return [];
    });

    $siteName        = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');
    $siteSubtitle    = $siteSettings['site_subtitle'] ?? (config('app.bio') ?? 'Build • Share • Learn');
    $siteDescription = $siteSettings['site_description'] ?? ('Explore community updates on ' . $siteName . '.');
    $siteKeywords    = $siteSettings['site_keywords'] ?? ($siteName . ', forums, community, posts, tags');
    $themeColor      = $siteSettings['site_theme_color'] ?? '#FF4268';
    $twitter         = $siteSettings['site_twitter'] ?? null;
     $appName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');

    // Theme mode (per-user)
    $mode = request()->cookie('theme_mode', 'dark');
    $mode = in_array($mode, ['dark','light'], true) ? $mode : 'dark';

    // Canonical
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();

    // ✅ Page-aware meta (SAFE)
    $pageTitle = trim($__env->yieldContent('meta_title'))
        ?: ($page?->meta_title ?? $page?->title ?? trim($__env->yieldContent('title')) ?: 'Page');

    $metaTitle = $pageTitle . ' • ' . $siteName;

    $metaDesc = trim($__env->yieldContent('meta_description'))
        ?: ($page?->meta_description ?? null)
        ?: ($page?->content ? Str::limit(strip_tags($page->content), 160) : null)
        ?: ($pageTitle ? ($pageTitle . ' — ' . $siteName) : $siteDescription);

    // Robots
    $robots = trim($__env->yieldContent('meta_robots'))
        ?: ($siteSettings['meta_robots'] ?? 'index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1');

    // ✅ Helper for resolving URLs (http(s), /storage, storage, or asset)
    $resolveUrl = function (?string $value, string $fallbackAsset = '') {
        $value = $value ? trim($value) : null;
        if (!$value) return $fallbackAsset ? asset($fallbackAsset) : null;

        if (Str::startsWith($value, ['http://', 'https://'])) return $value;
        if (Str::startsWith($value, ['/storage/', 'storage/'])) return url($value);
        return asset($value);
    };

    // Images
    $logoUrl = $resolveUrl($siteSettings['site_logo_url'] ?? null, 'logo/AuraNexusLogo.png');

    $ogImage = $resolveUrl($siteSettings['site_og_image'] ?? null, '');
    $ogImage = $ogImage ?: $logoUrl; // fallback

    $ogType = trim($__env->yieldContent('og_type')) ?: 'website';

    // Global JSON-LD
    $globalLd = [
        "@context" => "https://schema.org",
        "@graph" => [
            [
                "@type" => "Organization",
                "name"  => $siteName,
                "url"   => url('/'),
                "logo"  => $logoUrl,
            ],
            [
                "@type" => "WebSite",
                "name"  => $siteName,
                "url"   => url('/'),
                "description" => $siteDescription,
                "potentialAction" => [
                    "@type" => "SearchAction",
                    "target" => url('/search?q={search_term_string}'),
                    "query-input" => "required name=search_term_string",
                ],
            ],
        ],
    ];
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $mode }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $metaTitle }}</title>

    {{-- Canonical --}}
    <link rel="canonical" href="{{ $canonical }}">

    {{-- Primary Meta --}}
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="keywords" content="{{ $siteKeywords }}">
    <meta name="robots" content="{{ $robots }}">
    <meta name="theme-color" content="{{ $themeColor }}">

    {{-- App identity --}}
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:image:alt" content="{{ $pageTitle }}">

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    @if($twitter)
        <meta name="twitter:site" content="{{ $twitter }}">
    @endif
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">

    {{-- Icons --}}
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" href="{{ $logoUrl }}">

    {{-- JSON-LD (page specific) --}}
    @hasSection('json_ld')
        <script type="application/ld+json">
{!! trim($__env->yieldContent('json_ld')) !!}
        </script>
    @endif

    {{-- Global JSON-LD --}}
    <script type="application/ld+json">
{!! json_encode($globalLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
    </script>

    {{-- App assets --}}
    @vite(['resources/css/app.css','resources/js/app.js'])

    {{-- Theme CSS --}}
    @inject('theme', \App\Services\ThemeService::class)
    <style>
        {!! $theme->css($mode) !!}
        [x-cloak]{display:none !important;}
    </style>

    {{-- Sync theme attribute early --}}
    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', @json($mode));
        })();
    </script>

    @stack('head')
</head>

<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden font-sans">

    @include('partials.background-layer')
    @include('partials.nav')

    @include('partials.app-shell')

    @stack('scripts')


</body>
</html>
