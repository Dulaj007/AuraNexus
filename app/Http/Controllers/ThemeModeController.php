<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemeModeController extends Controller
{
    public function update(Request $request)
    {
        $mode = (string) $request->input('mode', 'dark');

        if (!in_array($mode, ['dark', 'light'], true)) {
            $mode = 'dark';
        }

        // 1 year cookie
        return back()->withCookie(cookie('theme_mode', $mode, 60 * 24 * 365));
    }
}
