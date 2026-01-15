<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Page;

class PagesController extends Controller
{
    public function show(Page $page)
    {
        // drafts should not be visible publicly
        if ($page->status !== Page::STATUS_PUBLISHED) {
            abort(404);
        }

        // optional: count views
        $page->increment('views');

        return view('pages.show', compact('page'));
    }
}
