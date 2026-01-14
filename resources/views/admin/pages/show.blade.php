@extends('layouts.forums')

@section('title', $page->title)

@section('forums_content')
<div class="max-w-4xl mx-auto px-6 py-10 space-y-6">
    <div class="rounded-2xl border bg-white p-6">
        <h1 class="text-3xl font-bold">{{ $page->title }}</h1>
        <div class="mt-4 prose max-w-none">
            {!! nl2br(e($page->content)) !!}
        </div>
    </div>
</div>
@endsection
