@props([
    'id',
    'title' => null,
    'maxWidth' => 'max-w-lg', // max-w-md|max-w-lg|max-w-2xl...
])

<div id="{{ $id }}" class="fixed inset-0 z-50 hidden">
    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-black/60" onclick="adminModalClose('{{ $id }}')"></div>

    {{-- Panel --}}
    <div class="relative mx-auto mt-24 w-[92%] {{ $maxWidth }}">
        <div class="rounded-2xl border border-[var(--an-border)] bg-[var(--an-card)] shadow-xl">
            <div class="flex items-center justify-between gap-3 border-b border-[var(--an-border)] px-5 py-4">
                <div class="text-base font-semibold text-[var(--an-text)]">
                    {{ $title ?? 'Modal' }}
                </div>
                <button type="button"
                        class="rounded-xl px-2 py-1 text-[var(--an-text-muted)] hover:bg-[var(--an-card-2)]"
                        onclick="adminModalClose('{{ $id }}')">
                    ✕
                </button>
            </div>

            <div class="px-5 py-4">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

@once
    <script>
        function adminModalOpen(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function adminModalClose(id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('hidden');
            document.body.style.overflow = '';
        }
    </script>
@endonce
