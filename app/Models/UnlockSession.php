<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class UnlockSession extends Model
{
    protected $fillable = [
        'post_link_id',
        'token',
        'required_seconds',
        'away_seconds_accumulated',
        'away_started_at',
        'last_ping_at',
        'status',
        'expires_at',
        'user_id',
        'ip_hash',
        'ua_hash',
    ];

    protected $casts = [
        'away_started_at' => 'datetime',
        'last_ping_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function postLink(): BelongsTo
    {
        return $this->belongsTo(PostLink::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && now()->greaterThan($this->expires_at);
    }

    /**
     * Compute and update away time based on last_ping_at.
     * Call this inside your status endpoint.
     */
    public function syncAwayTimer(int $pingGraceSeconds = 2): void
    {
        if ($this->status !== 'started') return;

        if ($this->isExpired()) {
            $this->status = 'expired';
            $this->save();
            return;
        }

        $now = now();

        $onDownloadPage = $this->last_ping_at && $this->last_ping_at->diffInSeconds($now) <= $pingGraceSeconds;

        if ($onDownloadPage) {
            // pause timer: if we were counting away-time, finalize it
            if ($this->away_started_at) {
                $this->away_seconds_accumulated += max(0, $this->away_started_at->diffInSeconds($now));
                $this->away_started_at = null;
            }
        } else {
            // user is away: start counting if not already
            if (!$this->away_started_at) {
                $this->away_started_at = $now;
            }
        }

        // unlock check (include ongoing away time)
        $totalAway = $this->away_seconds_accumulated;
        if ($this->away_started_at) {
            $totalAway += max(0, $this->away_started_at->diffInSeconds($now));
        }

        if ($totalAway >= $this->required_seconds) {
            $this->status = 'unlocked';
            $this->away_started_at = null; // optional: normalize
        }

        $this->save();
    }
}
