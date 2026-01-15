<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\RemovedComment;
use App\Models\UserActivity;
use Illuminate\Http\Request;

class CommentRemoveController extends Controller
{
    public function store(Request $request, Comment $comment)
    {
        $user = $request->user();

        // ✅ must be logged + have delete permission
        if (!$user || !$user->hasPermission('delete_post')) {
            abort(403);
        }

        $data = $request->validate([
            'reason' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        // ✅ if already removed, do nothing
        if ($comment->status === 'removed') {
            return back();
        }

        // ✅ log removal record
        RemovedComment::create([
            'comment_id' => $comment->id,
            'removed_by' => $user->id,
            'reason'     => $data['reason'],
        ]);

        // ✅ mark comment removed
        $comment->update([
            'status' => 'removed',
        ]);

        // ✅ activity log
        UserActivity::create([
            'user_id'      => $user->id,
            'event'        => 'comment_removed',
            'subject_type' => Comment::class,
            'subject_id'   => $comment->id,
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 1000),
            'meta'         => json_encode([
                'post_id'   => $comment->post_id ?? null,
                'reason'    => $data['reason'],
                'path'      => $request->path(),
            ], JSON_UNESCAPED_SLASHES),
        ]);

        return back()->with('success', 'Comment removed.');
    }
}
