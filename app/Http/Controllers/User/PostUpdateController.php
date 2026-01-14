<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Forum;
use App\Models\ParagraphTemplate;
use App\Models\Post;
use App\Models\PostEdit;
use App\Models\PostParagraph;
use App\Models\Tag;
use App\Support\LogsUserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PostUpdateController extends Controller
{
    use LogsUserActivity;

    public function edit(Request $request, Post $post)
    {
        $user = $request->user();

        // ✅ Only owner OR edit_post can access
        $isOwner   = $user && (int) $user->id === (int) $post->user_id;
        $canEditAny = $user?->hasPermission('edit_post') ?? false;

        if (!$isOwner && !$canEditAny) {
            return redirect()->route('home');
        }

        $forums = Forum::query()->orderBy('name')->get(['id', 'name']);

        $templates = ParagraphTemplate::query()
            ->orderBy('category')
            ->orderBy('id')
            ->get(['id', 'category', 'content'])
            ->groupBy('category');

        // Existing tags + highlight
        $existingTagNames = $post->tags()->pluck('name')->toArray();
        $existingHighlightName = $post->highlightTag?->name ?? '';

        // Existing paragraph (your save uses order=1)
        $existingParagraph = PostParagraph::where('post_id', $post->id)
            ->where('order', 1)
            ->first();

        return view('posting.create', [
            'mode' => 'edit',
            'post' => $post,

            'forums' => $forums,
            'templates' => $templates,

            // for form prefills (your JS can use these)
            'existingTagNames' => $existingTagNames,
            'existingHighlightName' => $existingHighlightName,
            'existingParagraph' => $existingParagraph,
        ]);
    }

    public function update(Request $request, Post $post)
    {
        $user = $request->user();

        $isOwner    = $user && (int) $user->id === (int) $post->user_id;
        $canEditAny = $user?->hasPermission('edit_post') ?? false;

        if (!$isOwner && !$canEditAny) {
            return redirect()->route('home');
        }

        $validated = $request->validate([
            'forum_id' => ['required', 'integer', 'exists:forums,id'],
            'title'    => ['required', 'string', 'min:5', 'max:150'],
            'content'  => ['required', 'string', 'min:20'],

            'tag_names' => ['nullable', 'array', 'max:15'],
            'tag_names.*' => ['string', 'min:1', 'max:30'],

            'highlight_tag_name' => ['nullable', 'string', 'min:1', 'max:30'],

            'paragraph_template_id' => ['nullable', 'integer', 'exists:paragraph_templates,id'],
            'paragraph_content'     => ['nullable', 'string', 'max:5000'],

            // Optional: track why it was edited
            'edit_reason' => ['nullable', 'string', 'max:300'],
        ]);

        // ✅ highlight must be among tag_names if set
        if (!empty($validated['highlight_tag_name'])) {
            $tags = array_map(fn ($t) => strtolower(trim($t)), $validated['tag_names'] ?? []);
            $highlight = strtolower(trim($validated['highlight_tag_name']));

            if (!in_array($highlight, $tags, true)) {
                return back()
                    ->withErrors(['highlight_tag_name' => 'Highlight tag must be one of your selected tags.'])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($validated, $user, $post, $isOwner) {

            // Update post core fields
            $post->update([
                'forum_id' => $validated['forum_id'],
                'title'    => trim($validated['title']),
                'content'  => $validated['content'],
            ]);

            // ✅ tags (create if not exist)
            $tagIds = [];
            foreach (($validated['tag_names'] ?? []) as $name) {
                $cleanName = trim($name);
                if ($cleanName === '') continue;

                $tag = Tag::firstOrCreate(
                    ['slug' => Str::slug($cleanName)],
                    ['name' => $cleanName]
                );

                $tagIds[] = $tag->id;
            }

            $tagIds = array_values(array_unique($tagIds));
            $post->tags()->sync($tagIds);

            // ✅ highlight tag by NAME → ID
            $post->update(['highlight_tag_id' => null]);

            if (!empty($validated['highlight_tag_name'])) {
                $highlightSlug = Str::slug($validated['highlight_tag_name']);
                $highlightTagId = Tag::where('slug', $highlightSlug)->value('id');

                if ($highlightTagId && in_array($highlightTagId, $tagIds, true)) {
                    $post->update(['highlight_tag_id' => $highlightTagId]);
                }
            }

            // ✅ paragraph update (keep single row order=1)
            $existing = PostParagraph::where('post_id', $post->id)
                ->where('order', 1)
                ->first();

            if (!empty($validated['paragraph_template_id']) && !empty($validated['paragraph_content'])) {
                if ($existing) {
                    $existing->update([
                        'paragraph_id' => $validated['paragraph_template_id'],
                        'content'      => $validated['paragraph_content'],
                    ]);
                } else {
                    PostParagraph::create([
                        'post_id'      => $post->id,
                        'paragraph_id' => $validated['paragraph_template_id'],
                        'content'      => $validated['paragraph_content'],
                        'order'        => 1,
                    ]);
                }
            } else {
                if ($existing) $existing->delete();
            }

            // ✅ log edit (who edited + reason)
            PostEdit::create([
                'post_id'   => $post->id,
                'edited_by' => $user->id,
                'reason'    => $validated['edit_reason'] ?? null,
                'was_owner' => $isOwner ? 1 : 0,
            ]);
        });

        // ✅ Activity log (UserActivity)
        $this->logActivity(
            $request,
            $user->id,
            'post_updated',
            Post::class,
            $post->id,
            ['slug' => $post->slug]
        );

        return redirect()
            ->route('post.show', $post->slug)
            ->with('success', 'Post updated successfully!');
    }
}
