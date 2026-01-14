@extends('layouts.forums')

@section('title', 'Page not found')

@section('forums_content')
<div class="max-w-3xl mx-auto px-6 py-12">
    <div class="rounded-2xl border bg-white p-6 space-y-3">
        <h1 class="text-xl font-semibold">Page not found</h1>

        <p class="text-sm text-gray-600">
            The page you’re looking for doesn’t exist or was moved.
        </p>

        <div class="pt-3 flex gap-2">
            <a href="{{ route('home') }}"
               class="px-4 py-2 rounded-xl bg-gray-900 text-white text-sm">
                Go home
            </a>

            <a href="{{ route('search.home') }}"
               class="px-4 py-2 rounded-xl border text-sm">
                Search posts
            </a>
        </div>
    </div>
</div>
@endsection
