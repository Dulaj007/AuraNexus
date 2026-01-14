<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    /**
     * Only these keys are allowed to be saved as light theme overrides.
     */
    private array $vars = [
        'bg','bg-2','card','card-2',
        'text','text-muted','border','shadow',
        'primary','primary-2','link',
        'success','warning','danger','info',
        'btn','btn-text','btn-hover',
        'input-bg','input-text','input-border',
        'ring',
    ];

    public function edit(Request $request)
    {
        // Extra safety (routes already have auth+admin middleware)
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }

        $mode = Setting::get('theme_mode', 'dark');

        $saved = json_decode(Setting::get('theme_light_vars', '{}'), true);
        $saved = is_array($saved) ? $saved : [];

        $defaults = $this->lightDefaults();

        $values = [];
        foreach ($this->vars as $key) {
            $values[$key] = $saved[$key] ?? $defaults[$key] ?? '';
        }

        // ✅ updated view path to match your new structure:
        // resources/views/admin/theme/index.blade.php
        return view('admin.theme.index', [
            'mode' => $mode,
            'vars' => $this->vars,
            'values' => $values,
            'defaults' => $defaults,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->validate([
            'theme_mode' => ['required', 'in:dark,light'],
            'vars'       => ['nullable', 'array'],
        ]);

        Setting::set('theme_mode', $data['theme_mode']);

        $incoming = is_array($data['vars'] ?? null) ? $data['vars'] : [];

        $clean = [];

        foreach ($this->vars as $k) {
            if (!array_key_exists($k, $incoming)) {
                continue;
            }

            $val = trim((string) $incoming[$k]);

            // Prevent CSS injection-ish attempts
            // disallow: ; { } < >
            if ($val === '' || strpbrk($val, ';{}<>') !== false) {
                continue;
            }

            // Keep length reasonable (avoid huge payloads)
            if (mb_strlen($val) > 60) {
                continue;
            }

            $clean[$k] = $val;
        }

        Setting::set('theme_light_vars', json_encode($clean, JSON_UNESCAPED_SLASHES));

        return back()->with('success', 'Theme settings updated.');
    }

    public function resetLight(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }

        Setting::set('theme_light_vars', json_encode([], JSON_UNESCAPED_SLASHES));

        return back()->with('success', 'Light theme reset to defaults.');
    }

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
}
