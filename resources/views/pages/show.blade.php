@extends('layouts.page')

@section('content')
    <article>
        <header class="mb-8">
            <h1 class="text-3xl font-semibold tracking-tight">
                {{ $page->title }}
            </h1>

            <p class="mt-2 text-sm text-gray-500">
                Last updated {{ $page->updated_at?->diffForHumans() }}
            </p>
        </header>

        <section class="rounded-2xl border bg-white p-6">
            @if(blank($page->content))
                <p class="text-sm text-gray-500">
                    This page does not have content yet.
                </p>
            @else
                <div class="prose max-w-none">
                    {!! $page->content !!}
                </div>
            @endif
        </section>
    </article>
@endsection
