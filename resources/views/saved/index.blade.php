@extends('layouts.forums')

@section('title', 'Saved posts')

@section('page_title', 'Saved posts')
@section('page_subtitle', 'Only you can see your saved posts')

@section('forums_content')
<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

    @if($posts->count() === 0)
        <div class="border rounded-2xl bg-white p-6">
            <div class="font-semibold">No saved posts</div>
            <div class="mt-1 text-sm text-gray-500">
                Save posts you like and they’ll appear here.
            </div>
        </div>
    @else
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($posts as $post)
                <x-forum.post-card :post="$post" />
            @endforeach
        </div>

        <div>
            {{-- If you want your custom /saved/2 style pagination, tell me.
                 For now this will generate query-string pagination like ?page=2 --}}
            {{ $posts->links() }}
        </div>
    @endif

</div>
@endsection
