{{-- resources/views/home.blade.php --}}
@extends('layouts.home')

@php
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
<div class="max-w-7xl mx-auto space-y-4 sm:space-y-6">
<!--<x-home.featured-pinned :posts="$featuredPinnedPosts" />
 <center>
<script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script> 
 <ins class="eas6a97888e10" data-zoneid="5839376"></ins> 
 <script>(AdProvider = window.AdProvider || []).push({"serve": {}});</script>
</center>
    

    <x-home.forums-by-category :categories="$homeCategories" />
    <center>
<script async type="application/javascript" src="https://a.magsrv.com/ad-provider.js"></script> 
 <ins class="eas6a97888e10" data-zoneid="5839380"></ins> 
 <script>(AdProvider = window.AdProvider || []).push({"serve": {}});</script>
 </center>
    <x-home.tag-cards :cards="$homeTagCards" />
    <center>
<script>
  atOptions = {
    'key' : '7f5180870e4380a39ed3e068f8ff3447',
    'format' : 'iframe',
    'height' : 50,
    'width' : 320,
    'params' : {}
  };
</script>
<script src="https://hardypistol.com/7f5180870e4380a39ed3e068f8ff3447/invoke.js"></script>
</center>-->
</div>
@endsection
