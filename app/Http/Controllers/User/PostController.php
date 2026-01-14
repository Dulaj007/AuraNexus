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
if ($post->status === Post::STATUS_REMOVED) {
    // custom removed page (you control the design)
    return response()
        ->view('posts.removed', ['post' => $post], 410); // 410 Gone is ideal
}

        // ✅ removed posts -> custom removed page
        if ($post->status === 'removed') {
            $removed = RemovedPost::where('post_id', $post->id)->latest()->first();

            return view('post.removed', [
                'post'    => $post,
                'removed' => $removed,
            ]);
        }

        // ✅ pending flag (only for non-removed)
        $isPending = $post->status !== 'published';

        // ✅ hide pending posts from everyone except owner/approver
        if ($isPending && !$isOwner && !$canApprove) {
            abort(404);
        }

        // ✅ view count (only for published)
        if ($post->status === 'published' && !$request->session()->has("viewed_post_{$post->id}")) {
            $post->increment('views');
            $request->session()->put("viewed_post_{$post->id}", true);

            // (optional) activity log for views - comment out if you don't want it
            // if ($user) {
            //     $this->logActivity($request, $user->id, 'post_viewed', Post::class, $post->id);
            // }
        }

        // ✅ parse post content
        $rendered = $post->parsedContent();

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
            // assumes you have User::savedPosts() relationship
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

        // published for everyone
        $comments = (clone $commentsQuery)
            ->where('status', 'published')
            ->get();

        // pending:
        // - approvers see all pending
        // - non-approver logged user sees ONLY their own pending
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

        return view('post.show', [
            'post'            => $post,
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

        if ($post->status === 'published') {
            return back()->with('success', 'This post is already published.');
        }

        $post->update(['status' => 'published']);

        // ✅ activity log
        $this->logActivity(
            $request,
            $user->id,
            'post_approved',
            Post::class,
            $post->id
        );

        return back()->with('success', 'Post published successfully.');
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
            'articleBody' => $plainText ?: Str::limit(strip_tags($post->content), 5000),
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
