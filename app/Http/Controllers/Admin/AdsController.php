<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdPlacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdsController extends Controller
{
    /**
     * Show ads management page
     */
    public function index()
    {
        // Allowed placements from config
        $placements = config('ads.placements');

        // Existing ads from DB indexed by key
        $ads = AdPlacement::query()
            ->whereIn('key', array_keys($placements))
            ->get()
            ->keyBy('key');

        return view('admin.ads.index', [
            'placements' => $placements,
            'ads'        => $ads,
        ]);
    }

    /**
     * Save / update all ad placements
     */
    public function update(Request $request)
    {
        $placements = config('ads.placements');
        $inputAds   = $request->input('ads', []);

        foreach ($placements as $key => $meta) {
            $data = $inputAds[$key] ?? [];

            AdPlacement::updateOrCreate(
                ['key' => $key],
                [
                    'label'       => $meta['label'],
                    'description' => $meta['desc'] ?? null,
                    'html'        => $data['html'] ?? null,
                    'is_enabled'  => isset($data['is_enabled']),
                ]
            );
        }

        // Clear ads cache so frontend updates instantly
        Cache::forget('ads.placements');

        return redirect()
            ->back()
            ->with('success', 'Ads updated successfully.');
    }
}
