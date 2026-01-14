<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Rules:
     * - User MUST be authenticated
     * - Allowed if:
     *      - Has role "admin"
     *      - OR has permission "login_admin_panel"
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Not logged in → go to login
        if (!$user) {
            return redirect()
                ->route('login')
                ->with('error', 'Please log in to access the admin panel.');
        }

        // ✅ Allow admin OR explicit permission
        if (
            $user->hasRole('admin') ||
            $user->hasPermission('login_admin_panel')
        ) {
            return $next($request);
        }

        // Logged in but not authorized → go home
        return redirect()
            ->route('home')
            ->with('error', 'You do not have permission to access the admin panel.');
    }
}
