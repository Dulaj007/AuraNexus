<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;

class PostCommentController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $data = $request->validate([
            'content' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        // Anti-link spam (simple)
        if (preg_match('/https?:\/\/|www\./i', $data['content'])) {
            return back()->withErrors(['content' => 'Links are not allowed in comments.']);
        }

        $user = $request->user();

        $canCreate = $user->hasPermission('create_post');
        $canApprove = $user->hasPermission('approve_post');
$commentsQuery = Comment::with('user:id,username')
    ->where('post_id', $post->id)
    ->latest();

if ($canApprove) {
    $commentsQuery->whereIn('status', ['published', 'pending']);
} else {
    $commentsQuery->where('status', 'published');
}

$comments = $commentsQuery->get();
        if (!$canCreate && !$canApprove) {
            return redirect('/')->with('error', 'You are not allowed to comment.');
        }

     $status = $canApprove ? 'published' : 'pending';


        Comment::create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'parent_id' => null,
            'content' => $data['content'],
            'status' => $status,
        ]);

        return back()->with('success', $status === 'published'
            ? 'Comment posted.'
            : 'Comment submitted for approval.');
    }

public function approve(Request $request, Comment $comment)
{
    if (!$request->user()?->hasPermission('approve_post')) {
        abort(403);
    }

    if ($comment->status === 'published') {
        return back();
    }

    $comment->update(['status' => 'published']);

    return back()->with('success', 'Comment approved.');
}

    
}
