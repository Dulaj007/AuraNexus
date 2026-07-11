<?php

namespace App\Services;

class BbcodeImageParser
{
    /**
     * Convert legacy `[url=X][img]Y[/img][/url]` / standalone `[img]Y[/img]`
     * BBCode-style image tags (pasted straight into the editor without using
     * the "Image" toolbar button) into real <img>/<a> HTML so they render
     * as actual images instead of literal bracket text.
     *
     * Only http(s) URLs match (the scheme is baked into the pattern), and
     * values are escaped before being placed in an attribute, so this is
     * safe to run before Purifier — which still gets the final say on
     * what's actually allowed through.
     */
    public function parse(string $content): string
    {
        // [url=LINK][img]IMAGE[/img][/url] -> clickable image
        $content = preg_replace_callback(
            '/\[url=(https?:\/\/[^\]\s]+)\]\s*\[img\]\s*(https?:\/\/[^\]\s]+)\s*\[\/img\]\s*\[\/url\]/i',
            function (array $m): string {
                $url = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');
                $img = htmlspecialchars($m[2], ENT_QUOTES, 'UTF-8');

                return '<a href="'.$url.'" target="_blank" rel="noopener noreferrer">'
                     . '<img src="'.$img.'" alt="Image"></a>';
            },
            $content
        );

        // standalone [img]IMAGE[/img]
        $content = preg_replace_callback(
            '/\[img\]\s*(https?:\/\/[^\]\s]+)\s*\[\/img\]/i',
            function (array $m): string {
                $img = htmlspecialchars($m[1], ENT_QUOTES, 'UTF-8');

                return '<img src="'.$img.'" alt="Image">';
            },
            $content
        );

        return $content;
    }
}
