@extends('layouts.page')

@section('meta_title', $page->meta_title ?? $page->title)
@section('meta_description', $page->meta_description ?? Str::limit(strip_tags($page->content ?? ''), 160))

@section('content')
@php
    // small safe helpers
    $updatedHuman = $page->updated_at?->diffForHumans() ?? null;

    $glass = ' border border-[var(--an-border)]
              bg-[color:var(--an-card)]/65 backdrop-blur-xl';

    $muted = 'color: var(--an-text-muted)';
@endphp

<article class="space-y-4 p-5">

    <header class="space-y-2">
        <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-[var(--an-text)]">
            {{ $page->title }}
        </h1>

 
    </header>

    <section class="{{ $glass }} p-4 sm:p-6">
        @if(blank($page->content))
            <p class="text-sm" style="{{ $muted }}">
                This page does not have content yet.
            </p>
        @else
            <div class="prose max-w-none prose-invert
                        prose-headings:text-[var(--an-text)]
                        prose-p:text-[var(--an-text)]
                        prose-strong:text-[var(--an-text)]
                        prose-a:text-[var(--an-link)] prose-a:no-underline hover:prose-a:underline
                        prose-blockquote:border-[var(--an-border)]
                        prose-hr:border-[var(--an-border)]
                        prose-li:text-[var(--an-text)]
                        prose-code:text-[var(--an-text)]
                        prose-pre:bg-[color:var(--an-bg)]/40 prose-pre:border prose-pre:border-[var(--an-border)]
                        prose-img:rounded-2xl prose-img:border prose-img:border-[var(--an-border)]">
                {!! $page->content !!}
            </div>
        @endif
    </section>

</article>
@endsection
