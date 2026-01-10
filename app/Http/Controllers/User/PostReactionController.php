<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReaction;
use Illuminate\Http\Request;

class PostReactionController extends Controller
{
    public function toggle(Request $request, Post $post)
    {
        $userId = $request->user()->id;

        $existing = PostReaction::where('post_id', $post->id)
            ->where('user_id', $userId)
            ->where('type', 'like')
            ->first();

        if ($existing) {
            $existing->delete();
        } else {
            PostReaction::create([
                'post_id' => $post->id,
                'user_id' => $userId,
                'type' => 'like',
            ]);
        }

        return back();
    }
}
