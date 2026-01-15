<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $user = $request->user();

        // ✅ must be logged in to comment
        if (!$user) {
            return redirect()->route('login')->with('error', 'Please login to comment.');
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        // ✅ Anti-link spam (simple)
        if (preg_match('/https?:\/\/|www\./i', $data['content'])) {
            return back()->withErrors(['content' => 'Links are not allowed in comments.'])->withInput();
        }

        // ✅ permission rule you already use: create_post OR approve_post can comment
        $canCreate = $user->hasPermission('create_post');
        $canApprove = $user->hasPermission('approve_post');

        if (!$canCreate && !$canApprove) {
            return redirect('/')->with('error', 'You are not allowed to comment.');
        }

        // ✅ if approver => publish instantly, else pending
        $status = $canApprove ? 'published' : 'pending';

        // ✅ create comment (IMPORTANT: keep the created model in $comment)
        $comment = Comment::create([
            'post_id'    => $post->id,
            'user_id'    => $user->id,
            'parent_id'  => null,
            'content'    => $data['content'],
            'status'     => $status,
        ]);

        // ✅ activity log (fix: you were using $comment without defining it)
        UserActivity::create([
            'user_id'      => $user->id,
            'event'        => 'comment_created',
            'subject_type' => Comment::class,
            'subject_id'   => $comment->id,
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 1000),
            'meta'         => json_encode([
                'post_id' => $post->id,
                'status'  => $status,
                'path'    => $request->path(),
            ], JSON_UNESCAPED_SLASHES),
        ]);

        return back()->with(
            'success',
            $status === 'published'
                ? 'Comment posted.'
                : 'Comment submitted for approval.'
        );
    }

    public function approve(Request $request, Comment $comment)
    {
        $user = $request->user();

        if (!$user || !$user->hasPermission('approve_post')) {
            abort(403);
        }

        if ($comment->status === 'published') {
            return back();
        }

        $comment->update(['status' => 'published']);

        // ✅ activity log
        UserActivity::create([
            'user_id'      => $user->id,
            'event'        => 'comment_approved',
            'subject_type' => Comment::class,
            'subject_id'   => $comment->id,
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 1000),
            'meta'         => json_encode([
                'post_id' => $comment->post_id,
                'path'    => $request->path(),
            ], JSON_UNESCAPED_SLASHES),
        ]);

        return back()->with('success', 'Comment approved.');
    }
}
