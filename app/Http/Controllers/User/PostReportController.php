<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReport;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class PostReportController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $report = PostReport::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'reason'  => trim($data['reason']),
        ]);

        // ✅ activity log
        $this->logActivity(
            $request,
            (int) $user->id,
            'post_reported',
            Post::class,
            (int) $post->id,
            [
                'report_id' => (int) $report->id,
                'reason'    => trim($data['reason']),
            ]
        );

        return back()->with('success', 'Report submitted. Thank you.');
    }

    /* ============================================================
     |  ACTIVITY LOGGER (UserActivity)
     ============================================================ */
    private function logActivity(
        Request $request,
        int $userId,
        string $event,
        string $subjectType,
        int $subjectId,
        array $meta = []
    ): void {
        UserActivity::create([
            'user_id'      => $userId,
            'event'        => $event,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 1000),
            'meta'         => empty($meta) ? null : json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
