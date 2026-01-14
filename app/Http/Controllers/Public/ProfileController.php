<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    public function show(Request $request, User $user, ?int $page = 1)
    {
        $page = max(1, (int) $page);
        Paginator::currentPageResolver(fn () => $page);

        $viewer = $request->user();
        $isOwner = $viewer && $viewer->id === $user->id;

        // Posts by this user (published only for public viewers; owner can see all if you want)
        $postsQuery = Post::query()
            ->where('user_id', $user->id)
            ->when(!$isOwner, fn ($q) => $q->where('status', Post::STATUS_PUBLISHED ?? 'published'))
            ->with(['tags:id,name,slug'])
            ->orderByDesc('created_at');

        $posts = $postsQuery->paginate(10)->withQueryString();

        // Stats
        $postsCount = (int) Post::where('user_id', $user->id)
            ->where('status', Post::STATUS_PUBLISHED ?? 'published')
            ->count();

        $commentsCount = (int) ($user->comments()->count());

        // If your posts table has reputation_points, sum it. If not, it becomes 0.
        $reputationPoints = (int) Post::where('user_id', $user->id)->sum('reputation_points');

        // Profile views:
        // Option A: if you store PageView morph for User, use that
        // Option B: fallback to user->pageViews count (your relation is hasMany, not morph)
        $profileViews = $this->getProfileViews($user);

        // Track view (don’t count owner)
        if (!$isOwner) {
            $this->trackProfileView($request, $user);
        }

        return view('users.profile', [
            'profileUser' => $user,
            'isOwner' => $isOwner,
            'posts' => $posts,
            'postsCount' => $postsCount,
            'commentsCount' => $commentsCount,
            'reputationPoints' => $reputationPoints,
            'profileViews' => $profileViews,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $viewer = $request->user();
        if (!$viewer || $viewer->id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'bio' => ['nullable', 'string', 'max:280'],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:200'], // KB (200KB)
        ]);

        // bio
        if (array_key_exists('bio', $data)) {
            $user->bio = trim((string) $data['bio']);
        }

        // avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');

            // Store in /storage/app/public/avatars
            // Make sure: php artisan storage:link
            $path = $file->storePublicly('avatars', ['disk' => 'public']);

            // delete old avatar file if it's a local file
            $this->deleteOldAvatarIfLocal($user->avatar);

            $user->avatar = $path; // store relative path in DB
        }

        $user->save();

        return back()->with('success', 'Profile updated.');
    }

    private function getProfileViews(User $user): int
    {
        // If you have a morph PageView setup for users, adjust this.
        // For now we’ll count rows in page_views where viewable is User (if columns exist).
        try {
            if (DB::getSchemaBuilder()->hasTable('page_views')) {
                $hasMorphCols =
                    DB::getSchemaBuilder()->hasColumn('page_views', 'viewable_type') &&
                    DB::getSchemaBuilder()->hasColumn('page_views', 'viewable_id');

                if ($hasMorphCols) {
                    return (int) DB::table('page_views')
                        ->where('viewable_type', User::class)
                        ->where('viewable_id', $user->id)
                        ->count();
                }
            }
        } catch (\Throwable $e) {
            // ignore and fallback
        }

        // fallback to your existing relation
        try {
            return (int) $user->pageViews()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    private function trackProfileView(Request $request, User $user): void
    {
        // Optional: avoid spam counts (same session)
        $key = 'profile_viewed_' . $user->id;
        if ($request->session()->has($key)) {
            return;
        }
        $request->session()->put($key, true);

        // If you have morph page views, create them
        try {
            if (DB::getSchemaBuilder()->hasTable('page_views')) {
                $hasMorphCols =
                    DB::getSchemaBuilder()->hasColumn('page_views', 'viewable_type') &&
                    DB::getSchemaBuilder()->hasColumn('page_views', 'viewable_id');

                if ($hasMorphCols) {
                    DB::table('page_views')->insert([
                        'viewable_type' => User::class,
                        'viewable_id' => $user->id,
                        'user_id' => $request->user()?->id,
                        'ip_address' => (string) $request->ip(),
                        'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Also log user_activities if table exists
        try {
            if (DB::getSchemaBuilder()->hasTable('user_activities')) {
                DB::table('user_activities')->insert([
                    'user_id' => $request->user()?->id,
                    'event' => 'profile_view',
                    'subject_type' => User::class,
                    'subject_id' => $user->id,
                    'ip_address' => (string) $request->ip(),
                    'user_agent' => mb_substr((string) $request->userAgent(), 0, 500),
                    'meta' => json_encode([
                        'username' => $user->username,
                    ], JSON_UNESCAPED_SLASHES),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function deleteOldAvatarIfLocal(?string $old): void
    {
        if (!$old) return;

        // if it's a URL, do nothing
        if (Str::startsWith($old, ['http://', 'https://'])) return;

        try {
            Storage::disk('public')->delete($old);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
