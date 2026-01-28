@props([
    'categories' => collect(),
    'glass' => 'rounded-3xl border border-[var(--an-border)] bg-[color:var(--an-card)]/65 backdrop-blur-xl',
    'muted' => 'color: color-mix(in srgb, var(--an-text) 65%, transparent);',
])


<div class="space-y-2 py-1">
    @foreach(($categories ?? collect()) as $cat)
        <section class="{{ $glass }} overflow-hidden">

            {{-- Category header --}}
            <div class="px-3 py-3 border-b border-[var(--an-border)]
                        bg-[var(--an-primary)]/30">
                <div class="flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        {{-- ✅ clickable category name --}}
                        <a href="{{ route('categories.show', $cat) }}"
                           class="inline-flex items-center gap-2 text-sm sm:text-base font-extrabold text-[var(--an-text)]
                                  hover:opacity-90 transition"
                           aria-label="Open category: {{ $cat->name }}">
                            <span class="line-clamp-2">{{ $cat->name }}</span>

                            {{-- tiny arrow (desktop hint) --}}
                            <svg viewBox="0 0 24 24" fill="none"
                                 class="h-4 w-4 opacity-70 hidden sm:block"
                                 stroke="currentColor" stroke-width="2"
                                 stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 18l6-6-6-6"/>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- Forums list --}}
            <div class="divide-y divide-[var(--an-border)]">
                @foreach(($cat->forums ?? collect()) as $forum)
                    @php
                        $previewPost = $forum->latestPublishedPost ?? null;

                        // ✅ New simple thumbnail source (NO parsing)
                        $img = $previewPost?->thumbnail_url;

                        $alt       = $previewPost?->title ?? $forum->name;
                        $titleAttr = $previewPost?->title ?? $forum->name;

                        $postsCount = (int) ($forum->posts_count ?? 0);
                    @endphp


                    <a href="{{ route('forums.show', $forum) }}"
                       class="block p-1 py-[5px] sm:p-4 hover:bg-white/5 transition bg-[var(--an-primary)]/5 ">

                        <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden opacity-0 ">
                            <div class="absolute -top-40 -left-40 h-[520px] w-[520px] rounded-full blur-3xl opacity-15 bg-[var(--an-link)]"></div>
                            <div class="absolute top-24 -right-48 h-[620px] w-[620px] rounded-full blur-3xl opacity-12 bg-[var(--an-primary)]"></div>
                            <div class="absolute bottom-[-220px] left-[25%] h-[520px] w-[520px] rounded-full blur-[140px] opacity-10 bg-[var(--an-info)]"></div>
                        </div>

                        {{-- ✅ PC-friendly: keep mobile exactly, only adjust on sm+ --}}
                        <div class="flex gap-2 sm:gap-4 sm:items-center">

                            {{-- Left (1/3): image --}}
                            <div class="shrink-0 w-[28%] sm:w-[260px] md:w-[300px]">
                                <div class="relative aspect-[10/11] sm:aspect-[16/9] overflow-hidden rounded-lg
                                            border border-[var(--an-border)] bg-[color:var(--an-card)]/55">
                                @if($img)
                                    <img
                                        src="{{ $img }}"
                                        alt="{{ $alt }}"
                                        title="{{ $titleAttr }}"
                                        loading="lazy"
                                        decoding="async"
                                        width="300"
                                        height="300"
                                        class="absolute inset-0 h-full w-full object-cover"
                                        onerror="
                                            this.onerror=null;
                                            this.closest('div').innerHTML =
                                            '<div class=&quot;h-full w-full flex items-center justify-center text-[10px]&quot; style=&quot;color: var(--an-text-muted)&quot;>No preview</div>';
                                        "
                                    >
                                @else
                                    <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.12),transparent_60%)]"></div>
                                    <div class="absolute inset-0 bg-gradient-to-br from-[var(--an-primary)]/18 via-transparent to-[var(--an-secondary)]/12"></div>

                                    <div class="absolute bottom-2 left-2 right-2 text-[10px] font-extrabold text-white/85 line-clamp-2">
                                        {{ $previewPost?->title ?? 'Latest from this forum' }}
                                    </div>
                                @endif

                                </div>
                            </div>

                            {{-- Right (2/3): details --}}
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2 pt-1">
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-[13px] sm:text-lg text-[var(--an-text)] line-clamp-2">
                                            {{ $forum->name }}
                                        </div>
                                        <div class="text-[11px] sm:text-sm md:text-[15px] line-clamp-4" style="{{ $muted }}">
                                            {{ $forum->description ?: '—' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-2 flex items-center justify-between gap-2">

                                    {{-- Stats --}}
                                    <div class="flex items-center gap-3 sm:gap-5 text-[11px] sm:text-sm" style="{{ $muted }}">

                                        {{-- Posts --}}
                                        <span class="inline-flex items-center gap-1">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                                 style="color: var(--an-text-muted)">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                                <path d="M14 2v6h6"/>
                                                <path d="M8 13h8"/><path d="M8 17h6"/>
                                            </svg>
                                            <span class="font-extrabold" style="color: var(--an-text);">
                                                {{ number_format($postsCount) }}
                                            </span>
                                        </span>


                                    </div>

                                    {{-- Arrow --}}
                                    <div class="shrink-0 sm:rounded-xl sm:border sm:border-[var(--an-border)]
                                                sm:bg-[color:var(--an-card)]/50 sm:hover:bg-[color:var(--an-card)]/80
                                                sm:px-2 sm:py-1 transition">
                                        <svg viewBox="0 0 24 24" fill="none"
                                             xmlns="http://www.w3.org/2000/svg"
                                             class="h-6 w-9 rotate-180 sm:h-5 sm:w-5"
                                             stroke="currentColor" stroke-width="2"
                                             stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 12H20M4 12L8 8M4 12L8 16"/>
                                        </svg>
                                    </div>

                                </div>
                            </div>

                        </div>
                    </a>
                @endforeach
            </div>

        </section>
    @endforeach
</div>
