<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use App\Models\Post;

class TagController extends Controller
{
    public function show(Tag $tag)
    {
        // show only published posts
        $posts = Post::where('status', 'published')
            ->whereHas('tags', fn($q) => $q->where('tags.id', $tag->id))
            ->latest()
            ->paginate(20);

        return view('tags.show', compact('tag', 'posts'));
    }
}
