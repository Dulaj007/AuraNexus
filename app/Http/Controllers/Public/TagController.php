<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

class TagController extends Controller
{
    /**
     * /tag/{tag:slug}
     * /tag/{tag:slug}/{page}
     */
    public function show(Request $request, Tag $tag, ?int $page = 1)
    {
        $page = max(1, (int) $page);
        Paginator::currentPageResolver(fn () => $page);

        // Posts with this tag (published only)
        $posts = Post::query()
            ->where('status', Post::STATUS_PUBLISHED ?? 'published')
            ->whereHas('tags', fn ($q) => $q->where('tags.id', $tag->id))
            ->with(['tags:id,name,slug'])
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // track only on canonical visit
        $this->trackTagView($request, $tag);

        return view('tags.show', [
            'tag' => $tag,
            'posts' => $posts,
            'resultsCount' => (int) $posts->total(),
            'page' => $page,
        ]);
    }

    private function trackTagView(Request $request, Tag $tag): void
    {
        // If you only want to count unique per session, add a session key check here.
        DB::transaction(function () use ($request, $tag) {

            // increment tags.views column
            $tag->increment('views', 1);

            // Optional: if you use PageView morph table and want it too
            // (remove if you don't want/need this)
            if (class_exists(\App\Models\PageView::class)) {
                $tag->views()->create([
                    'ip_address' => (string) $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                    'user_id' => $request->user()?->id,
                ]);
            }

            // Optional: log user_activities like you did for search
            if (DB::getSchemaBuilder()->hasTable('user_activities')) {
                DB::table('user_activities')->insert([
                    'user_id' => $request->user()?->id,
                    'event' => 'tag_view',
                    'subject_type' => Tag::class,
                    'subject_id' => $tag->id,
                    'ip_address' => (string) $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                    'meta' => json_encode([
                        'tag' => $tag->slug,
                        'name' => $tag->name,
                    ], JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }
}
