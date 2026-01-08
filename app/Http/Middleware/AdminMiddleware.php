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

        // Not logged in → redirect home
        if (!$user) {
            return redirect('/');
        }

        // Check if user has admin role
        $isAdmin = $user->roles()->where('name', 'admin')->exists();

        if (!$isAdmin) {
            return redirect('/')->with('error', 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
