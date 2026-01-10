<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RemovedPost;
use App\Models\RemovedComment;
use Illuminate\Http\Request;

class RemovalReportController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Permission check (use your own if you have a dedicated admin permission)
        if (!$user || !$user->hasPermission('delete_post')) {
            abort(403);
        }

        $q = trim((string) $request->get('q', ''));

        // main tab in admin/reports.blade.php
        $tab = $request->get('tab', 'removals'); // reports | removals
        $removedTab = $request->get('removedTab', 'posts'); // posts | comments

        // REMOVED POSTS
        $removedPosts = RemovedPost::query()
            ->with([
                'post.user:id,username,name,avatar',
                'remover:id,username,name,avatar',
                'post.forum:id,name,slug,category_id',
                'post.forum.category:id,name,slug',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('reason', 'like', "%{$q}%")
                    ->orWhereHas('post', fn($p) => $p->where('title', 'like', "%{$q}%"))
                    ->orWhereHas('post.user', fn($u) => $u->where('username', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%"));
            })
            ->latest()
            ->paginate(15, ['*'], 'removed_posts_page')
            ->withQueryString();

        // REMOVED COMMENTS
        $removedComments = RemovedComment::query()
            ->with([
                'comment.user:id,username,name,avatar',
                'remover:id,username,name,avatar',
                'comment.post:id,title,slug,status',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where('reason', 'like', "%{$q}%")
                    ->orWhereHas('comment', fn($c) => $c->where('content', 'like', "%{$q}%"))
                    ->orWhereHas('comment.user', fn($u) => $u->where('username', 'like', "%{$q}%")
                        ->orWhere('name', 'like', "%{$q}%"));
            })
            ->latest()
            ->paginate(15, ['*'], 'removed_comments_page')
            ->withQueryString();

        // If your admin/reports.blade.php still expects normal reports data,
        // you can pass empty placeholders here, OR better: merge with your ReportsController.
        // For now we pass placeholders to avoid "undefined variable" errors.
        $reports = collect(); // replace with your real reports pagination if needed
        $reportMessage = '';  // replace with Setting::... if needed

        return view('admin.reports.removals', [
            'tab' => $tab,
            'removedTab' => $removedTab,
            'q' => $q,

            // Removed content
            'removedPosts' => $removedPosts,
            'removedComments' => $removedComments,

            // Placeholders (so blade won't error if it references these)
            'reports' => $reports,
            'reportMessage' => $reportMessage,
        ]);

        
    }
}
