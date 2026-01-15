<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function edit(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) abort(403);

        // 3 priority accents (admin-controlled, global)
        $primary   = Setting::get('theme_primary',   '#8B5CF6'); // main theme
        $secondary = Setting::get('theme_secondary', '#22D3EE'); // links/nav
        $tertiary  = Setting::get('theme_tertiary',  '#FBBF24'); // highlights/info

        // Mode is per-user now (cookie), not stored in DB
        $mode = $request->cookie('theme_mode', 'dark');
        $mode = in_array($mode, ['dark', 'light'], true) ? $mode : 'dark';

        return view('admin.theme.index', [
            'mode' => $mode, // can be used only for preview UI
            'primary' => $primary,
            'secondary' => $secondary,
            'tertiary' => $tertiary,
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) abort(403);

        $data = $request->validate([
            'theme_primary'   => ['required', 'string', 'max:16'],
            'theme_secondary' => ['required', 'string', 'max:16'],
            'theme_tertiary'  => ['required', 'string', 'max:16'],
        ]);

        $primary   = $this->normalizeHex($data['theme_primary'])   ?? '#8B5CF6';
        $secondary = $this->normalizeHex($data['theme_secondary']) ?? '#22D3EE';
        $tertiary  = $this->normalizeHex($data['theme_tertiary'])  ?? '#FBBF24';

        Setting::set('theme_primary', $primary);
        Setting::set('theme_secondary', $secondary);
        Setting::set('theme_tertiary', $tertiary);

        // If you cache settings/theme anywhere, clear it here.
        // cache()->forget('theme.colors'); // example, only if you implemented caching

        return back()->with('success', 'Theme colors updated.');
    }

    public function reset(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->hasRole('admin')) abort(403);

        Setting::set('theme_primary', '#8B5CF6');
        Setting::set('theme_secondary', '#22D3EE');
        Setting::set('theme_tertiary', '#FBBF24');

        // If you cache settings/theme anywhere, clear it here.
        // cache()->forget('theme.colors');

        return back()->with('success', 'Theme colors reset to defaults.');
    }

    private function normalizeHex(string $val): ?string
    {
        $v = trim($val);

        // allow "#abc" or "#aabbcc" or without "#"
        if ($v !== '' && $v[0] !== '#') $v = '#'.$v;

        if (!preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $v)) {
            return null;
        }

        // expand #abc -> #aabbcc
        if (strlen($v) === 4) {
            $v = '#'.$v[1].$v[1].$v[2].$v[2].$v[3].$v[3];
        }

        return strtoupper($v);
    }
}
