<?php

use Illuminate\Support\Facades\URL;

if (!function_exists('resized_image_url')) {
    /**
     * Build a signed URL to a resized/compressed copy of an image, served
     * (and cached on first hit) by ThumbnailController. Deliberately does
     * NOT fetch/resize here — that happens per-image, on request, so a
     * slow or unreachable source never blocks the page that links to it.
     * Local/relative URLs are returned as-is.
     */
    function resized_image_url(?string $url, int $width, int $quality = 75): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || !preg_match('#^https?://#i', $url)) {
            return $url ?: null;
        }

        return URL::signedRoute('thumb.show', [
            'u' => $url,
            'w' => $width,
            'q' => $quality,
        ]);
    }
}
