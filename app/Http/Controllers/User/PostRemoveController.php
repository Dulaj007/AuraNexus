<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\RemovedPost;
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

        return redirect()->route('post.show', $post)
            ->with('success', 'Post removed.');
    }
}
