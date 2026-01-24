{{-- resources/views/partials/footer.blade.php --}}
@php
    use Illuminate\Support\Str;

    // ✅ Use shared settings (from AppServiceProvider)
    $settings = $siteSettings ?? [];

    $siteName = $settings['site_name'] ?? config('app.name', 'AuraNexus');
    $siteDesc = $settings['site_description'] ?? ('Explore community updates on ' . $siteName . '.');

    // footer_links stored as JSON string in settings.value
    $rawLinks = $settings['footer_links'] ?? null;

    $decoded = [];
    if (is_string($rawLinks) && trim($rawLinks) !== '') {
        $tmp = json_decode($rawLinks, true);
        if (is_array($tmp)) $decoded = $tmp;
    } elseif (is_array($rawLinks)) {
        // safety: if some old version stored array
        $decoded = $rawLinks;
    }

    $footerLinks = collect($decoded)
        ->map(function ($item) {
            $label = trim((string) data_get($item, 'label', ''));
            $href  = trim((string) data_get($item, 'href', ''));

            if ($label === '' || $href === '') return null;

            // normalize "storage/..." into "/storage/..."
            if (Str::startsWith($href, 'storage/')) $href = '/' . $href;

            // allow relative (/privacy) or absolute (https://...)
            $ok = Str::startsWith($href, ['/', 'http://', 'https://']);
            if (!$ok) return null;

            $isExternal = Str::startsWith($href, ['http://', 'https://']);

            return [
                'label' => $label,
                'href'  => $href,
                'ext'   => $isExternal,
            ];
        })
        ->filter()
        ->take(30)
        ->values()
        ->all();

    $glass = 'border-t border-[var(--an-border)] bg-[var(--an-card)]/70 backdrop-blur';
@endphp

<footer class="{{ $glass }}">
    <div class="max-w-7xl mx-auto px-4 pt-6 text-center ">
        <div class="flex flex-col gap-6  sm:items-center sm:justify-center">

            <div class="min-w-0">
   


            @if(!empty($footerLinks))
                <nav aria-label="Footer links" class="flex flex-wrap gap-x-4 mt-1 gap-y-2 text-xs justify-center">
                    @foreach($footerLinks as $l)
                        <a href="{{ $l['href'] }}"
                           @if($l['ext']) target="_blank" rel="noopener noreferrer" @endif
                           class="underline underline-offset-4 hover:no-underline transition uppercase
                                  active:scale-95 active:translate-y-[1px] text-[var(--an-text-muted)]"
                          >
                            {{ $l['label'] }}
                        </a>
                    @endforeach
                </nav>
            @else
                <div class="text-xs text-[var(--an-text-muted)] sm:text-right">
                    Community
                </div>
            @endif
                <div class="mt-3 text-[11px] text-[var(--an-text-muted)] mb-1">
                 {{ date('Y') }} {{ $siteName }}
                </div>
            </div>



        </div>
    </div>
</footer>
