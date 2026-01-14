@extends('layouts.admin')

@section('title', 'Theme')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold">Theme</h1>
            <p class="text-sm text-[color:var(--an-text-muted)]">Manage admin panel theme variables.</p>
        </div>

        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('admin.theme.resetLight') }}">
                @csrf
                <x-admin.ui.button variant="ghost" type="submit">
                    Reset light theme
                </x-admin.ui.button>
            </form>

            <x-admin.ui.badge>
                Theme: {{ strtoupper($mode ?? 'dark') }}
            </x-admin.ui.badge>
        </div>
    </div>

    <x-admin.ui.alert variant="info" class="mb-6">
        <div class="font-medium">Note</div>
        <div class="text-sm opacity-90">
            These values are saved as <b>Light theme overrides</b>. To see changes applied, switch <b>Theme Mode</b> to <b>Light</b> and save.
        </div>
    </x-admin.ui.alert>

    <form method="POST" action="{{ route('admin.theme.update') }}">
        @csrf

        <div class="mb-6">
            <label class="block text-sm font-medium mb-2">Theme mode</label>
            <div class="flex gap-3">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="theme_mode" value="dark" {{ ($mode ?? 'dark') === 'dark' ? 'checked' : '' }}>
                    <span>Dark</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="theme_mode" value="light" {{ ($mode ?? 'dark') === 'light' ? 'checked' : '' }}>
                    <span>Light</span>
                </label>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($vars as $key)
                @php
                    $val = $values[$key] ?? '';
                    $def = $defaults[$key] ?? '';
                    $isColor = is_string($val) && (str_starts_with($val, '#') || str_starts_with($val, 'rgb') || str_starts_with($val, 'hsl'));
                @endphp

                <x-admin.card>
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold">{{ $key }}</div>
                            @if($def !== '')
                                <div class="text-xs text-[color:var(--an-text-muted)] mt-1">
                                    Default: <span class="font-mono">{{ $def }}</span>
                                </div>
                            @endif
                        </div>

                        <span class="text-xs font-mono px-2 py-1 rounded border border-[color:var(--an-border)] text-[color:var(--an-text-muted)]">
                            --an-{{ $key }}
                        </span>
                    </div>

                    <div class="mt-4 flex items-center gap-3">
                        {{-- Color picker (works best for hex colors; still useful as quick picker) --}}
                        <input
                            type="color"
                            class="h-10 w-14 rounded border border-[color:var(--an-border)] bg-[color:var(--an-input-bg)]"
                            data-color-for="{{ $key }}"
                            value="{{ str_starts_with($val, '#') ? $val : (str_starts_with($def, '#') ? $def : '#000000') }}"
                        />

                        {{-- Raw value input (supports rgba(), hsl(), keywords like transparent, etc.) --}}
                        <input
                            type="text"
                            name="vars[{{ $key }}]"
                            value="{{ $val }}"
                            class="flex-1 h-10 px-3 rounded border border-[color:var(--an-input-border)] bg-[color:var(--an-input-bg)] text-[color:var(--an-input-text)]"
                            placeholder="{{ $def !== '' ? $def : 'Enter CSS value' }}"
                            data-value-for="{{ $key }}"
                        />
                    </div>

                    <div class="mt-3 flex items-center gap-2 text-xs text-[color:var(--an-text-muted)]">
                        <span>Preview:</span>
                        <span
                            class="inline-block w-10 h-5 rounded border border-[color:var(--an-border)]"
                            data-preview-for="{{ $key }}"
                            style="background: {{ $val !== '' ? $val : $def }};"
                        ></span>
                        <span class="font-mono opacity-90" data-preview-text="{{ $key }}">{{ $val !== '' ? $val : $def }}</span>
                    </div>
                </x-admin.card>
            @endforeach
        </div>

        <div class="mt-6 flex justify-end">
            <x-admin.ui.button type="submit">
                Save changes
            </x-admin.ui.button>
        </div>
    </form>

    <script>
        // Sync color input -> text input (only for hex colors)
        document.querySelectorAll('[data-color-for]').forEach((colorEl) => {
            const key = colorEl.getAttribute('data-color-for');
            const textEl = document.querySelector(`[data-value-for="${key}"]`);
            const previewEl = document.querySelector(`[data-preview-for="${key}"]`);
            const previewTextEl = document.querySelector(`[data-preview-text="${key}"]`);

            if (!textEl) return;

            colorEl.addEventListener('input', () => {
                const hex = colorEl.value;
                textEl.value = hex;

                if (previewEl) previewEl.style.background = hex;
                if (previewTextEl) previewTextEl.textContent = hex;
            });

            textEl.addEventListener('input', () => {
                const v = textEl.value.trim();
                if (previewEl) previewEl.style.background = v || 'transparent';
                if (previewTextEl) previewTextEl.textContent = v || '';
            });
        });
    </script>
@endsection
