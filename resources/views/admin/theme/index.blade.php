@extends('layouts.admin')

@section('title', 'Theme')

@section('content')
<div class="max-w-5xl mx-auto space-y-6 pt-5">



    {{-- Main card --}}
    <div class="rounded-2xl border p-6"
         style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 12px 35px var(--an-shadow);">

        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold">Theme Settings</h1>
                <p class="mt-1 text-sm" style="color: var(--an-text-muted);">
                    AuraNexus uses 3 priority accent colors. Everything else is generated automatically.
                </p>
            </div>

            {{-- Reset --}}
            <form method="POST" action="{{ route('admin.theme.reset') }}">
                @csrf
                <button
                    class="px-4 py-2 rounded-xl border font-medium transition"
                    style="border-color: var(--an-border); color: var(--an-text); background: transparent;">
                    Reset
                </button>
            </form>
        </div>

        {{-- Save --}}
        <form method="POST" action="{{ route('admin.theme.update') }}" class="mt-6 space-y-6">
            @csrf

            {{-- Mode --}}
            <div class="grid md:grid-cols-2 gap-4">
                <div class="rounded-2xl border p-4"
                     style="background: var(--an-card-2); border-color: var(--an-border);">
                    <label class="block text-sm font-medium">Theme Mode</label>
        <select
            name="theme_mode"
            class="mt-2 w-full rounded-xl border px-3 py-2 appearance-none"
            style="
                background: var(--an-input-bg);
                border-color: var(--an-input-border);
                color: var(--an-input-text);
            "
        >
            <option value="dark" @selected($mode === 'dark')
                style="background: var(--an-input-bg); color: black;">
                Dark (Pure Black)
            </option>

            <option value="light" @selected($mode === 'light')
                style="background: var(--an-input-bg); color: black">
                Light (Pure White)
            </option>
        </select>

                </div>

                <div class="rounded-2xl border p-4"
                     style="background: var(--an-card-2); border-color: var(--an-border);">
                    <p class="text-sm font-medium">Accent Priority</p>
                    <ul class="mt-2 text-sm space-y-1" style="color: var(--an-text-muted);">
                        <li><b style="color:var(--an-text)">Primary</b> → buttons, active states, focus ring</li>
                        <li><b style="color:var(--an-text)">Secondary</b> → links, tabs, navigation</li>
                        <li><b style="color:var(--an-text)">Tertiary</b> → chips, info, highlights</li>
                    </ul>
                </div>
            </div>

            {{-- Accent colors --}}
            <div class="grid md:grid-cols-3 gap-4">

                {{-- PRIMARY --}}
                <div class="rounded-2xl border p-4"
                     style="background: var(--an-card-2); border-color: var(--an-border);">
                    <label class="block text-sm font-medium">Primary (Main)</label>

                    <div class="mt-2 flex items-center gap-3">
                        <input
                            type="color"
                            id="theme_primary_color"
                            name="theme_primary"
                            value="{{ old('theme_primary', $primary) }}"
                            class="h-10 w-12 rounded-lg border"
                            style="border-color: var(--an-border); background: transparent;"
                        />

                        <input
                            type="text"
                            id="theme_primary_text"
                            value="{{ old('theme_primary', $primary) }}"
                            class="h-10 w-full rounded-xl border px-3"
                            style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                            placeholder="#8B5CF6"
                        />
                    </div>
                </div>

                {{-- SECONDARY --}}
                <div class="rounded-2xl border p-4"
                     style="background: var(--an-card-2); border-color: var(--an-border);">
                    <label class="block text-sm font-medium">Secondary</label>

                    <div class="mt-2 flex items-center gap-3">
                        <input
                            type="color"
                            id="theme_secondary_color"
                            name="theme_secondary"
                            value="{{ old('theme_secondary', $secondary) }}"
                            class="h-10 w-12 rounded-lg border"
                            style="border-color: var(--an-border); background: transparent;"
                        />

                        <input
                            type="text"
                            id="theme_secondary_text"
                            value="{{ old('theme_secondary', $secondary) }}"
                            class="h-10 w-full rounded-xl border px-3"
                            style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                            placeholder="#22D3EE"
                        />
                    </div>
                </div>

                {{-- TERTIARY --}}
                <div class="rounded-2xl border p-4"
                     style="background: var(--an-card-2); border-color: var(--an-border);">
                    <label class="block text-sm font-medium">Tertiary</label>

                    <div class="mt-2 flex items-center gap-3">
                        <input
                            type="color"
                            id="theme_tertiary_color"
                            name="theme_tertiary"
                            value="{{ old('theme_tertiary', $tertiary) }}"
                            class="h-10 w-12 rounded-lg border"
                            style="border-color: var(--an-border); background: transparent;"
                        />

                        <input
                            type="text"
                            id="theme_tertiary_text"
                            value="{{ old('theme_tertiary', $tertiary) }}"
                            class="h-10 w-full rounded-xl border px-3"
                            style="background: var(--an-input-bg); border-color: var(--an-input-border); color: var(--an-input-text);"
                            placeholder="#FBBF24"
                        />
                    </div>
                </div>

            </div>

            {{-- Save --}}
            <div class="flex justify-end">
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-xl font-semibold transition"
                    style="background: var(--an-btn); color: var(--an-btn-text); box-shadow: 0 12px 30px var(--an-shadow);">
                    Save Theme
                </button>
            </div>
        </form>
    </div>

    {{-- Preview --}}
    <div class="rounded-2xl border p-6"
         style="background: var(--an-card); border-color: var(--an-border); box-shadow: 0 12px 35px var(--an-shadow);">

        <h2 class="text-lg font-semibold">Live Preview</h2>

        <div class="mt-4 grid md:grid-cols-3 gap-4">
            <div class="rounded-2xl border p-4"
                 style="background: var(--an-card-2); border-color: var(--an-border);">
                <p class="text-sm text-muted">Primary button</p>
                <button class="mt-3 w-full rounded-xl px-4 py-2 font-semibold"
                        style="background: var(--an-primary); color: var(--an-btn-text); box-shadow: 0 12px 30px var(--an-shadow);">
                    Action
                </button>
            </div>

            <div class="rounded-2xl border p-4"
                 style="background: var(--an-card-2); border-color: var(--an-border);">
                <p class="text-sm text-muted">Secondary link</p>
                <a href="#" class="mt-3 inline-block font-semibold"
                   style="color: var(--an-link);">
                    Example link →
                </a>
            </div>

            <div class="rounded-2xl border p-4"
                 style="background: var(--an-card-2); border-color: var(--an-border);">
                <p class="text-sm text-muted">Tertiary highlight</p>
                <span class="inline-flex mt-3 rounded-full px-3 py-1 text-sm font-semibold"
                      style="color: var(--an-info); border:1px solid var(--an-border);">
                    Highlight chip
                </span>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
(function () {
  function bind(colorId, textId) {
    const c = document.getElementById(colorId);
    const t = document.getElementById(textId);
    if (!c || !t) return;

    c.addEventListener('input', () => {
      t.value = c.value.toUpperCase();
    });

    t.addEventListener('input', () => {
      let v = t.value.trim();
      if (!v) return;
      if (v[0] !== '#') v = '#' + v;

      if (!/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/.test(v)) return;

      if (v.length === 4) {
        v = '#' + v[1]+v[1] + v[2]+v[2] + v[3]+v[3];
      }
      c.value = v.toLowerCase();
    });
  }

  bind('theme_primary_color', 'theme_primary_text');
  bind('theme_secondary_color', 'theme_secondary_text');
  bind('theme_tertiary_color', 'theme_tertiary_text');
})();
</script>
@endpush
