<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Models\PostReaction;
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

        $viewer  = $request->user();
        $isOwner = $viewer && (int) $viewer->id === (int) $user->id;

        // Posts by this user
        $postsQuery = Post::query()
            ->where('user_id', $user->id)
            ->when(!$isOwner, fn ($q) => $q->where('status', Post::STATUS_PUBLISHED ?? 'published'))
            ->with(['tags:id,name,slug', 'highlightTag:id,name,slug'])
            ->orderByDesc('created_at');

        $posts = $postsQuery->paginate(10)->withQueryString();

        // Stats
        $postsCount = (int) Post::where('user_id', $user->id)
            ->where('status', Post::STATUS_PUBLISHED ?? 'published')
            ->count();

        $commentsCount = (int) ($user->comments()->count());

        /**
         * ✅ Reputation (calculated from reactions table)
         * Count total "like" reactions on ALL posts owned by this user.
         * (ignores whether user.reputation_points exists)
         */
        $reputationPoints = (int) PostReaction::query()
            ->where('type', 'like')
            ->whereHas('post', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
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

    public function update(Request $request, User $user)
    {
        $viewer = $request->user();

        $canEditProfile = $viewer && (
            (int) $viewer->id === (int) $user->id
            || $viewer->hasPermission('edit_profile')
            || $viewer->hasRole('admin')
        );

        if (!$canEditProfile) abort(403);

        $data = $request->validate([
            'bio'    => ['nullable', 'string', 'max:280'],
            'avatar' => ['nullable', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:200'], // KB
        ]);

        if (array_key_exists('bio', $data)) {
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

    private function getProfileViews(User $user): int
    {
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

        try {
            return (int) $user->pageViews()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

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
        } catch (\Throwable $e) {
            // ignore
        }

        try {
            if (DB::getSchemaBuilder()->hasTable('user_activities')) {
                DB::table('user_activities')->insert([
                    'user_id'      => $request->user()?->id,
                    'event'        => 'profile_view',
                    'subject_type' => User::class,
                    'subject_id'   => $user->id,
                    'ip_address'   => (string) $request->ip(),
                    'user_agent'   => mb_substr((string) $request->userAgent(), 0, 500),
                    'meta'         => json_encode([
                        'username' => $user->username,
                    ], JSON_UNESCAPED_SLASHES),
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    private function deleteOldAvatarIfLocal(?string $old): void
    {
        if (!$old) return;
        if (Str::startsWith($old, ['http://', 'https://'])) return;

        try {
            Storage::disk('public')->delete($old);
        } catch (\Throwable $e) {
            // ignore
        }
    }
}
