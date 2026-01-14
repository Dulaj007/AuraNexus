<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\DailyStat;
use App\Models\Forum;
use App\Models\PageView;
use App\Models\ParagraphTemplate;
use App\Models\Post;
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
        $status = $user->hasPermission('approve_post') ? 'published' : 'pending';

        $validated = $request->validate([
            'forum_id' => ['required', 'integer', 'exists:forums,id'],
            'title'    => ['required', 'string', 'min:5', 'max:150'],
            'content'  => ['required', 'string', 'min:20'],

            // user-created tags (names)
            'tag_names'    => ['nullable', 'array', 'max:15'],
            'tag_names.*'  => ['string', 'min:1', 'max:30'],

            // highlight tag by name (must be within tag_names)
            'highlight_tag_name' => ['nullable', 'string', 'min:1', 'max:30'],

            'paragraph_template_id' => ['nullable', 'integer', 'exists:paragraph_templates,id'],
            'paragraph_content'     => ['nullable', 'string', 'max:5000'],
        ]);

        // ✅ normalize tag names once (lower/trim), remove empties, unique
        $rawTagNames = $validated['tag_names'] ?? [];
        $normTagNames = collect($rawTagNames)
            ->map(fn ($t) => trim((string) $t))
            ->filter(fn ($t) => $t !== '')
            ->map(fn ($t) => mb_strtolower($t))
            ->unique()
            ->values()
            ->all();

        // ✅ if highlight set, ensure it exists in tag_names
        $highlightName = null;
        if (!empty($validated['highlight_tag_name'])) {
            $highlightName = mb_strtolower(trim((string) $validated['highlight_tag_name']));
            if (!in_array($highlightName, $normTagNames, true)) {
                return back()
                    ->withErrors(['highlight_tag_name' => 'Highlight tag must be one of your selected tags.'])
                    ->withInput();
            }
        }

        $post = DB::transaction(function () use ($validated, $user, $request, $status, $normTagNames, $highlightName) {

            $title = trim($validated['title']);
            $slug  = $this->uniqueSlug($title);

            $post = Post::create([
                'forum_id' => (int) $validated['forum_id'],
                'user_id'  => (int) $user->id,
                'title'    => $title,
                'slug'     => $slug,
                'content'  => $validated['content'],
                'views'    => 0,
                'status'   => $status, // published or pending
                'replies_count'      => 0,
                'reputation_points'  => 0,
            ]);

            // ✅ tags: create on-the-fly from names
            $tagIds = [];
            $slugToId = [];

            foreach ($normTagNames as $nameLower) {
                // keep original-ish display casing: use first seen version from raw list if possible
                $display = $this->findOriginalTagDisplay($validated['tag_names'] ?? [], $nameLower) ?? $nameLower;

                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($nameLower)],
                    ['name' => $display]
                );

                $tagIds[] = $tag->id;
                $slugToId[$tag->slug] = $tag->id;
            }

            $tagIds = array_values(array_unique($tagIds));

            if (!empty($tagIds)) {
                $post->tags()->sync($tagIds);
            }

            // ✅ highlight tag by NAME -> convert to ID and save (must be within selected tags)
            if ($highlightName) {
                $highlightSlug = Str::slug($highlightName);
                $highlightTagId = $slugToId[$highlightSlug] ?? null;

                if ($highlightTagId) {
                    $post->update(['highlight_tag_id' => $highlightTagId]);
                }
            }

            // ✅ paragraph save (only if both exist)
            if (!empty($validated['paragraph_template_id']) && !empty($validated['paragraph_content'])) {
                PostParagraph::create([
                    'post_id'      => $post->id,
                    'paragraph_id' => (int) $validated['paragraph_template_id'],
                    'content'      => $validated['paragraph_content'],
                    'order'        => 1,
                ]);
            }

            // ✅ stats + activity only if actually created
            $this->incrementPostStats();

            $this->logActivity(
                $request,
                (int) $user->id,
                'post_created',
                Post::class,
                (int) $post->id,
                [
                    'status'   => $status,
                    'forum_id' => (int) $post->forum_id,
                    'path'     => $request->path(),
                ]
            );

            return $post;
        });

        // ✅ Always redirect to the created post page
        if ($post->status === 'pending') {
            return redirect()
                ->route('post.show', $post->slug)
                ->with('success', 'Post submitted for approval (pending).');
        }

        return redirect()
            ->route('post.show', $post->slug)
            ->with('success', 'Post published successfully!');
    }

    /* ---------------- helpers ---------------- */

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base ?: Str::random(10);

        $i = 2;
        while (Post::where('slug', $slug)->exists()) {
            $slug = ($base ?: $slug) . '-' . $i;
            $i++;
        }

        return $slug;
    }

    private function findOriginalTagDisplay(array $raw, string $needleLower): ?string
    {
        foreach ($raw as $t) {
            $clean = trim((string) $t);
            if ($clean === '') continue;

            if (mb_strtolower($clean) === $needleLower) {
                return $clean;
            }
        }
        return null;
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

    private function logActivity(
        Request $request,
        int $userId,
        string $event,
        string $subjectType,
        int $subjectId,
        array $meta = []
    ): void {
        UserActivity::create([
            'user_id'      => $userId,
            'event'        => $event,
            'subject_type' => $subjectType,
            'subject_id'   => $subjectId,
            'ip_address'   => $request->ip(),
            'user_agent'   => substr((string) $request->userAgent(), 0, 1000),
            'meta'         => empty($meta) ? null : json_encode($meta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);
    }
}
