@props([
    'items' => [], // [['label'=>'Forums','url'=>...], ...]
    'current' => null
])

<div class="flex px-3 sm:px-0 flex-wrap items-center gap-1.5 text-[11px] sm:text-[12px] text-[var(--an-text-muted)]">

    @foreach($items as $item)
        @if(!$loop->first)
            <span>/</span>
        @endif

        <a href="{{ $item['url'] }}" 
           class="hover:text-[var(--an-primary)] transition-colors">
            {{ $item['label'] }}
        </a>
    @endforeach

    @if($current)
        <span>/</span>
        <span class="text-[var(--an-primary)] truncate max-w-[140px] sm:max-w-none">
            {{ $current }}
        </span>
    @endif

</div>