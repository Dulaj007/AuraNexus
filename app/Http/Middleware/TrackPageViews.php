<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PageView;

class TrackPageViews
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Only for GET pages (avoid spamming from POST/PUT)
        if ($request->isMethod('get')) {
            $userId = auth()->id();

            PageView::create([
                'user_id'    => $userId,
                'path'       => '/' . ltrim($request->path(), '/'),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 255),
            ]);
        }

        return $response;
    }
}
