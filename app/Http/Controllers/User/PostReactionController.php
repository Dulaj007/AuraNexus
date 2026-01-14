<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class PostReactionController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $user = $request->user();
        if (!$user) {
            abort(403); // must be logged in
        }

        $existing = PostReaction::where('post_id', $post->id)
            ->where('user_id', $user->id)
            ->where('type', 'like')
            ->first();

        $didReact = false;

        if ($existing) {
            $existing->delete();
            $didReact = false;
        } else {
            PostReaction::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
                'type'    => 'like',
            ]);
            $didReact = true;
        }

        // ✅ activity log
        $event = $didReact ? 'post_liked' : 'post_unliked';

        $this->logActivity(
            $request,
            (int) $user->id,
            $event,
            Post::class,
            (int) $post->id,
            ['type' => 'like']
        );

        return back();
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
