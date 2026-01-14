<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RemovedComment;
use App\Models\RemovedPost;
use Illuminate\Http\Request;

class RemovalReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // allow admin OR delete_post permission
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('delete_post'))) {
            abort(403);
        }

        $q = trim((string) $request->query('q', ''));
        $removedTab = (string) $request->query('removedTab', 'posts'); // posts | comments

        // normalize tab value
        if (!in_array($removedTab, ['posts', 'comments'], true)) {
            $removedTab = 'posts';
        }

        // REMOVED POSTS
        $removedPosts = RemovedPost::query()
            ->with([
                'post:id,user_id,forum_id,title,slug,status,created_at',
                'post.user:id,username,name,avatar',
                'post.forum:id,name,slug,category_id',
                'post.forum.category:id,name,slug',
                'remover:id,username,name,avatar',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('reason', 'like', "%{$q}%")
                        ->orWhereHas('post', fn ($p) => $p->where('title', 'like', "%{$q}%"))
                        ->orWhereHas('post.user', fn ($u) => $u
                            ->where('username', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->paginate(15, ['*'], 'removed_posts_page')
            ->withQueryString();

        // REMOVED COMMENTS
        $removedComments = RemovedComment::query()
            ->with([
                'comment:id,user_id,post_id,content,created_at',
                'comment.user:id,username,name,avatar',
                'comment.post:id,title,slug,status',
                'remover:id,username,name,avatar',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('reason', 'like', "%{$q}%")
                        ->orWhereHas('comment', fn ($c) => $c->where('content', 'like', "%{$q}%"))
                        ->orWhereHas('comment.user', fn ($u) => $u
                            ->where('username', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->paginate(15, ['*'], 'removed_comments_page')
            ->withQueryString();

        return view('admin.reports.removals', [
            'removedTab' => $removedTab,
            'q' => $q,
            'removedPosts' => $removedPosts,
            'removedComments' => $removedComments,
        ]);
    }
}
