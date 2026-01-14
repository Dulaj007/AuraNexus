<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PagesController extends Controller
{
    private array $systemSlugs = ['terms', 'privacy', 'dmca', 'contact'];

    public function index()
    {
        $pages = Page::query()
            ->orderByRaw("FIELD(slug,'terms','privacy','dmca','contact') DESC")
            ->orderBy('title')
            ->paginate(20);

        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.pages.form', [
            'page' => new Page(['status' => Page::STATUS_PUBLISHED]),
            'isEdit' => false,
            'isSystem' => false,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:120'],
            'slug' => ['required','string','max:120','alpha_dash','unique:pages,slug'],
            'content' => ['nullable','string'],
            'status' => ['required','in:published,draft'],
        ]);

        $data['slug'] = Str::slug($data['slug']);
        $data['views'] = 0;

        Page::create($data);

        return redirect()->route('admin.pages.index')->with('success', 'Page created.');
    }

    public function edit(Page $page)
    {
        $isSystem = in_array($page->slug, $this->systemSlugs, true);

        return view('admin.pages.form', [
            'page' => $page,
            'isEdit' => true,
            'isSystem' => $isSystem,
        ]);
    }

    public function update(Request $request, Page $page)
    {
        $isSystem = in_array($page->slug, $this->systemSlugs, true);

        $rules = [
            'title' => ['required','string','max:120'],
            'content' => ['nullable','string'],
            'status' => ['required','in:published,draft'],
        ];

        // system pages: slug should not change
        if (!$isSystem) {
            $rules['slug'] = ['required','string','max:120','alpha_dash','unique:pages,slug,' . $page->id];
        }

        $data = $request->validate($rules);

        if (!$isSystem && isset($data['slug'])) {
            $data['slug'] = Str::slug($data['slug']);
        } else {
            unset($data['slug']);
        }

        $page->update($data);

        return redirect()->route('admin.pages.index')->with('success', 'Page updated.');
    }

    public function destroy(Page $page)
    {
        $isSystem = in_array($page->slug, $this->systemSlugs, true);

        if ($isSystem) {
            return back()->with('error', 'System pages cannot be deleted.');
        }

        $page->delete();

        return back()->with('success', 'Page deleted.');
    }

    /**
     * Ensure system pages exist (run once from admin menu button or call from seeder)
     */
    public function ensureSystemPages()
    {
        $defaults = [
            'terms' => 'Terms of Use',
            'privacy' => 'Privacy Policy',
            'dmca' => 'DMCA Notice',
            'contact' => 'Contact',
        ];

        foreach ($defaults as $slug => $title) {
            Page::firstOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => '', 'views' => 0, 'status' => Page::STATUS_PUBLISHED]
            );
        }

        return back()->with('success', 'System pages ensured.');
    }
}
