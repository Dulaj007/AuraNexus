<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserNotRestricted
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) return $next($request);

        // Allow the restricted page + logout always
        if ($request->routeIs('restricted') || $request->routeIs('logout')) {
            return $next($request);
        }

        // If suspended and time is over => auto restore
        if ($user->status === 'suspended') {
            if ($user->suspended_until && now()->greaterThanOrEqualTo($user->suspended_until)) {
               $user->clearRestriction();
                return redirect()->route('home');

            }

            return redirect()->route('restricted');
        }

        // Banned => always blocked
        if ($user->status === 'banned') {
            return redirect()->route('restricted');
        }

        return $next($request);
    }
}
