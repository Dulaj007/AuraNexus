@props([
    'title',
    'description',
    'postsTotal' => 0,
    'basePath',
    'sort' => 'recent',
    'showSort' => true,  // <- add this
])

<section class="relative overflow-hidden border border-[var(--an-border)] 
    bg-[var(--an-card)]/40 backdrop-blur-xl shadow-2xl">

    {{-- glow --}}
    <div class="absolute top-0 right-0 w-40 sm:w-72 h-40 sm:h-72 bg-[var(--an-primary)]/20 blur-[80px] sm:blur-[100px]"></div>
    <div class="absolute bottom-0 left-0 w-40 sm:w-72 h-40 sm:h-72 bg-[var(--an-info)]/20 blur-[80px] sm:blur-[100px]"></div>

    <div class="relative z-10 p-3 sm:p-6 space-y-4">

        {{-- MARQUEE --}}
        <div class="absolute top-1 inset-x-0 overflow-hidden opacity-[0.05] pointer-events-none">
            <div class="flex whitespace-nowrap">
                <div class="marquee__inner-tool-bar font-black uppercase italic">
                    @for($i=0; $i<6; $i++)
                        <span class="mr-10 sm:mr-15 text-[2rem] sm:text-[4rem] text-[var(--an-primary)]">{{ $title }}</span>
                        <span class="mr-10 sm:mr-15 text-[2rem] sm:text-[4rem] text-[var(--an-text)]">
                            {{ $description }}
                        </span>
                    @endfor
                </div>
            </div>
        </div>

        {{-- HEADER --}}
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">

            {{-- LEFT --}}
            <div class="flex items-start gap-3 lg:gap-4">
                <div class="h-6 sm:h-8 lg:h-14 w-1.5 bg-[var(--an-primary)] rounded-full shadow-[0_0_10px_var(--an-primary)]"></div>

                <div>
                    <span class="text-[9px] sm:text-[10px] font-black text-[var(--an-primary)] uppercase tracking-[0.3em] leading-normal mb-1 block">
                        {{ $description }}
                    </span>

                    <h2 class="text-lg sm:text-2xl lg:text-3xl font-black text-[var(--an-text)] tracking-tight uppercase leading-tight">
                        {{ $title }}
                    </h2>
                </div>
            </div>

            {{-- RIGHT --}}
            <div class="flex flex-wrap items-center gap-3 sm:gap-4">

                {{-- stats --}}
                <div class="flex items-center gap-2 text-[var(--an-text-muted)]">
  <svg class="w-5 h-5 sm:w-6 sm:h-6 text-[var(--an-text)]/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                    <span class="text-[11px]">{{ number_format($postsTotal) }} </span>
                </div>

                {{-- sort --}} 
                @if($showSort ?? true)
                <form method="GET" action="{{ $basePath }}">
                    <div class="relative">
                       
                        <select name="sort" onchange="this.form.submit()"
                            class="appearance-none px-2.5 sm:px-3 py-1.5 text-[11px] sm:text-sm
                            bg-[var(--an-bg)]/40 border border-[var(--an-border)]
                            text-[var(--an-text)] rounded-lg pr-7 sm:pr-8
                            focus:ring-2 focus:ring-[var(--an-primary)] outline-none">

                            <option value="recent" @selected($sort === 'recent')>Newest</option>
                            <option value="popular" @selected($sort === 'popular')>Popular</option>
                            <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                        </select>
                      
    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-[var(--an-text)]/50"> <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg> </div>
                    </div>
                </form>
  @endif
            </div>
        </div>
    </div>
</section>
<style>
.marquee__inner-tool-bar {
    animation: marquee-left 150s linear infinite;
}
</style>