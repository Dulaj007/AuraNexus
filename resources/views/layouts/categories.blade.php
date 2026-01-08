@extends('layouts.public')

@push('head')
    {{-- Category pages head slot (ads/seo later) --}}
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    {{-- Header area (categories branding) --}}
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-500">Community</p>
            <h1 class="text-2xl font-bold">@yield('page_title', 'Categories')</h1>
            <p class="text-sm text-gray-600">@yield('page_subtitle', 'Browse categories and forums')</p>
        </div>

        {{-- Top ad slot (optional) --}}
        @hasSection('ad_top')
            <div class="min-w-[260px]">
                @yield('ad_top')
            </div>
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- Main --}}
        <div class="lg:col-span-9">
            @yield('categories_content')
        </div>

        {{-- Sidebar (ads/widgets later) --}}
        <aside class="lg:col-span-3 space-y-4">
            @hasSection('sidebar')
                @yield('sidebar')
            @else
                <div class="bg-white border rounded-xl p-4">
                    <p class="text-sm font-semibold">Categories</p>
                    <p class="text-xs text-gray-600 mt-1">
                        Ads/widgets can be placed here later.
                    </p>
                    <div class="mt-3 text-sm space-y-2">
                        <a class="underline" href="{{ route('categories.index') }}">All Categories</a><br>
                        <a class="underline" href="{{ route('forums.index') }}">All Forums</a>
                    </div>
                </div>
            @endif

            @hasSection('ad_sidebar')
                @yield('ad_sidebar')
            @endif
        </aside>
    </div>

</div>
@endsection
