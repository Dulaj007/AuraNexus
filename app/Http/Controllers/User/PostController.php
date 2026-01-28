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
use App\Services\PostLinkifier;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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

        // ✅ removed posts -> custom removed page
        if ($post->status === Post::STATUS_REMOVED || $post->status === 'removed') {
            $removed = RemovedPost::where('post_id', $post->id)->latest()->first();

            return view('post.removed', [
                'post'    => $post,
                'removed' => $removed,
            ]);
        }

        // ✅ pending flag (only for non-removed)
        $isPending = $post->status !== Post::STATUS_PUBLISHED;

        // ✅ hide pending posts from everyone except owner/approver
        if ($isPending && !$isOwner && !$canApprove) {
            abort(404);
        }

        // ✅ view count (only for published)
        if ($post->status === Post::STATUS_PUBLISHED && !$request->session()->has("viewed_post_{$post->id}")) {
            $post->increment('views');
            $request->session()->put("viewed_post_{$post->id}", true);
        }

        // ✅ lightweight parse (NO $post->parsedContent())
        $rendered = $this->buildRenderedFromContent(
            (string) $post->content,
            (string) $post->title
        );

        // ✅ Convert outbound links into /link/{code} (download gate) + add masked display
        $rendered = app(PostLinkifier::class)->linkifySections($post->id, $rendered);

        // ✅ optional SEO paragraph
        $paragraph = PostParagraph::where('post_id', $post->id)->latest()->first();

        $metaText = $rendered['plainText'] ?? '';
        if ($paragraph?->content) {
            $metaText .= ' ' . $paragraph->content;
        }

        // ✅ json-ld
        $jsonLd = $this->buildJsonLd(
            $post,
            $rendered['images'] ?? [],
            url()->current(),
            $metaText
        );

        // ✅ reactions
        $reactionCount = PostReaction::where('post_id', $post->id)
            ->where('type', 'like')
            ->count();

        $userReacted = $user
            ? PostReaction::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->where('type', 'like')
                ->exists()
            : false;

        // ✅ report message
        $reportMessage = Setting::where('key', 'report_post_message')
            ->value('value') ?: 'Please explain why you are reporting this post. Reports are reviewed by moderators.';

        // ✅ saved?
        $isSaved = false;
        if ($user) {
            $isSaved = $user->savedPosts()->where('post_id', $post->id)->exists();
        }

        // ✅ share count (using activities)
        $shareCount = UserActivity::where('event', 'post_shared')
            ->where('subject_type', Post::class)
            ->where('subject_id', $post->id)
            ->count();

        // ✅ comments (Collections!)
        $commentsQuery = Comment::with('user:id,username,name,avatar')
            ->where('post_id', $post->id)
            ->latest();

        $comments = (clone $commentsQuery)
            ->where('status', 'published')
            ->get();

        $pendingComments = collect();

        if ($canApprove) {
            $pendingComments = (clone $commentsQuery)
                ->where('status', 'pending')
                ->get();
        } elseif ($user) {
            $pendingComments = (clone $commentsQuery)
                ->where('status', 'pending')
                ->where('user_id', $user->id)
                ->get();
        }

        // ✅ RELATED POSTS (tag overlap score, then most recent)
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

        return view('post.show', [
            'post'            => $post,
            'relatedPosts'    => $relatedPosts,

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
            'articleBody' => $plainText ?: Str::limit(strip_tags((string) $post->content), 5000),
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
