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
    $latestPosts = $latestPosts ?? collect();

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
       // primary
 
     // primary

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
<div class="max-w-[1500px] mx-auto w-full px-3  py-3 space-y-15">

    <x-home.featured-pinned :posts="$featuredPinnedPosts" :ad="$ad" />


<x-home.latest-home :latestPosts="$latestPosts" :ad="$ad"   :sidebarQuickLinks="$sidebarQuickLinks"/>
    <x-home.forums-by-category :categories="$homeCategories" :ad="$ad" />



    <x-home.tag-cards :cards="$homeTagCards"   />



</div>
@endsection