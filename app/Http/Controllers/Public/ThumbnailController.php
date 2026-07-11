<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\ThumbnailService;
use Illuminate\Http\Request;

class ThumbnailController extends Controller
{
    /**
     * Serve a resized/compressed copy of an external image URL, generating
     * and caching it on first request. This is deliberately its own route
     * (rather than being resolved during the page render) so a slow or
     * unreachable source image only delays this one <img> tag — loaded in
     * parallel by the browser — instead of blocking the whole page.
     *
     * The `u`/`w`/`q` params are validated via Laravel's signed-URL
     * middleware, so this can't be used as an open fetch-anything proxy.
     */
    public function show(Request $request, ThumbnailService $service)
    {
        if (!$request->hasValidSignature()) {
            abort(403);
        }

        $url = (string) $request->query('u', '');
        $width = max(1, min(2000, (int) $request->query('w', 400)));
        $quality = max(1, min(100, (int) $request->query('q', 75)));

        $path = $service->resolveCachedPath($url, $width, $quality);

        if ($path === null) {
            return $url !== '' ? redirect()->away($url) : abort(404);
        }

        return response()->file($path, [
            'Content-Type' => 'image/webp',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
