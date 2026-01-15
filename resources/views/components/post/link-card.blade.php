@props(['url', 'label' => null])

<a href="{{ $url }}" target="_blank" rel="nofollow noopener"
   class="block rounded-xl border border-white/10 bg-black/30 p-4 hover:border-white/20 hover:bg-black/40 transition">
    <div class="text-sm text-white/90 break-all">{{ $label ?: $url }}</div>
    <div class="text-xs text-white/50 mt-1">Open link</div>
</a>
