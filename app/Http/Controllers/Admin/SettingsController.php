<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::query()->pluck('value', 'key')->toArray();

        return view('admin.settings.index', [
            'settings' => $settings,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            // Identity
            'site_name'        => ['nullable', 'string', 'max:80'],
            'site_subtitle'    => ['nullable', 'string', 'max:120'],
            'site_description' => ['nullable', 'string', 'max:200'],

            // SEO
            'site_keywords'         => ['nullable', 'string', 'max:255'],
            'home_meta_title'       => ['nullable', 'string', 'max:80'],
            'home_meta_description' => ['nullable', 'string', 'max:200'],
            'meta_robots'           => ['nullable', 'string', 'max:200'],

            //social media links
            'site_twitter'  => ['nullable','url','max:255'],
            'site_facebook' => ['nullable','url','max:255'],
            'site_youtube'  => ['nullable','url','max:255'],

            // Policy
            'minimum_age' => ['nullable', 'integer', 'min:0', 'max:99'],

            // Footer links
            'footer_links'         => ['nullable', 'array'],
            'footer_links.*.label' => ['nullable', 'string', 'max:40'],
            'footer_links.*.href'  => ['nullable', 'string', 'max:500'],

            // Switches
            'registrations_open' => ['nullable', 'boolean'],

            // ✅ Link Unlock feature
            'link_unlock_enabled' => ['nullable', 'boolean'],
            'link_unlock_seconds' => ['nullable', 'integer', 'min:0', 'max:60'],

            // ✅ New: Ad redirect links (one per line)
            'link_unlock_ad_urls' => ['nullable', 'string', 'max:5000'],

            'quick_navigation_links'         => ['nullable', 'array'],
            'quick_navigation_links.*.title' => ['nullable', 'string', 'max:60'],
            'quick_navigation_links.*.url'   => ['nullable', 'string', 'max:500'],
            
        ]);

        // Normalize booleans (checkboxes)
        $data['registrations_open'] = $request->boolean('registrations_open');
        $data['link_unlock_enabled'] = $request->boolean('link_unlock_enabled');

        // ✅ Normalize / sanitize ad urls textarea -> JSON array OR null
        $adUrlsRaw = (string) $request->input('link_unlock_ad_urls', '');
        $adUrls = collect(preg_split("/\r\n|\n|\r/", $adUrlsRaw))
            ->map(fn ($v) => trim((string) $v))
            ->filter()
            ->map(function ($url) {
                // only http/https, block javascript:, data:, etc
                $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
                if (!in_array($scheme, ['http', 'https'], true)) return null;
                return $url;
            })
            ->filter()
            ->unique()
            ->values()
            ->take(50);

        $data['link_unlock_ad_urls'] = $adUrls->isEmpty()
            ? null
            : $adUrls->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // Footer links -> JSON string
        $links = collect($request->input('footer_links', []))
            ->map(function ($row) {
                $label = trim((string) ($row['label'] ?? ''));
                $href  = trim((string) ($row['href'] ?? ''));

                if ($label === '' || $href === '') return null;

                $okHref = Str::startsWith($href, ['/', 'http://', 'https://']);
                if (!$okHref) return null;

                return ['label' => $label, 'href' => $href];
            })
            ->filter()
            ->values()
            ->take(30);

        $footerLinksJson = $links->isEmpty()
            ? null
            : $links->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        unset($data['footer_links']);
        // Quick navigation links -> JSON
        $quickLinks = collect($request->input('quick_navigation_links', []))
            ->map(function ($row) {
                $title = trim((string) ($row['title'] ?? ''));
                $url   = trim((string) ($row['url'] ?? ''));

                if ($title === '' || $url === '') return null;

                $okUrl = Str::startsWith($url, ['/', 'http://', 'https://']);
                if (!$okUrl) return null;

                return ['title' => $title, 'url' => $url];
            })
            ->filter()
            ->values()
            ->take(30);

        $quickLinksJson = $quickLinks->isEmpty()
            ? null
            : $quickLinks->toJson(JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        unset($data['quick_navigation_links']);
        // Save scalar settings
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
                if ($value === '') $value = null;
            }

            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
        Setting::updateOrCreate(
            ['key' => 'quick_navigation_links'],
            ['value' => $quickLinksJson]
        );
        // Save footer_links separately
        Setting::updateOrCreate(
            ['key' => 'footer_links'],
            ['value' => $footerLinksJson]
        );

        Cache::forget('site.settings.public');
        if (class_exists(\App\Support\SiteSettings::class)) {
            \App\Support\SiteSettings::forgetPublic();
        }

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Settings updated.');
    }
}
