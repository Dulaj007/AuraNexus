@props([
    'url',
    'label' => null,
    'display' => null,     // ✅ masked text like "example.com/…"
    'target' => null,      // ✅ override if needed (default same-tab)
])

@php
    $href = $url;

    // default display text
    $host = null;
    try {
        $host = parse_url($url, PHP_URL_HOST);
    } catch (\Throwable $e) {
        $host = null;
    }

    $displayText = $display
        ?: ($host ? ($host . '/…') : 'Open link');

    // Title line: label OR display (not raw URL)
    $titleText = $label ?: $displayText;

    // ✅ For unlock flow, better to stay same-tab (no target=_blank)
    $openTarget = $target; // null means same tab
@endphp

<a href="{{ $href }}"
   @if($openTarget) target="{{ $openTarget }}" @endif
   rel="nofollow noopener"
   class="block rounded-2xl border border-[var(--an-border)]
          bg-[var(--an-link)]/25 
          an-gradient-animated-link backdrop-blur
          hover:bg-[color:var(--an-card-2)]/60 hover:border-[color:var(--an-border)]/80
          transition p-1 shadow-2xl w-auto mr-[5vh]">

    <div class="flex items-center gap-2 mr-2 ml-1">

        <div class="min-w-0 flex-1">
            <div class="text-sm text-[var(--an-text)] break-all line-clamp-2">
                {{ $titleText }}
            </div>

            <div class="mt-0 text-xs text-[var(--an-text-muted)] break-all">
                {{ $displayText }}
            </div>
        </div>

        <span class="inline-flex h-5 w-5 items-center justify-center text-[var(--an-text-muted)]">
            {{-- external link icon --}}
            <svg viewBox="0 0 16 16" fill="currentColor"
                 xmlns="http://www.w3.org/2000/svg"
                 class="h-5 w-5">
                <path d="M7.05025 1.53553C8.03344 0.552348 9.36692 0 10.7574 0C13.6528 0 16 2.34721 16 5.24264C16 6.63308 15.4477 7.96656 14.4645 8.94975L12.4142 11L11 9.58579L13.0503 7.53553C13.6584 6.92742 14 6.10264 14 5.24264C14 3.45178 12.5482 2 10.7574 2C9.89736 2 9.07258 2.34163 8.46447 2.94975L6.41421 5L5 3.58579L7.05025 1.53553Z"/>
                <path d="M7.53553 13.0503L9.58579 11L11 12.4142L8.94975 14.4645C7.96656 15.4477 6.63308 16 5.24264 16C2.34721 16 0 13.6528 0 10.7574C0 9.36693 0.552347 8.03344 1.53553 7.05025L3.58579 5L5 6.41421L2.94975 8.46447C2.34163 9.07258 2 9.89736 2 10.7574C2 12.5482 3.45178 14 5.24264 14C6.10264 14 6.92742 13.6584 7.53553 13.0503Z"/>
                <path d="M5.70711 11.7071L11.7071 5.70711L10.2929 4.29289L4.29289 10.2929L5.70711 11.7071Z"/>
            </svg>
        </span>
    </div>
</a>
