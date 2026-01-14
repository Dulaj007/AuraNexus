<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PostReport;
use App\Models\Setting;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Allow admin OR users with approve_post (or change to a dedicated permission if you have one)
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('approve_post'))) {
            abort(403);
        }

        $q = trim((string) $request->query('q', ''));

        $reports = PostReport::query()
            ->with([
                'post:id,title,slug,status',
                'user:id,username,name,email,avatar',
            ])
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($sub) use ($q) {
                    $sub->where('reason', 'like', "%{$q}%")
                        ->orWhereHas('post', fn ($p) => $p->where('title', 'like', "%{$q}%"))
                        ->orWhereHas('user', fn ($u) => $u
                            ->where('username', 'like', "%{$q}%")
                            ->orWhere('name', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $reportMessage = Setting::get(
            'report_post_message',
            'Please explain why you are reporting this post.'
        );

        return view('admin.reports.index', [
            'reports' => $reports,
            'reportMessage' => $reportMessage,
            'q' => $q,
        ]);
    }

    public function updateReportMessage(Request $request)
    {
        $user = $request->user();

        // Only admin should change the global report prompt text
        if (!$user || !$user->hasRole('admin')) {
            abort(403);
        }

        $data = $request->validate([
            'report_post_message' => ['required', 'string', 'max:500'],
        ]);

        Setting::set('report_post_message', $data['report_post_message']);

        return back()->with('success', 'Report message updated.');
    }
}
