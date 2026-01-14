@php
    use App\Models\Setting;

    $mode = Setting::get('theme_mode', 'dark');

    // Dark theme is FIXED (not editable)
    $dark = [
        'bg' => '#020617',
        'bg-2' => '#0b1220',
        'card' => 'rgba(255,255,255,.04)',
        'card-2' => 'rgba(255,255,255,.06)',
        'text' => '#e5e7eb',
        'text-muted' => 'rgba(255,255,255,.55)',
        'border' => 'rgba(255,255,255,.10)',
        'shadow' => 'rgba(0,0,0,.35)',
        'primary' => '#6366f1',
        'primary-2' => '#4f46e5',
        'link' => '#93c5fd',
        'success' => '#22c55e',
        'warning' => '#f59e0b',
        'danger' => '#ef4444',
        'info' => '#38bdf8',
        'btn' => 'rgba(255,255,255,.10)',
        'btn-text' => '#ffffff',
        'btn-hover' => 'rgba(255,255,255,.16)',
        'input-bg' => 'rgba(255,255,255,.06)',
        'input-text' => '#ffffff',
        'input-border' => 'rgba(255,255,255,.12)',
        'ring' => 'rgba(99,102,241,.35)',
    ];

    $lightDefaults = (new \App\Http\Controllers\Admin\ThemeController())->edit(request())->getData()['defaults'] ?? null;
    // NOTE: the above line is NOT ideal in production (controller call inside view).
    // We'll replace it properly later with a ThemeService or config array.

    $light = json_decode(Setting::get('theme_light_vars', '{}'), true) ?: [];
    $defaults = $lightDefaults ?: [
        'bg' => '#f6f7fb', 'bg-2' => '#eef1f7', 'card' => '#ffffff', 'card-2' => '#f9fafc',
        'text' => '#0f172a','text-muted' => '#475569','border' => '#e2e8f0','shadow' => 'rgba(2,6,23,.10)',
        'primary' => '#4f46e5','primary-2' => '#4338ca','link' => '#2563eb',
        'success' => '#16a34a','warning' => '#f59e0b','danger' => '#dc2626','info' => '#0284c7',
        'btn' => '#0f172a','btn-text' => '#ffffff','btn-hover' => '#111827',
        'input-bg' => '#ffffff','input-text' => '#0f172a','input-border' => '#cbd5e1',
        'ring' => 'rgba(79,70,229,.35)',
    ];

    $final = $mode === 'light'
        ? array_merge($defaults, $light)
        : $dark;
@endphp

<style>
    :root {
        --bg: {{ $final['bg'] }};
        --bg-2: {{ $final['bg-2'] }};
        --card: {{ $final['card'] }};
        --card-2: {{ $final['card-2'] }};
        --text: {{ $final['text'] }};
        --text-muted: {{ $final['text-muted'] }};
        --border: {{ $final['border'] }};
        --shadow: {{ $final['shadow'] }};
        --primary: {{ $final['primary'] }};
        --primary-2: {{ $final['primary-2'] }};
        --link: {{ $final['link'] }};
        --success: {{ $final['success'] }};
        --warning: {{ $final['warning'] }};
        --danger: {{ $final['danger'] }};
        --info: {{ $final['info'] }};
        --btn: {{ $final['btn'] }};
        --btn-text: {{ $final['btn-text'] }};
        --btn-hover: {{ $final['btn-hover'] }};
        --input-bg: {{ $final['input-bg'] }};
        --input-text: {{ $final['input-text'] }};
        --input-border: {{ $final['input-border'] }};
        --ring: {{ $final['ring'] }};
    }
</style>
