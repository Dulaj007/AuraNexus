<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        // If not logged in, send to login (or home if you prefer)
        if (!$user) {
            return redirect()->route('login');
            // or: return redirect('/');
        }

        // If logged in but no permission -> go home
        if (!$user->hasPermission($permission)) {
            return redirect('/')->with('error', 'You do not have permission to access that page.');
        }

        return $next($request);
    }
}
