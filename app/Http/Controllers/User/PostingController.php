<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyStat;
use App\Models\Forum;
use App\Models\PageView;
use App\Models\ParagraphTemplate;
use App\Models\Post;
use App\Models\PostJsonld;
use App\Models\PostParagraph;
use App\Models\Tag;
use App\Models\TotalStat;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostingController extends Controller
{
    public function create(Request $request)
    {
        // Log a view for /posting page
        $this->logPageView($request);

        // Increment daily stats views (guest vs registered) + total stats views
        $this->incrementDailyViews($request);

        $forums = Forum::query()->orderBy('name')->get(['id', 'name']);

        // Optional: used if you want tag suggestions UI (not required for creation)
        $allTags = Tag::query()->orderBy('name')->get(['id', 'name']);

        // Paragraph templates grouped by category
        $templates = ParagraphTemplate::query()
            ->orderBy('category')
            ->orderBy('id')
            ->get(['id', 'category', 'content'])
            ->groupBy('category');

        return view('posting.create', [
            'forums'    => $forums,
            'allTags'   => $allTags,
            'templates' => $templates,
        ]);
    }

public function store(Request $request)
{
    $user = $request->user();

    // ✅ hard permission check (server-side)
    if (!$user || !$user->hasPermission('create_post')) {
        return redirect('/')->with('error', 'You are not allowed to create posts.');
    }

    // ✅ status rule
    $status = ($user->hasPermission('approve_post'))
        ? 'published'
        : 'pending';

    $validated = $request->validate([
        'forum_id' => ['required', 'integer', 'exists:forums,id'],
        'title'    => ['required', 'string', 'min:5', 'max:150'],
        'content'  => ['required', 'string', 'min:20'],

        // user-created tags (names)
        'tag_names' => ['nullable', 'array', 'max:15'],
        'tag_names.*' => ['string', 'min:1', 'max:30'],

        // highlight tag by name (must be within tag_names)
        'highlight_tag_name' => ['nullable', 'string', 'min:1', 'max:30'],

        'paragraph_template_id' => ['nullable', 'integer', 'exists:paragraph_templates,id'],
        'paragraph_content'     => ['nullable', 'string', 'max:5000'],
    ]);

    // ✅ if highlight set, ensure it exists in tag_names
    if (!empty($validated['highlight_tag_name'])) {
        $tags = array_map(fn($t) => strtolower(trim($t)), $validated['tag_names'] ?? []);
        $highlight = strtolower(trim($validated['highlight_tag_name']));

        if (!in_array($highlight, $tags, true)) {
            return back()
                ->withErrors(['highlight_tag_name' => 'Highlight tag must be one of your selected tags.'])
                ->withInput();
        }
    }

    $post = DB::transaction(function () use ($validated, $user, $request, $status) {

        $title = trim($validated['title']);
        $slug  = $this->uniqueSlug($title);

        $post = Post::create([
            'forum_id' => $validated['forum_id'],
            'user_id'  => $user->id,
            'title'    => $title,
            'slug'     => $slug,
            'content'  => $validated['content'],
            'views'    => 0,
            'status'   => $status, // ✅ published or pending
            'replies_count' => 0,
            'reputation_points' => 0,
        ]);

        // ✅ tags: create on-the-fly from names
        $tagIds = [];
        foreach (($validated['tag_names'] ?? []) as $name) {
            $cleanName = trim($name);
            if ($cleanName === '') continue;

            $tag = Tag::firstOrCreate(
                ['slug' => Str::slug($cleanName)],
                ['name' => $cleanName]
            );

            $tagIds[] = $tag->id;
        }
        $tagIds = array_values(array_unique($tagIds));

        if (!empty($tagIds)) {
            $post->tags()->sync($tagIds);
        }

        // ✅ highlight tag by NAME → convert to ID and save
        if (!empty($validated['highlight_tag_name'])) {
            $highlightSlug = Str::slug($validated['highlight_tag_name']);
            $highlightTagId = Tag::where('slug', $highlightSlug)->value('id');

            if ($highlightTagId && in_array($highlightTagId, $tagIds, true)) {
                $post->update(['highlight_tag_id' => $highlightTagId]);
            }
        }

        // ✅ paragraph save
        if (!empty($validated['paragraph_template_id']) && !empty($validated['paragraph_content'])) {
            PostParagraph::create([
                'post_id'      => $post->id,
                'paragraph_id' => $validated['paragraph_template_id'],
                'content'      => $validated['paragraph_content'],
                'order'        => 1,
            ]);
        }

        // ✅ stats + activity only if actually created
        $this->incrementPostStats();
        $this->logUserActivity($request, $user->id, $post->id);

        return $post;
    });

    // ✅ redirect message based on status
// ✅ Always redirect to the created post page
if ($post->status === 'pending') {
    return redirect()
        ->route('post.show', $post->slug)   // or ->route('post.show', $post)
        ->with('success', 'Post submitted for approval (pending).');
}

return redirect()
    ->route('post.show', $post->slug)       // or ->route('post.show', $post)
    ->with('success', 'Post published successfully!');

}


    /* ---------------- helpers ---------------- */

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base ?: Str::random(10);

        $i = 2;
        while (Post::where('slug', $slug)->exists()) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function extractImageUrls(string $content): array
    {
        $pattern = '/https?:\/\/[^\s"\']+\.(?:png|jpe?g|webp|gif)(?:\?[^\s"\']*)?/i';
        preg_match_all($pattern, $content, $matches);

        $urls = array_values(array_unique($matches[0] ?? []));
        return array_slice($urls, 0, 10);
    }

    private function buildJsonLd(array $data): array
    {
        /** @var \App\Models\Post $post */
        $post = $data['post'];

        $authorName = optional($post->user)->name ?? 'Member';

        return [
            '@context' => 'https://schema.org',
            '@type' => 'DiscussionForumPosting',
            'headline' => $post->title,
            'datePublished' => optional($post->created_at)->toIso8601String(),
            'dateModified' => optional($post->updated_at)->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name' => $authorName,
            ],
            'url' => $data['url'] ?? null,
            'image' => $data['images'] ?? [],
            'articleBody' => Str::limit(strip_tags($post->content), 5000),
        ];
    }

    private function logPageView(Request $request): void
    {
        PageView::create([
            'viewable_type' => 'page',
            'viewable_id'   => 0,
            'user_id'       => optional($request->user())->id,
            'is_guest'      => $request->user() ? 0 : 1,
            'ip_address'    => $request->ip(),
            'user_agent'    => substr((string) $request->userAgent(), 0, 1000),
            'referrer'      => $request->headers->get('referer'),
            'path'          => $request->path(),
            'url'           => $request->fullUrl(),
            'is_bot'        => 0,
        ]);
    }

    private function incrementDailyViews(Request $request): void
    {
        $today = now()->toDateString();

        DailyStat::query()->updateOrCreate(
            ['date' => $today],
            ['date' => $today]
        );

        DailyStat::where('date', $today)->increment('total_views');

        if ($request->user()) {
            DailyStat::where('date', $today)->increment('registered_views');
        } else {
            DailyStat::where('date', $today)->increment('guest_views');
        }

        TotalStat::query()->updateOrCreate(['id' => 1], ['id' => 1]);
        TotalStat::where('id', 1)->increment('total_website_views');
    }

    private function incrementPostStats(): void
    {
        $today = now()->toDateString();

        DailyStat::query()->updateOrCreate(['date' => $today], ['date' => $today]);
        DailyStat::where('date', $today)->increment('posts_created');

        TotalStat::query()->updateOrCreate(['id' => 1], ['id' => 1]);
        TotalStat::where('id', 1)->increment('posts_count');
    }

    private function logUserActivity(Request $request, int $userId, int $postId): void
    {
        UserActivity::create([
            'user_id' => $userId,
            'event' => 'post_created',
            'subject_type' => Post::class,
            'subject_id' => $postId,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'meta' => json_encode([
                'path' => $request->path(),
            ], JSON_UNESCAPED_SLASHES),
        ]);
    }
}
