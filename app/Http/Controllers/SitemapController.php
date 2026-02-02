<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

use App\Models\Post;
use App\Models\Category;
use App\Models\Forum;
use App\Models\Tag;
use App\Models\Page;

class SitemapController extends Controller
{
    public function index(Request $request)
    {
        $sitemap = Sitemap::create();

        // Home
        $sitemap->add(
            Url::create(route('home'))
                ->setPriority(1.0)
                ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
        );

        // Categories
        if (\Route::has('categories.show') && class_exists(Category::class)) {
            Category::query()->each(function ($category) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('categories.show', $category->slug))
                        ->setPriority(0.7)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });
        }

        // Forums (page 1 only)
        if (\Route::has('forums.show') && class_exists(Forum::class)) {
            Forum::query()->each(function ($forum) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('forums.show', ['forum' => $forum->slug]))
                        ->setPriority(0.6)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });
        }

        // Tags (page 1 only)
        if (\Route::has('tags.show') && class_exists(Tag::class)) {
            Tag::query()->each(function ($tag) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('tags.show', $tag->slug))
                        ->setPriority(0.5)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                );
            });
        }

        // Latest + Popular (optional but nice)
        if (\Route::has('latest.index')) {
            $sitemap->add(
                Url::create(route('latest.index'))
                    ->setPriority(0.4)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            );
        }

        if (\Route::has('popular.index')) {
            $sitemap->add(
                Url::create(route('popular.index'))
                    ->setPriority(0.4)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY)
            );
        }

        // Posts
        if (\Route::has('post.show') && class_exists(Post::class)) {
            Post::query()
                // ✅ ADD YOUR "published only" logic here:
                // ->where('status', 'published')
                // ->whereNotNull('published_at')
                ->each(function ($post) use ($sitemap) {
                    $sitemap->add(
                        Url::create(route('post.show', $post->slug))
                            ->setPriority(0.8)
                            ->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY)
                    );
                });
        }

        // Public pages (/p/{slug})
        if (\Route::has('pages.show') && class_exists(Page::class)) {
            Page::query()->each(function ($page) use ($sitemap) {
                $sitemap->add(
                    Url::create(route('pages.show', $page->slug))
                        ->setPriority(0.3)
                        ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
                );
            });
        }

        // Fixed pages that you map via "/{page:slug}" constraint
        foreach (['terms', 'privacy', 'dmca', 'contact'] as $fixed) {
            $sitemap->add(
                Url::create(url('/' . $fixed))
                    ->setPriority(0.2)
                    ->setChangeFrequency(Url::CHANGE_FREQUENCY_YEARLY)
            );
        }

        return $sitemap->toResponse($request);
    }
}
