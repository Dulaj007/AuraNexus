<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostParagraph;
use App\Models\PostReaction;
use App\Models\Setting;
use App\Models\UserActivity;
use App\Models\RemovedPost;
use App\Services\BbcodeImageParser;
use App\Services\PostLinkifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Purifier;
class PostController extends Controller
{
    /**
     * Show a post by slug
     */
    public function show(Request $request, Post $post)
    {
        $post->load([
            'user:id,username,name,avatar',
            'forum:id,name,slug,category_id',
            'forum.category:id,name,slug',
            'tags:id,name,slug',
            'highlightTag:id,name,slug',
        ]);

        $user = $request->user();

        $isOwner    = $user && (int) $user->id === (int) $post->user_id;
        $canApprove = $user?->hasPermission('approve_post') ?? false;

        // Removed posts get their own template instead of the normal show view.
        if ($post->status === Post::STATUS_REMOVED || $post->status === 'removed') {
            $removed = RemovedPost::where('post_id', $post->id)->latest()->first();

            return response()->view('post.removed', [
                'post'    => $post,
                'removed' => $removed,
            ], 410);
        }

        $isPending = $post->status !== Post::STATUS_PUBLISHED;

        // Pending posts are only visible to their owner or someone who can approve them.
        if ($isPending && !$isOwner && !$canApprove) {
            abort(404);
        }

        // Count the view once per session so refreshes don't inflate the number.
        if ($post->status === Post::STATUS_PUBLISHED && !$request->session()->has("viewed_post_{$post->id}")) {
            $post->increment('views');
            $request->session()->put("viewed_post_{$post->id}", true);
        }

        try {
            // Older posts may still contain [url=..][img]..[/img][/url] bbcode
            // pasted without using the editor's "Image" button. Convert it to
            // real <img> tags before sanitizing so it renders as an image
            // instead of literal bracket text.
            $withImages = app(BbcodeImageParser::class)->parse((string) $post->content);
            $cleanContent = Purifier::clean($withImages, 'post');

            if ($cleanContent !== $post->content) {
                // Log what Purifier stripped so it can be reviewed if content looks wrong.
                \Log::info('Purifier removed content from post ID '.$post->id, [
                    'original' => $post->content,
                    'cleaned'  => $cleanContent,
                ]);
            }

            // Only applied in memory, not saved, so the view renders the
            // converted and sanitized HTML without rewriting the stored value.
            $post->content = $cleanContent;

            $rendered = $this->buildRenderedFromContent(
                (string) $cleanContent,
                (string) $post->title
            );
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to render the post content safely.');
        }

        // Swap outbound links for /link/{code} download-gate URLs with a masked display.
        $rendered = app(PostLinkifier::class)->linkifySections($post->id, $rendered);

        $paragraph = PostParagraph::where('post_id', $post->id)->latest()->first();

        $metaText = $rendered['plainText'] ?? '';
        if ($paragraph?->content) {
            $metaText .= ' ' . $paragraph->content;
        }

        $jsonLd = $this->buildJsonLd(
            $post,
            $rendered['images'] ?? [],
            url()->current(),
            $metaText
        );

        $reactionCount = PostReaction::where('post_id', $post->id)
            ->where('type', 'like')
            ->count();

        $userReacted = $user
            ? PostReaction::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->where('type', 'like')
                ->exists()
            : false;

        $reportMessage = Setting::where('key', 'report_post_message')
            ->value('value') ?: 'Please explain why you are reporting this post. Reports are reviewed by moderators.';

        $isSaved = false;
        if ($user) {
            $isSaved = $user->savedPosts()->where('post_id', $post->id)->exists();
        }

        $shareCount = UserActivity::where('event', 'post_shared')
            ->where('subject_type', Post::class)
            ->where('subject_id', $post->id)
            ->count();

        $commentsQuery = Comment::with('user:id,username,name,avatar')
            ->where('post_id', $post->id)
            ->latest();

        // Capped so a post with an unusually large thread can't load an
        // unbounded result set into memory on every view.
        $comments = (clone $commentsQuery)
            ->where('status', 'published')
            ->limit(500)
            ->get();

        $pendingComments = collect();

        if ($canApprove) {
            $pendingComments = (clone $commentsQuery)
                ->where('status', 'pending')
                ->limit(500)
                ->get();
        } elseif ($user) {
            $pendingComments = (clone $commentsQuery)
                ->where('status', 'pending')
                ->where('user_id', $user->id)
                ->limit(500)
                ->get();
        }

        // Related posts: ranked by tag overlap first, then recency.
        $tagIds = $post->tags->pluck('id')->values();
        $relatedPosts = collect();

        if ($tagIds->isNotEmpty()) {
            $relatedPosts = Post::query()
                ->where('status', Post::STATUS_PUBLISHED)
                ->where('id', '!=', $post->id)
                ->whereHas('tags', fn ($q) => $q->whereIn('tags.id', $tagIds))
                ->with([
                    'forum:id,name,slug,category_id',
                    'forum.category:id,name,slug',
                    'tags:id,name,slug',
                    'highlightTag:id,name,slug',
                    'user:id,username,name,avatar',
                ])
                ->withCount([
                    'tags as overlap_count' => fn ($q) => $q->whereIn('tags.id', $tagIds),
                ])
                ->orderByDesc('overlap_count')
                ->orderByDesc('created_at')
                ->limit(4)
                ->get();
        }

        // Recent posts for the sidebar: just the latest published posts.
        $recentPosts = Post::query()
            ->where('status', Post::STATUS_PUBLISHED)
            ->where('id', '!=', $post->id)
            ->whereNotIn('id', $relatedPosts->pluck('id'))
            ->with([
                'forum:id,name,slug,category_id',
                'forum.category:id,name,slug',
                'tags:id,name,slug',
                'highlightTag:id,name,slug',
                'user:id,username,name,avatar',
            ])
            ->latest()
            ->limit(5)
            ->get();

        return view('post.show', [
            'post'            => $post,
            'relatedPosts'    => $relatedPosts,
            'recentPosts'     => $recentPosts,

            'isPending'       => $isPending,
            'canApprove'      => $canApprove,
            'rendered'        => $rendered,
            'paragraph'       => $paragraph,
            'jsonLd'          => $jsonLd,
            'reactionCount'   => $reactionCount,
            'userReacted'     => $userReacted,
            'reportMessage'   => $reportMessage,
            'comments'        => $comments,
            'pendingComments' => $pendingComments,
            'isOwner'         => $isOwner,
            'isSaved'         => $isSaved,
            'shareCount'      => $shareCount,
        ]);
    }

