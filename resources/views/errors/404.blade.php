
@extends('layouts.page')

@section('title', 'Page not found')
@section('meta_robots', 'noindex,follow')

@section('content')
@php
    $glass = 'rounded-3xl border border-[var(--an-border)]
              bg-[color:var(--an-card)]/65 backdrop-blur-xl
              shadow-[0_10px_30px_rgba(0,0,0,0.08)]';

    $btnBase = 'inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold
                transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]
                active:scale-95 active:translate-y-[1px]';
@endphp

<div class="max-w-7xl mx-auto px-3 sm:px-6 py-10 sm:py-12">
    <div class="{{ $glass }} p-5 sm:p-6 space-y-3">


        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-[var(--an-text)]">
            Page not found
        </h1>

        <p class="text-sm" style="color: var(--an-text-muted)">
            The page you’re looking for doesn’t exist or was moved.
        </p>

        <div class="pt-3 flex flex-wrap gap-2">
            <a href="{{ route('home') }}"
               class="{{ $btnBase }} border border-[var(--an-border)]"
               style="background: color-mix(in srgb, var(--an-primary) 22%, transparent); color: var(--an-text);">
                Go home
            </a>

        </div>
    </div>
</div>
@endsection
