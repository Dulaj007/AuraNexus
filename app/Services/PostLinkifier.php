<?php

namespace App\Services;

use App\Models\PostLink;

class PostLinkifier
{
    public function linkifySections(int $postId, array $rendered): array
    {
        $sections = $rendered['sections'] ?? [];
        if (!is_array($sections)) return $rendered;

        // original_url is an `encrypted` cast, so its ciphertext changes on
        // every save — matching it in a WHERE clause never hits, so the
        // post's existing links have to be loaded and compared in PHP instead.
        $existingLinks = PostLink::where('post_id', $postId)->get();

        foreach ($sections as $i => $block) {
            if (($block['type'] ?? null) !== 'link') continue;

            $url = trim((string)($block['url'] ?? ''));
            if ($url === '') continue;

            $postLink = $existingLinks->first(fn (PostLink $link) => $link->original_url === $url);

            if (!$postLink) {
                $postLink = PostLink::create([
                    'post_id'      => $postId,
                    'original_url' => $url,
                    'is_enabled'   => true, // code is generated automatically on create
                ]);

                $existingLinks->push($postLink);
            }

            $host = parse_url($url, PHP_URL_HOST) ?: 'link';

            $sections[$i]['gate_url'] = route('link.show', $postLink->code);
            $sections[$i]['display']  = $host . '/…';
        }

        $rendered['sections'] = $sections;
        return $rendered;
    }
}
