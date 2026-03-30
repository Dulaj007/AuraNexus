{{-- resources/views/tags/index.blade.php --}}
@extends('layouts.home')

@php
    $siteSettings = \App\Support\SiteSettings::public();
    $siteName = $siteSettings['site_name'] ?? config('app.name', 'AuraNexus');

    $titleText = 'All Tags — ' . $siteName;
    $metaDesc = 'Browse all tags and categories on ' . $siteName . '.';
@endphp

@section('title', $titleText)
@section('meta_description', $metaDesc)

@section('content')
<div class="max-w-7xl mx-auto px-1 sm:px-6 lg:px-8 py-6 space-y-6">
    {{-- Tag Cards Component --}}
    <x-home.tag-cards :cards="$cards" />
</div>
@endsection