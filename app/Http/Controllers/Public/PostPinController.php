<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\Post;
use App\Models\PinnedPost;
use Illuminate\Http\Request;

class PostPinController extends Controller
{
    public function pin(Request $request, Forum $forum, Post $post)
    {
        // safety: ensure post belongs to forum
        if ((int) $post->forum_id !== (int) $forum->id) {
            abort(404);
        }

        PinnedPost::query()->updateOrCreate(
            ['forum_id' => $forum->id, 'post_id' => $post->id],
            ['pinned_by' => $request->user()?->id, 'pinned_at' => now()]
        );

        return back()->with('success', 'Post pinned.');
    }

    public function unpin(Request $request, Forum $forum, Post $post)
    {
        if ((int) $post->forum_id !== (int) $forum->id) {
            abort(404);
        }

        PinnedPost::query()
            ->where('forum_id', $forum->id)
            ->where('post_id', $post->id)
            ->delete();

        return back()->with('success', 'Post unpinned.');
    }
}
