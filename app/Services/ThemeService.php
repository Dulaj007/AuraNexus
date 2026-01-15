<?php

namespace App\Services;

use App\Models\Setting;

class ThemeService
{
    public function prefix(): string
    {
        return (string) config('admin-theme.prefix', 'an');
    }

    /**
     * Mode should be per-user (cookie/session/localStorage), NOT global setting.
     * We accept it from caller and validate.
     */
    public function mode(?string $mode = null): string
    {
        $mode = $mode ?? 'dark';
        return in_array($mode, ['dark', 'light'], true) ? $mode : 'dark';
    }

    public function payload(?string $mode = null): array
    {
        $mode = $this->mode($mode);
        $vars = $this->resolvedVars($mode);

        return [
            'mode' => $mode,
            'vars' => $vars,
            'css'  => $this->cssFromVars($vars),
        ];
    }

    public function css(?string $mode = null): string
    {
        return $this->payload($mode)['css'];
    }

    public function resolvedVars(?string $mode = null): array
    {
        $mode = $this->mode($mode);

        // 3 priority accents from admin panel (admin-only can edit, but everyone uses them)
        $primary   = $this->normalizeHex(Setting::get('theme_primary', '#8B5CF6'))   ?? '#8B5CF6';
        $secondary = $this->normalizeHex(Setting::get('theme_secondary', '#22D3EE')) ?? '#22D3EE';
        $tertiary  = $this->normalizeHex(Setting::get('theme_tertiary', '#FBBF24'))  ?? '#FBBF24';

        $p = $this->accentPack($primary);
        $s = $this->accentPack($secondary);
        $t = $this->accentPack($tertiary);

        if ($mode === 'dark') {
            // PURE BLACK base
            return [
                'bg' => '#000000',
                'bg-2' => '#07070A',

                // glass-like cards
                'card' => 'rgba(255,255,255,.04)',
                'card-2' => 'rgba(255,255,255,.06)',

                'text' => '#FFFFFF',
                'text-muted' => 'rgba(255,255,255,.65)',
                'border' => 'rgba(255,255,255,.12)',

                // subtle glow / depth (use as box-shadow color if needed)
                'shadow' => 'rgba(255,255,255,.08)',

                // Primary is MAIN THEME
                'primary' => $p['base'],
                'primary-2' => $p['dark'],
                'ring' => $this->rgba($p['base'], 0.35),

                // Secondary drives links/navigation
                'link' => $s['base'],

                // Tertiary drives info accents (chips, badges etc.)
                'info' => $t['base'],

                // status colors
                'success' => '#22C55E',
                'warning' => '#F59E0B',
                'danger' => '#EF4444',

                // Buttons use primary
                'btn' => $p['base'],
                'btn-text' => '#FFFFFF',
                'btn-hover' => $p['dark'],

                // Inputs
                'input-bg' => 'rgba(255,255,255,.04)',
                'input-text' => '#FFFFFF',
                'input-border' => 'rgba(255,255,255,.14)',
            ];
        }

        // LIGHT mode: PURE WHITE base
        return [
            'bg' => '#FFFFFF',
            'bg-2' => '#F6F7FB',

            'card' => '#FFFFFF',
            'card-2' => '#F8FAFC',

            'text' => '#000000',
            'text-muted' => 'rgba(0,0,0,.62)',
            'border' => 'rgba(0,0,0,.10)',

            'shadow' => 'rgba(0,0,0,.12)',

            'primary' => $p['base'],
            'primary-2' => $p['dark'],
            'ring' => $this->rgba($p['base'], 0.30),

            'link' => $s['base'],
            'info' => $t['base'],

            'success' => '#16A34A',
            'warning' => '#D97706',
            'danger' => '#DC2626',

            'btn' => $p['base'],
            'btn-text' => '#FFFFFF',
            'btn-hover' => $p['dark'],

            'input-bg' => '#FFFFFF',
            'input-text' => '#000000',
            'input-border' => 'rgba(0,0,0,.18)',
        ];
    }

    private function cssFromVars(array $vars): string
    {
        $prefix = $this->prefix();

        $lines = [];
        foreach ($vars as $k => $v) {
            $k = trim((string) $k);
            $v = trim((string) $v);
            if ($k === '' || $v === '') continue;
            if (str_contains($k, ';') || str_contains($v, ';')) continue;
            $lines[] = "--{$prefix}-{$k}: {$v};";
        }

        $root = ":root{\n  " . implode("\n  ", $lines) . "\n}\n";

        return <<<CSS
/* AuraNexus theme variables (admin + public) */
{$root}
:root[data-theme="dark"]  { color-scheme: dark; }
:root[data-theme="light"] { color-scheme: light; }
CSS;
    }

    /**
     * Build base/dark/light variants from a single hex accent.
     */
    private function accentPack(string $hex): array
    {
        return [
            'base'  => $hex,
            'dark'  => $this->mix($hex, '#000000', 0.22),
            'light' => $this->mix($hex, '#FFFFFF', 0.18),
        ];
    }

    private function normalizeHex(string $val): ?string
    {
        $v = trim($val);
        if ($v !== '' && $v[0] !== '#') $v = '#'.$v;

        if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $v)) return null;

        if (strlen($v) === 4) {
            $v = '#'.$v[1].$v[1].$v[2].$v[2].$v[3].$v[3];
        }

        return strtoupper($v);
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return [$r, $g, $b];
    }

    private function rgbToHex(int $r, int $g, int $b): string
    {
        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));
        return sprintf('#%02X%02X%02X', $r, $g, $b);
    }

    /**
     * Mix color A towards color B by $t (0..1).
     */
    private function mix(string $a, string $b, float $t): string
    {
        [$ar, $ag, $ab] = $this->hexToRgb($a);
        [$br, $bg, $bb] = $this->hexToRgb($b);

        $r  = (int) round($ar + ($br - $ar) * $t);
        $g  = (int) round($ag + ($bg - $ag) * $t);
        $b2 = (int) round($ab + ($bb - $ab) * $t);

        return $this->rgbToHex($r, $g, $b2);
    }

    private function rgba(string $hex, float $alpha): string
    {
        [$r, $g, $b] = $this->hexToRgb($hex);
        $a = max(0, min(1, $alpha));
        return "rgba({$r},{$g},{$b},{$a})";
    }
}
