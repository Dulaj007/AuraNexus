<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReport;
use Illuminate\Http\Request;

class PostReportController extends Controller
{
    public function store(Request $request, Post $post)
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        PostReport::create([
            'post_id' => $post->id,
            'user_id' => $request->user()->id,
            'reason'  => trim($data['reason']),
        ]);

        return back()->with('success', 'Report submitted. Thank you.');
    }
}
