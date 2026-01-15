<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class PostSaveController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $user = $request->user();
        if (!$user) {
            abort(403);
        }

        $alreadySaved = $user->savedPosts()
            ->where('post_id', $post->id)
            ->exists();

        if ($alreadySaved) {
            // safer for belongsToMany: detach by id (or by $post->id)
            $user->savedPosts()->detach($post->id);

            $this->logActivity(
                $request,
                (int) $user->id,
                'post_unsaved',
                Post::class,
                (int) $post->id,
                ['path' => $request->path()]
            );

            return back()->with('success', 'Removed from saved posts.');
        }

        // avoid duplicate pivot rows
        $user->savedPosts()->syncWithoutDetaching([$post->id]);

        $this->logActivity(
            $request,
            (int) $user->id,
            'post_saved',
            Post::class,
            (int) $post->id,
            ['path' => $request->path()]
        );

        return back()->with('success', 'Post saved!');
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
