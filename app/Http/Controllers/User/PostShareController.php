<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\LogsUserActivity;
use Illuminate\Http\Request;

class PostShareController extends Controller
{
    use LogsUserActivity;

    public function store(Request $request, Post $post)
    {
        $user = $request->user();

        // 🔐 Must be logged in to track shares
        if (!$user) {
            abort(403);
        }

        // Optional: where user shared to (copy, whatsapp, facebook, etc.)
        $data = $request->validate([
            'channel' => ['nullable', 'string', 'max:50'],
        ]);

        $channel = $data['channel'] ?? 'unknown';

        // ✅ log share activity
        $this->logActivity(
            $request,
            $user->id,
            'post_shared',
            Post::class,
            $post->id,
            [
                'channel' => $channel,
                'path'    => $request->path(),
            ]
        );

        return response()->json([
            'ok' => true,
            'message' => 'Post share recorded.',
        ]);
    }
}
