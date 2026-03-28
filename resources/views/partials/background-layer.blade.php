
@php
    use Illuminate\Support\Facades\Cache;

    $siteName        = $settings['site_name'] ?? config('app.name', 'AuraNexus');
    $siteParts = explode(' ', $siteName); // splits by space
    $firstPart = $siteParts[0] ?? $siteName;
    $secondPart = $siteParts[1] ?? ''; // empty if no second word

@endphp



<div class="hidden [@media(min-width:1620px)]:block fixed -right-25 top-1/2 -rotate-90 select-none pointer-events-none opacity-[0.15] group-hover:opacity-[0.24] transition-opacity duration-700 z-50">
    <h3 class="text-7xl font-black text-[var(--an-text)] uppercase tracking-tighter whitespace-nowrap italic">
        {{ $firstPart }}
        @if($secondPart)
            <span class="text-transparent dark:text-black pl-2" style="-webkit-text-stroke: 1px white;">
                {{ $secondPart }}
            </span>
        @endif
    </h3>
</div>
{{-- Ambient Animated Blur Background --}}
<div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">

    <!-- Primary -->     
    <div class="absolute h-[700px] w-[700px] rounded-full mix-blend-lighten filter blur-[150px] opacity-35 animate-blob bg-[var(--an-primary)] top-[-150px] left-[-150px]"></div>

    <!-- Link / Secondary -->
    <div class="absolute h-[700px] w-[700px] rounded-full mix-blend-lighten filter blur-[150px] opacity-20 animate-blob animation-delay-2000 bg-[var(--an-link)] bottom-[-200px] right-[-200px]"></div>
</div>

