{{-- resources/views/layouts/post.blade.php --}}
@php
    use Illuminate\Support\Str;

    // ✅ Single source of truth (cached)
    $siteSettings = \App\Support\SiteSettings::public();

    // Site identity
    $siteName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');
    $siteBio  = $siteSettings['site_subtitle'] ?? (config('app.bio') ?? 'Build • Share • Learn');

    // Site description (generic fallback)
    $defaultDesc = $siteSettings['site_description']
        ?? ('Read community posts on ' . $siteName . '.');

    // Theme mode (per-user cookie)
    $mode = request()->cookie('theme_mode', 'dark');
    $mode = in_array($mode, ['dark','light'], true) ? $mode : 'dark';

    // ✅ Logo URL (must read from site_logo_url)
    $rawLogo = trim((string) ($siteSettings['site_logo_url'] ?? ''));

    // Build absolute URL for all cases
    $logoUrl = '';
    if ($rawLogo !== '') {
        if (Str::startsWith($rawLogo, ['http://', 'https://'])) {
            $logoUrl = $rawLogo;
        } else {
            $p = ltrim($rawLogo, '/');
            if (Str::startsWith($p, 'storage/')) {
                $logoUrl = url('/' . $p);
            } else {
                $logoUrl = asset($p);
            }
        }
    } else {
        // fallback asset (change if needed)
      
    }

    // Page meta (allow overrides from views)
    $pageTitle = trim($__env->yieldContent('meta_title'))
        ?: trim($__env->yieldContent('title'));

    // Title format: "Post title • SiteName"
    $metaTitle = $pageTitle !== '' ? ($pageTitle . ' • ' . $siteName) : $siteName;

    $metaDescription = trim($__env->yieldContent('meta_description')) ?: $defaultDesc;

    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();

    // Robots: allow per-page override, otherwise use setting default, otherwise index/follow
    $robotsDefault = trim((string)($siteSettings['seo_robots'] ?? 'index,follow'));
    $robotsValue = trim($__env->yieldContent('meta_robots')) ?: $robotsDefault;

    // Keywords: allow per-page override, otherwise use setting seo_keywords
    $keywordsDefault = trim((string)($siteSettings['seo_keywords'] ?? ''));
    $keywordsValue = trim($__env->yieldContent('meta_keywords')) ?: $keywordsDefault;

    // Theme color (optional)
    $themeColor = trim((string)($siteSettings['theme_color'] ?? '#FF4268'));
  $appName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');
    // OG type
    $ogType = trim($__env->yieldContent('og_type')) ?: 'article';

    // ✅ OG image logic:
    // 1) @section('og_image')
    // 2) $firstPostImage (set in the post view/controller)
    // 3) fallback to site logo (optional but good for shares)
    $ogImage = trim($__env->yieldContent('og_image'));

    if (!$ogImage && isset($firstPostImage) && is_string($firstPostImage) && trim($firstPostImage) !== '') {
        $ogImage = trim($firstPostImage);
    }

    // If ogImage exists but is an ImageTwist "page" URL and we have a direct image, prefer direct
    if ($ogImage && isset($firstPostImage) && is_string($firstPostImage)) {
        $fp = trim($firstPostImage);
        if ($fp !== '' && Str::contains($ogImage, 'imagetwist.com/') && !Str::contains($ogImage, 'img') ) {
            $ogImage = $fp;
        }
    }

    if (!$ogImage) {
        $ogImage = $logoUrl;
    }

    // If you want to allow disabling og:image completely, you can do:
    // $ogImage = $ogImage ?: null;

    // Post-only head script (cached via helper)
    $headPostHtml = function_exists('ad_html')
        ? ad_html('head_post')
        : (function_exists('ad') ? ad('head_post') : null);

    // Optional JSON-LD (string JSON from views)
    $jsonLd = trim($__env->yieldContent('json_ld'));
@endphp

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $mode }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title>{{ $metaTitle }}</title>
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="{{ $robotsValue }}">
    <meta name="theme-color" content="{{ $themeColor }}">

    @if($keywordsValue !== '')
        <meta name="keywords" content="{{ $keywordsValue }}">
    @endif

    <meta name="application-name" content="{{ $siteName }}">
    <meta name="apple-mobile-web-app-title" content="{{ $siteName }}">

    {{-- Icons --}}
    <link rel="icon" href="{{ $logoUrl }}">
    <link rel="apple-touch-icon" href="{{ $logoUrl }}">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="{{ $ogType }}">
    <meta property="og:url" content="{{ $canonical }}">
    @if(!empty($ogImage))
        <meta property="og:image" content="{{ $ogImage }}">
        <meta property="og:image:alt" content="{{ $siteName }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="{{ !empty($ogImage) ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if(!empty($ogImage))
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    {{-- JSON-LD --}}
    @if($jsonLd !== '')
        <script type="application/ld+json">{!! $jsonLd !!}</script>
    @endif

    {{-- ✅ Post Pages – Head Ads / Scripts (post-only) --}}
    @if(!empty($headPostHtml))
        {!! $headPostHtml !!}
    @endif

    {{-- App assets --}}
    @vite(['resources/css/app.css','resources/js/app.js'])

    {{-- Theme CSS --}}
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

    @stack('head')
</head>



<body class="min-h-screen bg-[var(--an-bg)] text-[var(--an-text)] overflow-x-hidden font-sans">

    @include('partials.background-layer')
    @include('partials.nav')

    @include('partials.app-shell')

    @stack('scripts')





    {{-- Global modal handler --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            let openModalId = null;

            function openModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('hidden');
                el.classList.add('flex');
                document.body.style.overflow = 'hidden';
                openModalId = id;
            }

            function closeModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.add('hidden');
                el.classList.remove('flex');
                document.body.style.overflow = '';
                if (openModalId === id) openModalId = null;
            }

            document.addEventListener('click', (e) => {
                const openBtn = e.target.closest('[data-modal-open]');
                if (openBtn) {
                    openModal(openBtn.getAttribute('data-modal-open'));
                    return;
                }

                const closeBtn = e.target.closest('[data-modal-close]');
                if (closeBtn) {
                    closeModal(closeBtn.getAttribute('data-modal-close'));
                    return;
                }
            });

            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && openModalId) closeModal(openModalId);
            });
        });
    </script>
</body>
</html>
