<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
    /**
     * Show a user's profile with posts, stats, and pagination.
     */
    public function show(Request $request, User $user)
    {
        $viewer  = $request->user();
        $isOwner = $viewer && ((int) $viewer->id === (int) $user->id);

        // Posts by this user
        $postsQuery = Post::query()
            ->where('user_id', $user->id)
            ->when(!$isOwner, fn ($q) => $q->where('status', Post::STATUS_PUBLISHED ?? 'published'))
            ->with(['tags:id,name,slug', 'highlightTag:id,name,slug'])
            ->orderByDesc('created_at');

        // Paginate posts (10 per page)
        $posts = $postsQuery->paginate(9)->withQueryString();

        // Counts for stats
        $postsCount = Post::where('user_id', $user->id)
            ->where('status', Post::STATUS_PUBLISHED ?? 'published')
            ->count();

        $commentsCount = $user->comments()->count();

        // Reputation points from likes on user's posts
        $reputationPoints = PostReaction::query()
            ->where('type', 'like')
            ->whereHas('post', fn ($q) => $q->where('user_id', $user->id))
            ->count();

        // Profile views
        $profileViews = $this->getProfileViews($user);

        // Track view (don’t count owner)
        if (!$isOwner) {
            $this->trackProfileView($request, $user);
        }

        return view('users.profile', [
            'profileUser'      => $user,
            'isOwner'          => $isOwner,
            'posts'            => $posts,
            'postsCount'       => $postsCount,
            'commentsCount'    => $commentsCount,
            'reputationPoints' => $reputationPoints,
            'profileViews'     => $profileViews,
        ]);
    }

    /**
     * Update a user's profile (bio and avatar)
     */
    public function update(Request $request, User $user)
    {
        $viewer = $request->user();

        $canEditProfile = $viewer && (
            (int) $viewer->id === (int) $user->id
            || $viewer->hasPermission('edit_profile')
            || $viewer->hasRole('admin')
        );

        if (!$canEditProfile) {
            abort(403);
        }

        $data = $request->validate([
            'bio'    => ['nullable', 'string', 'max:280'],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:200'], // KB
        ]);

        if (isset($data['bio'])) {
            $user->bio = trim((string) $data['bio']);
        }

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $path = $file->storePublicly('avatars', ['disk' => 'public']);
            $this->deleteOldAvatarIfLocal($user->avatar);
            $user->avatar = $path;
        }

        $user->save();

        return back()->with('success', 'Profile updated.');
    }

    /**
     * Get profile views count
     */
    private function getProfileViews(User $user): int
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('page_views')) {
                $hasMorphCols =
                    DB::getSchemaBuilder()->hasColumn('page_views', 'viewable_type') &&
                    DB::getSchemaBuilder()->hasColumn('page_views', 'viewable_id');

                if ($hasMorphCols) {
                    return DB::table('page_views')
                        ->where('viewable_type', User::class)
                        ->where('viewable_id', $user->id)
                        ->count();
                }
            }
        } catch (\Throwable) {
            // fallback
        }

        try {
            return $user->pageViews()->count();
        } catch (\Throwable) {
            return 0;
        }
    }

    /**
     * Track profile views for analytics
     */
    private function trackProfileView(Request $request, User $user): void
    {
        $key = 'profile_viewed_' . $user->id;
        if ($request->session()->has($key)) return;
        $request->session()->put($key, true);

        try {
            if (DB::getSchemaBuilder()->hasTable('page_views')) {
                $hasMorphCols =
                    DB::getSchemaBuilder()->hasColumn('page_views', 'viewable_type') &&
                    DB::getSchemaBuilder()->hasColumn('page_views', 'viewable_id');

                if ($hasMorphCols) {
                    DB::table('page_views')->insert([
                        'viewable_type' => User::class,
                        'viewable_id'   => $user->id,
                        'user_id'       => $request->user()?->id,
                        'ip_address'    => (string) $request->ip(),
                        'user_agent'    => mb_substr((string) $request->userAgent(), 0, 500),
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }
            }
        } catch (\Throwable) {
            // ignore
        }
    }

    /**
     * Delete old avatar file if stored locally
     */
    private function deleteOldAvatarIfLocal(?string $old): void
    {
        if (!$old) return;
        if (Str::startsWith($old, ['http://', 'https://'])) return;

        try {
            Storage::disk('public')->delete($old);
        } catch (\Throwable) {
            // ignore
        }
    }
}