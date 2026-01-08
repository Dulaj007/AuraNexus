<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Not logged in
        if (!$user) {
            return redirect('/')->with('error', 'Please log in first.');
        }

        /**
         * Allow if:
         *  - admin role (highest)
         *  - OR has permission to login admin panel
         *
         * This supports your plan:
         * - admin always allowed
         * - super_member / moderator can be allowed if you assign login_admin_panel
         */
        $allowed = $user->hasRole('admin') || $user->hasPermission('login_admin_panel');

        if (!$allowed) {
            return redirect('/')->with('error', 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
