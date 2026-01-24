<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeTagCard;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HomeTagCardController extends Controller
{
    public function index()
    {
        $cards = HomeTagCard::query()
            ->with('tag:id,name,slug')
            ->orderByDesc('id')
            ->get();

        return view('admin.ui.home-tag-cards.index', compact('cards'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tag_name' => ['required', 'string', 'max:60'],
            'image'    => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $name = trim($data['tag_name']);
        $slug = Str::slug($name);

        // ✅ get or create tag
        $tag = Tag::firstOrCreate(
            ['slug' => $slug],
            ['name' => $name]
        );

        // ✅ store image
        $path = $request->file('image')->store('home/tag-cards', 'public');

        // ✅ create card (set image_path + enabled)
        HomeTagCard::create([
            'tag_id'     => $tag->id,
            'image_path' => $path,
            'sort_order' => (int) (HomeTagCard::max('sort_order') ?? 0) + 1,
            'is_enabled' => true,
        ]);

        return redirect()
            ->route('admin.ui.home-tag-cards.index')
            ->with('success', 'Home tag card added.');
    }

    public function destroy(HomeTagCard $card)
    {
        // optional: delete stored file
        if ($card->image_path) {
            Storage::disk('public')->delete($card->image_path);
        }

        $card->delete();

        return back()->with('success', 'Home tag card removed.');
    }

    public function toggle(HomeTagCard $card)
    {
        $card->update(['is_active' => ! (bool) $card->is_active]);

        Cache::forget('home.tag_cards.v1');

        return back()->with('success', 'Updated.');
    }

    public function updateOrder(Request $request, HomeTagCard $card)
    {
        $data = $request->validate([
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $card->update(['sort_order' => $data['sort_order']]);

        Cache::forget('home.tag_cards.v1');

        return back()->with('success', 'Order updated.');
    }


}
