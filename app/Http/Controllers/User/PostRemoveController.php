<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\RemovedPost;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class PostRemoveController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $user = $request->user();

        if (!$user || !$user->hasPermission('delete_post')) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        // If already removed, just go to removed page
        if ($post->status === 'removed') {
            return redirect()->route('post.show', $post);
        }

        RemovedPost::create([
            'post_id'    => $post->id,
            'removed_by' => $user->id,
            'reason'     => $data['reason'],
        ]);

        // Keep the post row so route model binding still works
        $post->update([
            'status' => 'removed',
        ]);

        // ✅ activity log
        $this->logActivity(
            $request,
            (int) $user->id,
            'post_removed',
            Post::class,
            (int) $post->id,
            ['reason' => $data['reason']]
        );

        return redirect()
            ->route('post.show', $post)
            ->with('success', 'Post removed.');
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
