<?php

namespace App\Services;

use App\Models\PostLink;

class PostLinkifier
{
    public function linkifySections(int $postId, array $rendered): array
    {
        $sections = $rendered['sections'] ?? [];
        if (!is_array($sections)) return $rendered;

        foreach ($sections as $i => $block) {
            if (($block['type'] ?? null) !== 'link') continue;

            $url = trim((string)($block['url'] ?? ''));
            if ($url === '') continue;

            // create or reuse code for this post+url
            $postLink = PostLink::updateOrCreate(
                ['post_id' => $postId, 'original_url' => $url],
                ['is_enabled' => true] // code is generated automatically if creating
            );


            $host = parse_url($url, PHP_URL_HOST) ?: 'link';

            $sections[$i]['gate_url'] = route('link.show', $postLink->code); // adjust route name
            $sections[$i]['display']  = $host . '/…';
        }

        $rendered['sections'] = $sections;
        return $rendered;
    }
}
