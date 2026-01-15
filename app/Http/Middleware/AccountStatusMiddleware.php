<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AccountStatusMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Only matters when logged in
        if (!$user) {
            return $next($request);
        }

        // ✅ auto-clear if suspension ended
        $user->syncRestrictionState();

        $isRestrictedPage = $request->routeIs('account.restricted');

        // If user IS restricted → force restricted page
        if ($user->isRestricted()) {
            if ($isRestrictedPage) {
                return $next($request);
            }
            return redirect()->route('account.restricted');
        }

        // If user is NOT restricted but tries to open /restricted → send home
        if ($isRestrictedPage) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
