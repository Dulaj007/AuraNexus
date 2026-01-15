@extends('layouts.forums')

@section('title', $page->title)

@section('forums_content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8 sm:py-10 space-y-6">

    <div class="rounded-2xl border p-5 sm:p-6"
         style="background: var(--bg-card, #fff); border-color: var(--border, #e2e8f0); box-shadow: 0 18px 45px rgba(0,0,0,.12);">
        <h1 class="text-3xl font-bold">{{ $page->title }}</h1>

        <div class="mt-4 prose max-w-none">
            {!! nl2br(e($page->content)) !!}
        </div>
    </div>

</div>
@endsection
