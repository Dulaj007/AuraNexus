<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostParagraph;
use App\Models\PostReaction;
use App\Models\Comment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PostController extends Controller
{
    /**
     * Show a post by slug
     */
    public function show(Request $request, Post $post)
    {
        // Load relations needed for the page
        $post->load([
            'user:id,username,name,avatar',
            'forum:id,name,slug,category_id',
            'forum.category:id,name,slug',
            'tags:id,name,slug',
            'highlightTag:id,name,slug',
        ]);

        $user = $request->user();
        $isOwner = $user && (int) $user->id === (int) $post->user_id;
        $canApprove = $user?->hasPermission('approve_post') ?? false;
        $isPending = $post->status !== 'published';
if ($post->status === 'removed') {
    $removed = \App\Models\RemovedPost::where('post_id', $post->id)->latest()->first();

    return view('post.removed', [
        'post'    => $post,
        'removed' => $removed,
    ]);
}
        // Admin configurable report message
        $reportMessage = Setting::where('key', 'report_post_message')
            ->value('value') ?: 'Please explain why you are reporting this post.';

        /* -------------------------------------------------
         |  HIDE PENDING POSTS FROM EVERYONE EXCEPT:
         |  - the owner
         |  - users who can approve
         ------------------------------------------------- */
        if ($isPending && !$isOwner && !$canApprove) {
            abort(404);
        }

        /* -------------------------------------------------
         |  VIEW COUNT (SESSION SAFE) - only for published
         ------------------------------------------------- */
        if ($post->status === 'published' && !$request->session()->has("viewed_post_{$post->id}")) {
            $post->increment('views');
            $request->session()->put("viewed_post_{$post->id}", true);
        }

        /* -------------------------------------------------
         |  PARSE POST CONTENT
         ------------------------------------------------- */
        $rendered = $post->parsedContent();


        /* -------------------------------------------------
         |  OPTIONAL SEO PARAGRAPH
         ------------------------------------------------- */
        $paragraph = PostParagraph::where('post_id', $post->id)
            ->latest()
            ->first();

        $metaText = $rendered['plainText'] ?? '';
        if ($paragraph?->content) {
            $metaText .= ' ' . $paragraph->content;
        }

        /* -------------------------------------------------
         |  JSON-LD
         ------------------------------------------------- */
        $jsonLd = $this->buildJsonLd(
            $post,
            $rendered['images'],
            url()->current(),
            $metaText
        );

        /* -------------------------------------------------
         |  REACTIONS
         ------------------------------------------------- */
        $reactionCount = PostReaction::where('post_id', $post->id)
            ->where('type', 'like')
            ->count();

        $userReacted = $user
            ? PostReaction::where('post_id', $post->id)
                ->where('user_id', $user->id)
                ->where('type', 'like')
                ->exists()
            : false;

        /* -------------------------------------------------
         |  COMMENTS (published for everyone + pending for owner/mod)
         ------------------------------------------------- */
        
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
    'post'           => $post,
    'isPending'      => $isPending,
    'canApprove'     => $canApprove,
    'rendered'       => $rendered,
    'paragraph'      => $paragraph,
    'jsonLd'         => $jsonLd,
    'reactionCount'  => $reactionCount,
    'userReacted'    => $userReacted,
    'reportMessage'  => $reportMessage,
    'comments'       => $comments,
    'pendingComments'=> $pendingComments,
    'isOwner'        => $isOwner,
]);
    }

    /**
     * Approve a pending post
     */
    public function approve(Request $request, Post $post)
    {
        if (!$request->user()?->hasPermission('approve_post')) {
            abort(403);
        }

        if ($post->status === 'published') {
            return back()->with('success', 'This post is already published.');
        }

        $post->update(['status' => 'published']);

        return back()->with('success', 'Post published successfully.');
    }

    /* ============================================================
     |  CONTENT PARSER
     ============================================================ */

    private function parsePostContent(string $content, string $title): array
    {
        $lines = preg_split("/\r\n|\n|\r/", $content);

        $sections = [];
        $images = [];
        $plainTextParts = [];
        $currentBlock = null;

        foreach ($lines as $raw) {
            $line = trim($raw);
            if ($line === '') continue;

            if (preg_match('/^(download\s*links?)\s*:?\s*$/i', $line)) {
                $currentBlock = 'download';
                $sections[] = ['type' => 'heading', 'text' => 'Download Links', 'block' => $currentBlock];
                $plainTextParts[] = 'Download Links';
                continue;
            }

            if (preg_match('/^(watch\s*online)\s*:?\s*$/i', $line)) {
                $currentBlock = 'watch';
                $sections[] = ['type' => 'heading', 'text' => 'Watch Online', 'block' => $currentBlock];
                $plainTextParts[] = 'Watch Online';
                continue;
            }

            if (preg_match('/^\[URL=(.*?)\]\[IMG\](.*?)\[\/IMG\]\[\/URL\]$/i', $line, $m)) {
                $images[] = $m[1];
                $sections[] = [
                    'type'  => 'image',
                    'full'  => $m[1],
                    'thumb' => $m[2],
                    'block' => $currentBlock,
                ];
                continue;
            }

            if (preg_match('/^https?:\/\/\S+\.(png|jpe?g|webp|gif)(\?\S*)?$/i', $line)) {
                $images[] = $line;
                $sections[] = [
                    'type'  => 'image',
                    'full'  => $line,
                    'thumb' => $line,
                    'block' => $currentBlock,
                ];
                continue;
            }

            if (filter_var($line, FILTER_VALIDATE_URL)) {
                $sections[] = ['type' => 'link', 'url' => $line, 'block' => $currentBlock];
                $plainTextParts[] = $line;
                continue;
            }

            $sections[] = ['type' => 'text', 'text' => $line, 'block' => $currentBlock];
            $plainTextParts[] = $line;
        }

        $images = array_values(array_unique($images));
        $plainText = Str::limit(trim(implode("\n", $plainTextParts)), 5000);

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
            'articleBody' => $plainText ?: Str::limit(strip_tags($post->content), 5000),
        ];
    }
}