    /**
     * Approve a pending post
     */
    public function approve(Request $request, Post $post)
    {
        $user = $request->user();

        if (!$user || !$user->hasPermission('approve_post')) {
            abort(403);
        }

        if ($post->status === Post::STATUS_PUBLISHED) {
            return back()->with('success', 'This post is already published.');
        }

        $post->update(['status' => Post::STATUS_PUBLISHED]);

        $this->logActivity(
            $request,
            $user->id,
            'post_approved',
            Post::class,
            $post->id
        );

        return back()->with('success', 'Post published successfully.');
    }

    /**
     * Lightweight content parser:
     * - extracts images from [IMG]...[/IMG] + direct image URLs
     * - extracts normal http/https links
     * - builds plain text (masked, no raw huge urls)
     */
    private function buildRenderedFromContent(string $content, string $title = ''): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $content) ?: [];

        $sections = [];
        $images   = [];
        $plain    = [];
        $currentBlock = null;

        foreach ($lines as $raw) {
            $line = trim((string) $raw);
            if ($line === '') continue;

            // headings
            if (preg_match('/^(download\s*links?)\s*:?\s*$/i', $line)) {
                $currentBlock = 'download';
                $sections[] = ['type' => 'heading', 'text' => 'Download Links', 'block' => $currentBlock];
                $plain[] = 'Download Links';
                continue;
            }

            if (preg_match('/^(watch\s*online)\s*:?\s*$/i', $line)) {
                $currentBlock = 'watch';
                $sections[] = ['type' => 'heading', 'text' => 'Watch Online', 'block' => $currentBlock];
                $plain[] = 'Watch Online';
                continue;
            }

            // [IMG]URL[/IMG] (even if inside [URL] wrapper)
            if (preg_match('/\[IMG\]\s*(https?:\/\/[^\s\]]+)\s*\[\/IMG\]/i', $line, $m)) {
                $img = trim($m[1]);

                $images[] = $img;

                $sections[] = [
                    'type'  => 'image',
                    'full'  => $img,
                    'thumb' => $img,
                    'block' => $currentBlock,
                    'alt'   => $title ?: 'Image',
                    'title' => $title ?: 'Image',
                ];
                continue;
            }

            // direct image URL line
            if (preg_match('/^https?:\/\/\S+\.(png|jpe?g|webp|gif)(\?\S*)?$/i', $line)) {
                $images[] = $line;

                $sections[] = [
                    'type'  => 'image',
                    'full'  => $line,
                    'thumb' => $line,
                    'block' => $currentBlock,
                    'alt'   => $title ?: 'Image',
                    'title' => $title ?: 'Image',
                ];
                continue;
            }

            // normal link line
            if (filter_var($line, FILTER_VALIDATE_URL)) {
                $host = parse_url($line, PHP_URL_HOST) ?: 'link';
                $display = $host . '/…';

                $sections[] = [
                    'type'     => 'link',
                    'url'      => $line,       // original
                    'gate_url' => $line,       // PostLinkifier will replace if needed
                    'display'  => $display,
                    'block'    => $currentBlock,
                ];

                $plain[] = $display;
                continue;
            }

            // plain text
            $sections[] = ['type' => 'text', 'text' => $line, 'block' => $currentBlock];
            $plain[] = $line;
        }

        $images = array_values(array_unique(array_filter($images)));
        $plainText = Str::limit(trim(implode("\n", $plain)), 5000);

        return [
            'sections'  => $sections,
            'images'    => $images,
            'plainText' => $plainText,
        ];
    }

    /* ============================================================
     |  JSON-LD SEO
     ============================================================ */

    private function buildJsonLd(Post $post, array $images, string $url, string $plainText = ''): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'DiscussionForumPosting',
            'headline' => $post->title,
            'datePublished' => optional($post->created_at)?->toIso8601String(),
            'dateModified'  => optional($post->updated_at)?->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name'  => optional($post->user)->name ?? optional($post->user)->username ?? 'Member',
            ],
            'url'         => $url,
            'image'       => $images,
            'articleBody' => $plainText ?: Str::limit(strip_tags(Purifier::clean((string) $post->content, 'post')), 5000),
        ];
    }

    /* ============================================================
     |  ACTIVITY LOGGER (UserActivity)
     ============================================================ */

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
