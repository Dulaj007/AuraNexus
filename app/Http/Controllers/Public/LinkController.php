<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PostLink;
use App\Models\UnlockSession;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

    /**
     * Setting `link_unlock_ad_urls` is stored as JSON string OR null.
     * Returns array of http(s) urls.
     */
    private function adUrls(): array
    {
        $raw = $this->setting('link_unlock_ad_urls', null);
        if (!$raw) return [];

        $arr = json_decode((string) $raw, true);
        if (!is_array($arr)) return [];

        $clean = [];
        foreach ($arr as $u) {
            $u = trim((string) $u);
            if ($u === '') continue;
            $scheme = strtolower((string) parse_url($u, PHP_URL_SCHEME));
            if (!in_array($scheme, ['http', 'https'], true)) continue;
            $clean[] = $u;
        }

        // unique + reindex
        return array_values(array_unique($clean));
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
            'adUrls'          => $this->adUrls(), // ✅ pass array
        ]);
    }

    public function start(Request $request, string $code)
    {
        if (!$this->isUnlockEnabled()) {
            return response()->json(['error' => 'Unlock disabled'], 409);
        }

        $link = PostLink::query()
            ->where('code', $code)
            ->where('is_enabled', true)
            ->firstOrFail();

        $token = Str::random(48);

        $session = UnlockSession::create([
            'post_link_id'      => $link->id,
            'token'             => $token,
            'required_seconds'  => $this->requiredSeconds(),
            'status'            => 'started',
            'expires_at'        => now()->addMinutes(15),

            'user_id'           => auth()->id(),
            'ip_hash'           => hash('sha256', (string) $request->ip()),
            'ua_hash'           => hash('sha256', (string) $request->userAgent()),
            'last_ping_at'      => now(), // user is currently on download page
        ]);

        return response()->json([
            'token'            => $session->token,
            'required_seconds' => (int) $session->required_seconds,
        ]);
    }

    public function ping(Request $request, string $token)
    {
        $session = UnlockSession::query()->where('token', $token)->firstOrFail();

        if ($session->expires_at && now()->greaterThan($session->expires_at)) {
            if ($session->status !== 'expired') {
                $session->status = 'expired';
                $session->save();
            }
            return response()->json(['status' => 'expired'], 410);
        }

        if ($session->status === 'expired') {
            return response()->json(['status' => 'expired'], 410);
        }

        $session->last_ping_at = now();
        $session->save();

        return response()->json(['ok' => true]);
    }

    public function status(Request $request, string $token)
    {
        $session = UnlockSession::query()->where('token', $token)->firstOrFail();

        // ✅ counts "away time" when no pings
        $session->syncAwayTimer(2);

        $required = (int) $session->required_seconds;

        $totalAway = (int) $session->away_seconds_accumulated;
        if ($session->away_started_at) {
            $totalAway += max(0, $session->away_started_at->diffInSeconds(now()));
        }

        $remaining = max(0, $required - $totalAway);

        return response()->json([
            'status'    => $session->status,
            'remaining' => $remaining,
            'required'  => $required,
            'unlocked'  => $session->status === 'unlocked',
        ]);
    }

    public function go(Request $request, string $token)
    {
        $session = UnlockSession::query()->where('token', $token)->firstOrFail();

        $session->syncAwayTimer(2);

        if ($session->status !== 'unlocked') {
            abort(403);
        }

        $link = $session->postLink;

        $url = $this->safeExternalUrl($link->original_url);
        if (!$url) abort(404);

        return redirect()->away($url);
    }
}
