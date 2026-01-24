{{-- resources/views/layouts/profile.blade.php --}}
@php
    use Illuminate\Support\Str;

    // ✅ Single source of truth
    $siteSettings = \App\Support\SiteSettings::public();

    // Brand / defaults
    $siteName        = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');
    $siteDescription = $siteSettings['site_description'] ?? ('View user profiles on ' . $siteName . '.');
    $siteKeywords    = $siteSettings['site_keywords'] ?? ($siteName . ', profiles, users, community');
    $themeColor      = $siteSettings['site_theme_color'] ?? '#FF4268';
    $twitterSite     = $siteSettings['site_twitter'] ?? ($siteSettings['twitter_site'] ?? null);

    // ✅ SEO defaults (profile pages) with per-page overrides
    $metaTitle = trim($__env->yieldContent('meta_title')) ?: trim($__env->yieldContent('title')) ?: $siteName;
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: $siteDescription;
    $metaKeywords = trim($__env->yieldContent('meta_keywords')) ?: $siteKeywords;

    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    $ogType  = trim($__env->yieldContent('og_type')) ?: 'profile';
    $ogImage = trim($__env->yieldContent('og_image')); // optional

    // Robots: allow per page override, else use saved setting if exists
    $robots = trim($__env->yieldContent('meta_robots'));
    if ($robots === '') {
        $robots = trim((string)($siteSettings['meta_robots'] ?? ''));
    }
    if ($robots === '') {
        $robots = 'index,follow';
    }

    // Theme mode (per-user)
    $mode = request()->cookie('theme_mode', 'dark');
    $mode = in_array($mode, ['dark','light'], true) ? $mode : 'dark';

    // ✅ ADS helper (cached)
    $headProfile = function_exists('ad_html') ? ad_html('head_profile') : (function_exists('ad') ? ad('head_profile') : null);
    $headProfile = (is_string($headProfile) && trim($headProfile) !== '') ? $headProfile : null;

    // Optional verification metas (safe if not present)
    $googleVerify = trim((string)($siteSettings['google_site_verification'] ?? '')) ?: null;
    $bingVerify   = trim((string)($siteSettings['bing_site_verification'] ?? '')) ?: null;

    // OG image alt (optional per page)
    $ogImageAlt = trim($__env->yieldContent('og_image_alt')) ?: $metaTitle;

    // Optional sizes (per-page can set these sections if you want)
    $ogImageWidth  = trim($__env->yieldContent('og_image_width'));
    $ogImageHeight = trim($__env->yieldContent('og_image_height'));
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $mode }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $metaTitle }} - {{ $siteName }}</title>

    {{-- Canonical --}}
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

    {{-- ✅ Profile Head Ads/Scripts (from helper -> cached) --}}
    @if($headProfile)
        {!! $headProfile !!}
    @endif

    {{-- ✅ Backward-compatible hook (optional). Remove later if unused. --}}
    @stack('head_profile_ads')

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

<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden">

    {{-- Ambient glows (subtle, match site) --}}
    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden opacity-60">
        <div class="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full blur-3xl opacity-15 bg-[var(--an-link)]"></div>
        <div class="absolute top-24 -right-48 h-[620px] w-[620px] rounded-full blur-3xl opacity-12 bg-[var(--an-primary)]"></div>
        <div class="absolute bottom-[-220px] left-[25%] h-[520px] w-[520px] rounded-full blur-[140px] opacity-10 bg-[var(--an-info)]"></div>
    </div>

    {{-- Public navbar --}}
    @include('partials.nav')

    <main class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-2 sm:py-6">

        {{-- Flash messages --}}
        @if(session('success'))
            <div class="mb-3 sm:mb-4 rounded-2xl border px-4 py-3 text-sm"
                 style="border-color: color-mix(in srgb, var(--an-success) 35%, var(--an-border));
                        background: color-mix(in srgb, var(--an-success) 12%, transparent);
                        color: color-mix(in srgb, var(--an-text) 85%, var(--an-success));">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-3 sm:mb-4 rounded-2xl border px-4 py-3 text-sm"
                 style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                        background: color-mix(in srgb, var(--an-danger) 12%, transparent);
                        color: color-mix(in srgb, var(--an-text) 85%, var(--an-danger));">
                {{ session('error') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div class="mb-3 sm:mb-4 rounded-2xl border px-4 py-3 text-sm"
                 style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                        background: color-mix(in srgb, var(--an-danger) 12%, transparent);
                        color: color-mix(in srgb, var(--an-text) 85%, var(--an-danger));">
                <div class="font-semibold">Please fix the following:</div>
                <ul class="list-disc pl-5 mt-2 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Public footer --}}
    @include('partials.footer')

    @stack('scripts')
</body>
</html>
