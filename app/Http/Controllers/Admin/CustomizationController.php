<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Forum;
use App\Models\ParagraphTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomizationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Allow admin OR a permission (change permission name to your real one)
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) {
            abort(403);
        }

        $categories = Category::with(['forums' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get();

        $paragraphTemplates = ParagraphTemplate::orderBy('category')
            ->orderByDesc('id')
            ->get();

        // ✅ new structure view
        return view('admin.customization.index', compact('categories', 'paragraphTemplates'));
    }

    /* ---------------- CATEGORIES ---------------- */

    public function storeCategory(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) abort(403);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($data) {
            $slug = $this->uniqueSlug(Category::class, $data['name']);

            Category::create([
                'name'        => $data['name'],
                'slug'        => $slug,
                'description' => $data['description'] ?? null,
                'views'       => 0,
            ]);
        });

        return back()->with('success', 'Category created.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) abort(403);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:255'],
            // optional: allow manual slug updates
            'slug'        => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($category, $data) {

            // Safer for production: keep old slug unless admin explicitly changes it
            $newSlug = $category->slug;

            if (!empty($data['slug'])) {
                $newSlug = $this->uniqueSlug(Category::class, $data['slug'], $category->id);
            }

            $category->update([
                'name'        => $data['name'],
                'slug'        => $newSlug,
                'description' => $data['description'] ?? null,
            ]);
        });

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Request $request, Category $category)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) abort(403);

        if ($category->forums()->exists()) {
            return back()->withErrors([
                'admin' => 'You must delete/move forums in this category before deleting it.',
            ]);
        }

        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    /* ---------------- FORUMS ---------------- */

    public function storeForum(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) abort(403);

        $data = $request->validate([
            'category_id'  => ['required', 'exists:categories,id'],
            'name'         => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:255'],
        ]);

        // Recommended: unique forum name inside the same category
        $exists = Forum::where('category_id', $data['category_id'])
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A forum with this name already exists in this category.'])->withInput();
        }

        DB::transaction(function () use ($data) {
            $slug = $this->uniqueSlug(Forum::class, $data['name']);

            Forum::create([
                'category_id'  => $data['category_id'],
                'name'         => $data['name'],
                'slug'         => $slug,
                'description'  => $data['description'] ?? null,
                'views'        => 0,
            ]);
        });

        return back()->with('success', 'Forum created.');
    }

    public function updateForum(Request $request, Forum $forum)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) abort(403);

        $data = $request->validate([
            'category_id'  => ['required', 'exists:categories,id'],
            'name'         => ['required', 'string', 'max:100'],
            'description'  => ['nullable', 'string', 'max:255'],
            'slug'         => ['nullable', 'string', 'max:120'],
        ]);

        // Unique in category (excluding current forum)
        $exists = Forum::where('category_id', $data['category_id'])
            ->where('name', $data['name'])
            ->where('id', '!=', $forum->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A forum with this name already exists in this category.'])->withInput();
        }

        DB::transaction(function () use ($forum, $data) {
            $newSlug = $forum->slug;

            // keep slug stable unless admin sets it
            if (!empty($data['slug'])) {
                $newSlug = $this->uniqueSlug(Forum::class, $data['slug'], $forum->id);
            }

            $forum->update([
                'category_id'  => $data['category_id'],
                'name'         => $data['name'],
                'slug'         => $newSlug,
                'description'  => $data['description'] ?? null,
            ]);
        });

        return back()->with('success', 'Forum updated.');
    }

    public function destroyForum(Request $request, Forum $forum)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) abort(403);

        // Optional: if you want to block deleting forums that contain posts, add a check here
        // if ($forum->posts()->exists()) { ... }

        $forum->delete();

        return back()->with('success', 'Forum deleted.');
    }

    /* ---------------- PARAGRAPH TEMPLATES ---------------- */

    public function storeParagraphTemplate(Request $request)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) abort(403);

        $data = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'content'  => ['required', 'string', 'max:5000'],
        ]);

        ParagraphTemplate::create($data);

        return back()->with('success', 'Paragraph template added.');
    }

    public function updateParagraphTemplate(Request $request, ParagraphTemplate $paragraph_template)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) abort(403);

        $data = $request->validate([
            'category' => ['required', 'string', 'max:100'],
            'content'  => ['required', 'string', 'max:5000'],
        ]);

        $paragraph_template->update($data);

        return back()->with('success', 'Paragraph template updated.');
    }

    public function destroyParagraphTemplate(Request $request, ParagraphTemplate $paragraph_template)
    {
        $user = $request->user();
        if (!$user || (!$user->hasRole('admin') && !$user->hasPermission('login_admin_panel'))) abort(403);

        $paragraph_template->delete();

        return back()->with('success', 'Paragraph template deleted.');
    }

    /* ---------------- HELPERS ---------------- */

    /**
     * Build a unique slug for a given model (Category/Forum).
     * $ignoreId: exclude record when updating.
     */
    private function uniqueSlug(string $modelClass, string $base, ?int $ignoreId = null): string
    {
        $slug = Str::slug($base);
        $slug = $slug !== '' ? $slug : 'item';

        $query = $modelClass::query()->where('slug', $slug);
        if ($ignoreId) $query->where('id', '!=', $ignoreId);

        $exists = $query->exists();
        if (!$exists) return $slug;

        $i = 2;
        while (true) {
            $candidate = "{$slug}-{$i}";
            $q = $modelClass::query()->where('slug', $candidate);
            if ($ignoreId) $q->where('id', '!=', $ignoreId);

            if (!$q->exists()) return $candidate;
            $i++;
        }
    }
}
