<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ThumbnailService
{
    private const MAX_SOURCE_BYTES = 15 * 1024 * 1024; // 15MB
    private const FETCH_TIMEOUT = 6; // seconds

    /**
     * Resolve (generating + caching on first request if needed) the local
     * disk path of a resized/compressed copy of $url. Returns null if the
     * source can't be safely fetched or resized, so the caller can fall
     * back to the original URL — this never throws or blocks the page
     * render; it's meant to be called from a dedicated per-image route so
     * slow/failed sources don't hold up the rest of the page.
     */
    public function resolveCachedPath(?string $url, int $width, int $quality = 75): ?string
    {
        $url = trim((string) $url);

        if ($url === '' || !$this->isSafeFetchTarget($url)) {
            return null;
        }

        $key = 'thumbs/'.hash('sha256', "{$url}|{$width}|{$quality}").'.webp';

        if (Storage::disk('public')->exists($key)) {
            return Storage::disk('public')->path($key);
        }

        try {
            $resized = $this->fetchAndResize($url, $width, $quality);

            if ($resized === null) {
                return null;
            }

            Storage::disk('public')->put($key, $resized);

            return Storage::disk('public')->path($key);
        } catch (\Throwable $e) {
            Log::warning('Thumbnail generation failed', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    private function fetchAndResize(string $url, int $width, int $quality): ?string
    {
        $response = Http::timeout(self::FETCH_TIMEOUT)
            ->withOptions(['allow_redirects' => false])
            ->withHeaders(['User-Agent' => config('app.name').'-ThumbnailBot/1.0'])
            ->get($url);

        if (!$response->successful()) {
            return null;
        }

        $contentType = (string) $response->header('Content-Type');
        if (!str_starts_with($contentType, 'image/')) {
            return null;
        }

        $body = $response->body();
        if (strlen($body) === 0 || strlen($body) > self::MAX_SOURCE_BYTES) {
            return null;
        }

        $source = @imagecreatefromstring($body);
        if ($source === false) {
            return null;
        }

        $srcWidth = imagesx($source);
        $srcHeight = imagesy($source);

        if ($srcWidth < 1 || $srcHeight < 1) {
            imagedestroy($source);
            return null;
        }

        // never upscale — only shrink down to the target width
        $targetWidth = min($width, $srcWidth);
        $targetHeight = (int) round($srcHeight * ($targetWidth / $srcWidth));

        $resized = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);

        imagecopyresampled(
            $resized, $source,
            0, 0, 0, 0,
            $targetWidth, $targetHeight,
            $srcWidth, $srcHeight
        );

        imagedestroy($source);

        ob_start();
        imagewebp($resized, null, $quality);
        $webp = ob_get_clean();

        imagedestroy($resized);

        return ($webp !== false && $webp !== '') ? $webp : null;
    }

    /**
     * Block anything that isn't a plain http(s) URL resolving to a public
     * IP address, so a pasted thumbnail URL can't be used to make this
     * server fetch internal/private network resources (SSRF).
     */
    private function isSafeFetchTarget(string $url): bool
    {
        $parts = parse_url($url);

        if (!$parts || empty($parts['scheme']) || empty($parts['host'])) {
            return false;
        }

        if (!in_array(strtolower($parts['scheme']), ['http', 'https'], true)) {
            return false;
        }

        $host = $parts['host'];

        $ip = filter_var($host, FILTER_VALIDATE_IP) ? $host : gethostbyname($host);

        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            // gethostbyname() returns the original hostname unchanged on failure
            return false;
        }

        return (bool) filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }
}
