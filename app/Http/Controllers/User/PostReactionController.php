<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostReactionController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        DB::transaction(function () use ($request, $user, $post) {

            $existing = PostReaction::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->where('type', 'like')
                ->first();

            $didReact = false;

            if ($existing) {
                // Unlike
                $existing->delete();
                $didReact = false;

                // ✅ Decrease post owner's reputation (if not self-like)
                if ((int) $post->user_id !== (int) $user->id) {
                    DB::table('users')
                        ->where('id', $post->user_id)
                        ->where('reputation_points', '>', 0)
                        ->decrement('reputation_points', 1);
                }
            } else {
                // Like
                PostReaction::create([
                    'post_id' => $post->id,
                    'user_id' => $user->id,
                    'type'    => 'like',
                ]);
                $didReact = true;

                // ✅ Increase post owner's reputation (if not self-like)
                if ((int) $post->user_id !== (int) $user->id) {
                    DB::table('users')
                        ->where('id', $post->user_id)
                        ->increment('reputation_points', 1);
                }
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
        });

        return back();
    }

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
