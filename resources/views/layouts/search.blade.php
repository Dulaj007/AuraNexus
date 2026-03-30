{{-- resources/views/layouts/search.blade.php --}}

@php
    use Illuminate\Support\Str;

    // ✅ Single source of truth
    $siteSettings = \App\Support\SiteSettings::public();

    $siteName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');
    $siteDescription = $siteSettings['site_description'] ?? ('Search posts on ' . $siteName . '.');
    $siteKeywords    = $siteSettings['site_keywords'] ?? ($siteName . ', search, posts, tags, community');
    $themeColor      = $siteSettings['site_theme_color'] ?? '#FF4268';
    $twitterSite     = $siteSettings['site_twitter'] ?? ($siteSettings['twitter_site'] ?? null);
    $themeColor      = $settings['site_theme_color'] ?? '#FF4268';
    $appName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');
    
    // Theme mode (per-user)
    $mode = request()->cookie('theme_mode', 'dark');
    $mode = in_array($mode, ['dark','light'], true) ? $mode : 'dark';

    // Query (if undefined, treat as empty)
    $q = $q ?? '';

    // SEO defaults (with per-page overrides)
    $metaTitle = trim($__env->yieldContent('meta_title')) ?: trim($__env->yieldContent('title')) ?: ('Search - ' . $siteName);
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: $siteDescription;
    $metaKeywords = trim($__env->yieldContent('meta_keywords')) ?: $siteKeywords;

    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();

    $ogType  = trim($__env->yieldContent('og_type')) ?: 'website';
    $ogImage = trim($__env->yieldContent('og_image')); // optional
    $ogImageAlt = trim($__env->yieldContent('og_image_alt')) ?: $metaTitle;

    $ogImageWidth  = trim($__env->yieldContent('og_image_width'));
    $ogImageHeight = trim($__env->yieldContent('og_image_height'));

    // Robots:
    // - allow per page override
    // - else use settings meta_robots
    // - but force noindex for query results
    $robots = trim($__env->yieldContent('meta_robots'));
    if ($robots === '') {
        $robots = trim((string)($siteSettings['meta_robots'] ?? ''));
    }
    if ($robots === '') {
        $robots = 'index,follow';
    }

    // ✅ Prevent search result pages from being indexed when query exists
    if (!empty($q)) {
        $robots = 'noindex,follow';
    }

    // Ads set selection (shared for search + tags)
    $adsSet = trim($__env->yieldContent('ads_set', 'search_tags'));

    // Optional verification metas (safe if not present)
    $googleVerify = trim((string)($siteSettings['google_site_verification'] ?? '')) ?: null;
    $bingVerify   = trim((string)($siteSettings['bing_site_verification'] ?? '')) ?: null;
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $mode }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $metaTitle }}</title>

    <link rel="canonical" href="{{ $canonical }}">

    {{-- Primary Meta --}}
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="keywords" content="{{ $metaKeywords }}">
    <meta name="robots" content="{{ $robots }}">
    <meta name="theme-color" content="{{ $themeColor }}">

    {{-- App identity --}}
    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">

    {{-- Security/UX helpful metas --}}
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <meta name="format-detection" content="telephone=no">
    <meta name="color-scheme" content="dark light">

    {{-- Site verification (optional) --}}
    @if($googleVerify)
        <meta name="google-site-verification" content="{{ $googleVerify }}">
    @endif
    @if($bingVerify)
        <meta name="msvalidate.01" content="{{ $bingVerify }}">
    @endif

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:secure_url" content="{{ $ogImage }}">
        <meta property="og:image:alt" content="{{ $ogImageAlt }}">
        @if($ogImageWidth)  <meta property="og:image:width" content="{{ $ogImageWidth }}"> @endif
        @if($ogImageHeight) <meta property="og:image:height" content="{{ $ogImageHeight }}"> @endif
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ $ogImage ? 'summary_large_image' : 'summary' }}">
    @if($twitterSite)
        <meta name="twitter:site" content="{{ $twitterSite }}">
    @endif
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    {{-- JSON-LD --}}
    @hasSection('json_ld')
        <script type="application/ld+json">
{!! trim($__env->yieldContent('json_ld')) !!}
        </script>
    @endif

    {{-- ✅ Ads: head injections (scripts/meta if needed by ads set) --}}
    @stack('ads:head')

    {{-- App assets --}}
    @vite(['resources/css/app.css','resources/js/app.js'])

    {{-- Theme CSS (generated by ThemeService) --}}
    @inject('theme', \App\Services\ThemeService::class)
    <style>
        {!! $theme->css($mode) !!}
        [x-cloak]{display:none !important;}
    </style>

    {{-- Ensure attribute is synced early --}}
    <script>
        (function () {
            document.documentElement.setAttribute('data-theme', @json($mode));
        })();
    </script>

    {{-- Per-page head additions --}}
    @stack('head')
</head>


<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden font-sans">

    @include('partials.background-layer')
    @include('partials.nav')

    @include('partials.app-shell')

    @stack('scripts')


</body>
</html>
