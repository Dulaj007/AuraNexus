@extends('layouts.post')

@section('title', 'Post Removed • ' . config('app.name'))

@section('content')
<div class="space-y-6">

    <x-post.card variant="danger">
        <div class="flex items-start gap-3">
            <span class="inline-flex h-10 w-10 items-center justify-center rounded-2xl border"
                  style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                         background: color-mix(in srgb, var(--an-danger) 12%, transparent);">
                {{-- alert icon --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                     style="color: var(--an-danger)">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <path d="M12 9v4"/><path d="M12 17h.01"/>
                </svg>
            </span>

            <div class="min-w-0">
                <div class="text-sm sm:text-base font-extrabold" style="color: var(--an-danger);">
                    This post has been permanently removed
                </div>
                <div class="text-xs text-[var(--an-text-muted)] mt-1">
                    The content is no longer available on {{ config('app.name') }}.
                </div>
            </div>
        </div>
    </x-post.card>

    <x-post.card>
        <div class="text-sm text-[var(--an-text)]">
            <div class="text-xs text-[var(--an-text-muted)] mb-2">Post title</div>
            <div class="font-extrabold">{{ $post->title }}</div>

            @if($removed?->reason)
                <div class="mt-4 text-xs text-[var(--an-text-muted)]">Removal reason</div>
                <div class="mt-1 text-sm text-[var(--an-text)] whitespace-pre-wrap">{{ $removed->reason }}</div>
            @endif

            <div class="mt-6">
                <a href="{{ url('/') }}"
                   class="inline-flex items-center gap-2 text-sm font-semibold underline underline-offset-4 hover:no-underline"
                   style="color: var(--an-link)">
                    {{-- arrow left icon --}}
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M15 18l-6-6 6-6"/>
                    </svg>
                    Go back to home
                </a>
            </div>
        </div>
    </x-post.card>

</div>
@endsection
