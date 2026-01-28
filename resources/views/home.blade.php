@extends('layouts.page')

@php
    $settings = $siteSettings ?? [];
    $siteName = $settings['site_name'] ?? config('app.name', 'AuraNexus');

    $metaTitle = $settings['home_meta_title'] ?? $siteName;
    $metaDesc  = $settings['home_meta_description']
        ?? ($settings['site_description'] ?? ('Explore updates on ' . $siteName . '.'));
    $canonical = url('/');
@endphp

@section('title', 'Home')
@section('meta_title', $metaTitle)
@section('meta_description', $metaDesc)
@section('canonical', $canonical)
@section('og_type', 'website')

@section('json_ld')
@php
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
<div class="max-w-7xl mx-auto p-6">
    <h1 class="text-2xl font-extrabold">SEO TEST OK</h1>
    <p class="mt-2 text-sm opacity-80">No components, no ads, only meta + JSON-LD.</p>
</div>
@endsection
