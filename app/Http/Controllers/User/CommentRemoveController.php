<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\RemovedComment;
use Illuminate\Http\Request;

class CommentRemoveController extends Controller
{
    public function store(Request $request, Comment $comment)
    {
        $user = $request->user();

        if (!$user || !$user->hasPermission('delete_post')) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        if ($comment->status === 'removed') {
            return back();
        }

        RemovedComment::create([
            'comment_id' => $comment->id,
            'removed_by' => $user->id,
            'reason'     => $data['reason'],
        ]);

        // Keep comment but mark removed (so history exists)
        $comment->update([
            'status' => 'removed',
        ]);

        return back()->with('success', 'Comment removed.');
    }
}
