<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Support\SiteSettings;

class EnsureRegistrationsOpen
{
    public function handle(Request $request, Closure $next)
    {
        // Use your cached settings helper (same one you use in views)
        $settings = class_exists(SiteSettings::class) ? SiteSettings::public() : [];
        $open = (int)($settings['registrations_open'] ?? 1) === 1;

        if ($open) {
            return $next($request);
        }

        // If closed: show a friendly page (for GET) or redirect back (for POST)
        if ($request->isMethod('get')) {
            return response()->view('auth.registration-closed', [
                'siteName' => $settings['site_name'] ?? config('app.name', 'Site'),
            ], 403);
        }

        return redirect()
            ->route('login')
            ->with('error', ($settings['site_name'] ?? config('app.name')) . ' is currently not allowing new registrations.');
    }
}
