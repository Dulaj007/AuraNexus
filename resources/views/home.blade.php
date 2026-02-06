{{-- resources/views/home.blade.php --}}
@extends('layouts.home')

@php
    use Illuminate\Support\Facades\Cache;

    // ✅ Safe fallbacks
    $settings = $siteSettings ?? [];
    $siteName = $settings['site_name'] ?? config('app.name', 'AuraNexus');

    $featuredPinnedPosts = $featuredPinnedPosts ?? collect();
    $homeCategories      = $homeCategories ?? collect();
    $homeTagCards        = $homeTagCards ?? collect();

    // Home meta overrides (SEO)
    $metaTitle = $settings['home_meta_title'] ?? $siteName;
    $metaDesc  = $settings['home_meta_description']
        ?? ($settings['site_description'] ?? ('Explore featured pinned posts and the latest community updates on ' . $siteName . '.'));
    $canonical = url('/');

    /**
     * ✅ Ads (same method as your forums/show)
     * - Prefer global helper ad() if it exists
     * - Else fallback to cached DB map
     * - Load ONCE, then reuse via $ad()
     */
    $adsMap = null;

    if (!function_exists('ad')) {
        $adsMap = Cache::remember('ads.placements', 300, function () {
            return \App\Models\AdPlacement::query()
                ->where('is_enabled', true)
                ->whereNotNull('html')
                ->pluck('html', 'key')
                ->toArray();
        });
    }

    $ad = function (string $key) use (&$adsMap): ?string {
        $html = null;

        if (function_exists('ad')) {
            $html = ad($key);
        } else {
            $html = $adsMap[$key] ?? null;
        }

        return (is_string($html) && trim($html) !== '') ? $html : null;
    };

    // ✅ Home ad placements (rendered inside page)
    // NOTE: head_home_ads is in <head> inside layouts/home.blade.php
    $adTopA    = $ad('home_ads_top');      // primary
    $adMidA    = $ad('home_ads_mid');      // primary
    $adBottomA = $ad('home_ads_bottom');   // primary

    // (Optional future desktop extra slots if you add them later in registry)
    $adTopB    = $ad('home_ads_top_b');
    $adMidB    = $ad('home_ads_mid_b');
    $adBottomB = $ad('home_ads_bottom_b');
@endphp

@section('title', 'Home')

@section('meta_title', $metaTitle)
@section('meta_description', $metaDesc)
@section('canonical', $canonical)
@section('og_type', 'website')

@section('json_ld')
@php
    // Home-specific LD (can extend later with ItemList for categories/tags)
    $jsonLd = [
        "@context" => "https://schema.org",
        "@type"    => "WebPage",
        "name"     => $metaTitle,
        "url"      => $canonical,
        "description" => $metaDesc,
        "isPartOf" => [
            "@type" => "WebSite",
            "name"  => $siteName,
            "url"   => $canonical,
        ],
    ];
@endphp
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) !!}
@endsection

@section('content')
<div class="max-w-7xl mx-auto px-1 sm:px-6 lg:px-8 py-2 sm:py-6 space-y-4 sm:space-y-6">

    <x-home.featured-pinned :posts="$featuredPinnedPosts" />

    {{-- ✅ TOP ADS (same style pattern as forums) --}}
    @if($adTopA || $adTopB)
        <div class="flex flex-row justify-center">
            @if($adTopA)
                <div class="flex">
                    {!! $adTopA !!}
                </div>
            @endif

            @if($adTopB)
                <div class="hidden lg:flex">
                    {!! $adTopB !!}
                </div>
            @endif
        </div>
    @endif

    <x-home.forums-by-category :categories="$homeCategories" />

    {{-- ✅ MID ADS --}}
    @if($adMidA || $adMidB)
        <div class="flex flex-row justify-center">
            @if($adMidA)
                <div class="flex">
                    {!! $adMidA !!}
                </div>
            @endif

            @if($adMidB)
                <div class="hidden lg:flex">
                    {!! $adMidB !!}
                </div>
            @endif
        </div>
    @endif

    <x-home.tag-cards :cards="$homeTagCards" />

    {{-- ✅ BOTTOM ADS --}}
    @if($adBottomA || $adBottomB)
        <div class="flex flex-row justify-center">
            @if($adBottomA)
                <div class="flex">
                    {!! $adBottomA !!}
                </div>
            @endif

            @if($adBottomB)
                <div class="hidden lg:flex">
                    {!! $adBottomB !!}
                </div>
            @endif
        </div>
    @endif

</div>
@endsection