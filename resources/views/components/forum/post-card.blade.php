@props(['post'])

@php
    $imgData = method_exists($post, 'firstImage') ? $post->firstImage() : null;

    // Prefer thumb (more likely to allow hotlink), fallback to full
    $img = $imgData['thumb'] ?? ($imgData['full'] ?? null);

    $full = $imgData['full'] ?? null;

    $alt = $imgData['alt'] ?? $post->title;
    $titleAttr = $imgData['title'] ?? $post->title;

    $views = $post->views ?? 0;
@endphp

<a href="{{ route('post.show', $post) }}"
   class="group block overflow-hidden rounded-2xl border bg-white hover:shadow-sm transition">

    <div class="aspect-[16/9] bg-gray-100 overflow-hidden relative">
        @if($img)
            <img
                src="{{ $img }}"
                alt="{{ $alt }}"
                title="{{ $titleAttr }}"
                loading="lazy"
                class="h-full w-full object-cover group-hover:scale-[1.02] transition"
                onerror="
                    if (this.dataset.fallback && this.src !== this.dataset.fallback) { this.src = this.dataset.fallback; return; }
                    this.onerror=null;
                    this.closest('div').innerHTML='<div class=&quot;h-full w-full flex items-center justify-center text-xs text-gray-500&quot;>Image unavailable</div>';
                "
                data-fallback="{{ $full ?? '' }}"
            >
        @else
            <div class="h-full w-full flex items-center justify-center text-xs text-gray-500">
                No image
            </div>
        @endif
    </div>

    <div class="p-4 space-y-3">
        <h3 class="font-semibold text-gray-900 leading-snug line-clamp-2">
            {{ $post->title }}
        </h3>

        <div class="text-xs text-gray-500 flex flex-wrap gap-2">
            <span>{{ number_format($views) }} views</span>
            <span>•</span>
            <span>{{ $post->created_at?->format('Y-m-d H:i') }}</span>
        </div>

        <div class="flex flex-wrap gap-2">
            @if($post->highlightTag)
                <span class="text-xs px-2 py-1 rounded-full bg-yellow-100 text-yellow-800 border border-yellow-200">
                    {{ $post->highlightTag->name }}
                </span>
            @endif

            @foreach($post->tags->take(4) as $tag)
                <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 border">
                    {{ $tag->name }}
                </span>
            @endforeach
        </div>

        @if($full)
            <div class="pt-1">
                <a href="{{ $full }}" target="_blank" rel="nofollow noopener"
                   class="text-xs text-gray-500 underline hover:text-gray-700">
                    View image
                </a>
            </div>
        @endif
    </div>
</a>
