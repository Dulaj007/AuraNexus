<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PostLink;
use App\Models\Setting;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    private function setting(string $key, $default = null)
    {
        $val = Setting::query()->where('key', $key)->value('value');
        return $val === null ? $default : $val;
    }

    private function settingBool(string $key, bool $default = false): bool
    {
        $val = $this->setting($key, null);
        if ($val === null) return $default;
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    private function settingInt(string $key, int $default = 0): int
    {
        $val = $this->setting($key, null);
        if ($val === null) return $default;
        return (int) $val;
    }

    private function isUnlockEnabled(): bool
    {
        return $this->settingBool('link_unlock_enabled', true);
    }

    private function requiredSeconds(): int
    {
        $sec = $this->settingInt('link_unlock_seconds', 5);
        return min(max($sec, 0), 60);
    }

    private function safeExternalUrl(string $url): ?string
    {
        $url = trim($url);
        $scheme = parse_url($url, PHP_URL_SCHEME);

        if (!in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }

    public function show(Request $request, string $code)
    {
        $link = PostLink::query()
            ->where('code', $code)
            ->where('is_enabled', true)
            ->firstOrFail();

        $url = $this->safeExternalUrl($link->original_url);
        if (!$url) abort(404);

        if (!$this->isUnlockEnabled()) {
            return redirect()->away($url);
        }

        $host = parse_url($url, PHP_URL_HOST) ?: 'link';

        return view('link.show', [
            'link'            => $link,
            'host'            => $host,
            'requiredSeconds' => $this->requiredSeconds(),
        ]);
    }

    // Simple direct redirect (no token needed)
public function go(Request $request, string $code)
{
    $link = PostLink::query()
        ->where('code', $code)
        ->where('is_enabled', true)
        ->firstOrFail();

    $url = $this->safeExternalUrl($link->original_url);
    if (!$url) abort(404);

    return redirect()->away($url);
}
}