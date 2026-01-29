<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Post;
use Illuminate\Support\Facades\DB;

class FixPostSlugsFromTitles extends Command
{
    protected $signature = 'posts:fix-slugs {--dry-run : Show what would change without updating}';
    protected $description = 'Fix posts whose slug accidentally equals the related model slug; regenerate slug from title.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        // Find "bad" posts: posts.slug == models.slug (and model_id exists)
        // Assumes your models table is named "models" with a "slug" column.
        $badPosts = Post::query()
            ->join('models', 'posts.model_id', '=', 'models.id')
            ->whereNotNull('posts.model_id')
            ->whereColumn('posts.slug', 'models.slug')
            ->select('posts.id', 'posts.title', 'posts.slug')
            ->orderBy('posts.id')
            ->get();

        if ($badPosts->isEmpty()) {
            $this->info('No bad slugs found. Nothing to fix.');
            return self::SUCCESS;
        }

        $this->info("Found {$badPosts->count()} posts with bad slugs.");

        $changed = 0;

        DB::transaction(function () use ($badPosts, $dry, &$changed) {
            foreach ($badPosts as $row) {
                $postId = (int) $row->id;
                $title  = (string) $row->title;
                $old    = (string) $row->slug;

                $new = $this->uniqueSlugFromTitle($title, $postId);

                if ($new === $old) {
                    continue;
                }

                $this->line("#{$postId}  {$old}  ->  {$new}");

                if (!$dry) {
                    Post::where('id', $postId)->update(['slug' => $new]);
                }

                $changed++;
            }
        });

        if ($dry) {
            $this->warn("DRY RUN complete. Would change {$changed} posts. Run without --dry-run to apply.");
        } else {
            $this->info("Done. Updated {$changed} posts.");
        }

        return self::SUCCESS;
    }

    private function uniqueSlugFromTitle(string $title, int $currentPostId): string
    {
        $base = Str::slug(trim($title));

        if ($base === '') {
            $base = Str::random(10);
        }

        $slug = $base;
        $i = 2;

        // Ensure uniqueness excluding the current post
        while (
            Post::where('slug', $slug)
                ->where('id', '!=', $currentPostId)
                ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
