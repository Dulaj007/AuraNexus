<?php

namespace App\Services;

use App\Models\Setting;

class ThemeService
{
    public function prefix(): string
    {
        // You can keep "an" (AuraNexus)
        return (string) config('admin-theme.prefix', 'an');
    }

    public function mode(): string
    {
        $key = (string) config('admin-theme.mode_key', 'theme_mode');
        $mode = Setting::get($key, 'dark');

        return in_array($mode, ['dark', 'light'], true) ? $mode : 'dark';
    }

    public function defaults(string $mode): array
    {
        $defaults = (array) config('admin-theme.defaults', []);

        $dark = isset($defaults['dark']) && is_array($defaults['dark']) ? $defaults['dark'] : [];
        $light = isset($defaults['light']) && is_array($defaults['light']) ? $defaults['light'] : [];

        // If you haven't added config/admin-theme.php yet, keep safe fallbacks:
        if (empty($dark)) $dark = $this->darkDefaults();
        if (empty($light)) $light = $this->lightDefaults();

        return $mode === 'light' ? $light : $dark;
    }

    public function lightOverrides(): array
    {
        $key = (string) config('admin-theme.light_vars_key', 'theme_light_vars');
        $saved = json_decode(Setting::get($key, '{}'), true);

        return is_array($saved) ? $saved : [];
    }

    public function resolvedVars(): array
    {
        $mode = $this->mode();

        if ($mode === 'dark') {
            return $this->defaults('dark');
        }

        return array_merge(
            $this->defaults('light'),
            $this->lightOverrides()
        );
    }

    public function css(): string
    {
        $prefix = $this->prefix();
        $vars = $this->resolvedVars();

        // Build CSS variables like: --an-bg: #fff;
        $lines = [];
        foreach ($vars as $key => $value) {
            $k = trim((string) $key);
            $v = trim((string) $value);

            if ($k === '' || $v === '') continue;
            if (str_contains($k, ';') || str_contains($v, ';')) continue; // basic safety

            $lines[] = "--{$prefix}-{$k}: {$v};";
        }

        $root = ":root{\n  " . implode("\n  ", $lines) . "\n}";

        // Optional: some helpful defaults for admin UI (use vars in Tailwind via inline styles/classes)
        $base = <<<CSS
/* Admin theme variables */
{$root}

/* Small base helpers (optional) */
[data-admin-theme="dark"] { color-scheme: dark; }
[data-admin-theme="light"] { color-scheme: light; }
CSS;

        return $base;
    }

    /**
     * This matches your AppServiceProvider expectations:
     * $theme['mode'], $theme['css'], $theme['vars']
     */
    public function payload(): array
    {
        return [
            'mode' => $this->mode(),
            'vars' => $this->resolvedVars(),
            'css'  => $this->css(),
        ];
    }

    // ---- Fallback defaults if config/admin-theme.php isn't ready yet ----

    private function lightDefaults(): array
    {
        return [
            'bg' => '#f6f7fb',
            'bg-2' => '#eef1f7',
            'card' => '#ffffff',
            'card-2' => '#f9fafc',

            'text' => '#0f172a',
            'text-muted' => '#475569',
            'border' => '#e2e8f0',
            'shadow' => 'rgba(2,6,23,.10)',

            'primary' => '#4f46e5',
            'primary-2' => '#4338ca',
            'link' => '#2563eb',

            'success' => '#16a34a',
            'warning' => '#f59e0b',
            'danger' => '#dc2626',
            'info' => '#0284c7',

            'btn' => '#0f172a',
            'btn-text' => '#ffffff',
            'btn-hover' => '#111827',

            'input-bg' => '#ffffff',
            'input-text' => '#0f172a',
            'input-border' => '#cbd5e1',

            'ring' => 'rgba(79,70,229,.35)',
        ];
    }

    private function darkDefaults(): array
    {
        return [
            'bg' => '#0b1220',
            'bg-2' => '#0f172a',
            'card' => '#0f172a',
            'card-2' => '#111c33',

            'text' => '#e5e7eb',
            'text-muted' => '#94a3b8',
            'border' => 'rgba(148,163,184,.18)',
            'shadow' => 'rgba(0,0,0,.35)',

            'primary' => '#7c3aed',
            'primary-2' => '#6d28d9',
            'link' => '#60a5fa',

            'success' => '#22c55e',
            'warning' => '#f59e0b',
            'danger' => '#ef4444',
            'info' => '#38bdf8',

            'btn' => '#e5e7eb',
            'btn-text' => '#0b1220',
            'btn-hover' => '#ffffff',

            'input-bg' => '#0b1220',
            'input-text' => '#e5e7eb',
            'input-border' => 'rgba(148,163,184,.25)',

            'ring' => 'rgba(124,58,237,.45)',
        ];
    }
}
