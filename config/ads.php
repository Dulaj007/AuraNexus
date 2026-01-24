<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Ad Placements Registry
    |--------------------------------------------------------------------------
    | Defines all allowed ad slots.
    | Admin UI will ONLY render inputs for keys listed here.
    |
    | "group" is used to render collapsible sections in the admin UI.
    */

    'placements' => [

        // ==========================================================
        // HEAD SCRIPTS (loaded in <head>)
        // ==========================================================
        'head_community' => [
            'group' => 'Head Scripts',
            'label' => 'Community Pages – Head Script',
            'desc'  => 'Injected into <head> for Categories + Forums (index/show). Use for ad network scripts.',
        ],

        'head_post' => [
            'group' => 'Head Scripts',
            'label' => 'Post Pages – Head Script',
            'desc'  => 'Injected into <head> for Post show pages only.',
        ],

        'head_profile' => [
            'group' => 'Head Scripts',
            'label' => 'Profile Pages – Head Script',
            'desc'  => 'Injected into <head> for Profile + Saved pages only.',
        ],

        // ✅ NEW: Search + Tags head script
        'head_search' => [
            'group' => 'Head Scripts',
            'label' => 'Search + Tags – Head Script',
            'desc'  => 'Injected into <head> for Search results + Tag pages. Use for ad network scripts.',
        ],

        // HEAD SCRIPTS (loaded in <head>)
        'head_link_unlock' => [
            'group' => 'Head Scripts',
            'label' => 'Link Unlock Page – Head Script',
            'desc'  => 'Injected into <head> for /link/{code} pages only. Use for ad network scripts / meta tags.',
        ],

        // ==========================================================
        // COMMUNITY PAGES (Categories + Forums: index/show)
        // Use these keys in BOTH category + forum blades.
        // ==========================================================
        'community_top_a' => [
            'group' => 'Community Pages',
            'label' => 'Top – Primary',
            'desc'  => 'Top of page. Visible on all devices.',
        ],
        'community_top_b' => [
            'group' => 'Community Pages',
            'label' => 'Top – Desktop Extra',
            'desc'  => 'Second top slot. Desktop only.',
        ],

        'community_mid_a' => [
            'group' => 'Community Pages',
            'label' => 'Middle – Primary',
            'desc'  => 'Between hero/header and content grid.',
        ],
        'community_mid_b' => [
            'group' => 'Community Pages',
            'label' => 'Middle – Desktop Extra',
            'desc'  => 'Extra middle slot. Desktop only.',
        ],

        'community_feed_a' => [
            'group' => 'Community Pages',
            'label' => 'In-Feed – Primary',
            'desc'  => 'Inserted inside grids/lists (e.g. every N cards).',
        ],
        'community_feed_b' => [
            'group' => 'Community Pages',
            'label' => 'In-Feed – Desktop Extra',
            'desc'  => 'Extra in-feed slot. Desktop only.',
        ],

        'community_bottom_a' => [
            'group' => 'Community Pages',
            'label' => 'Bottom – Primary',
            'desc'  => 'Bottom of page. Visible on all devices.',
        ],
        'community_bottom_b' => [
            'group' => 'Community Pages',
            'label' => 'Bottom – Desktop Extra',
            'desc'  => 'Second bottom slot. Desktop only.',
        ],

        // ==========================================================
        // POSTS (Post show page)
        // Unique set for post pages (NOT shared with community pages)
        // 1 ad for mobile, 2 ads for desktop per block
        // ==========================================================
        'post_show_top_a' => [
            'group' => 'Post Pages',
            'label' => 'Post – Top (Mobile)',
            'desc'  => 'Between navbar and post title. Mobile slot (shown on mobile only).',
        ],
        'post_show_top_b' => [
            'group' => 'Post Pages',
            'label' => 'Post – Top (Desktop 1)',
            'desc'  => 'Between navbar and post title. Desktop slot #1 (desktop only).',
        ],
        'post_show_top_c' => [
            'group' => 'Post Pages',
            'label' => 'Post – Top (Desktop 2)',
            'desc'  => 'Between navbar and post title. Desktop slot #2 (desktop only).',
        ],

        'post_show_mid1_a' => [
            'group' => 'Post Pages',
            'label' => 'Post – Mid 1 (Mobile)',
            'desc'  => 'After first action buttons (like/save/share) and before main content. Mobile slot.',
        ],
        'post_show_mid1_b' => [
            'group' => 'Post Pages',
            'label' => 'Post – Mid 1 (Desktop 1)',
            'desc'  => 'After first action buttons (like/save/share) and before main content. Desktop slot #1.',
        ],
        'post_show_mid1_c' => [
            'group' => 'Post Pages',
            'label' => 'Post – Mid 1 (Desktop 2)',
            'desc'  => 'After first action buttons (like/save/share) and before main content. Desktop slot #2.',
        ],

        'post_show_mid2_a' => [
            'group' => 'Post Pages',
            'label' => 'Post – Mid 2 (Mobile)',
            'desc'  => 'After second action buttons (report/edit/remove) and before comments box. Mobile slot.',
        ],
        'post_show_mid2_b' => [
            'group' => 'Post Pages',
            'label' => 'Post – Mid 2 (Desktop 1)',
            'desc'  => 'After second action buttons (report/edit/remove) and before comments box. Desktop slot #1.',
        ],
        'post_show_mid2_c' => [
            'group' => 'Post Pages',
            'label' => 'Post – Mid 2 (Desktop 2)',
            'desc'  => 'After second action buttons (report/edit/remove) and before comments box. Desktop slot #2.',
        ],

        'post_show_end_a' => [
            'group' => 'Post Pages',
            'label' => 'Post – End (Mobile)',
            'desc'  => 'After comments box and before footer. Mobile slot.',
        ],
        'post_show_end_b' => [
            'group' => 'Post Pages',
            'label' => 'Post – End (Desktop 1)',
            'desc'  => 'After comments box and before footer. Desktop slot #1.',
        ],
        'post_show_end_c' => [
            'group' => 'Post Pages',
            'label' => 'Post – End (Desktop 2)',
            'desc'  => 'After comments box and before footer. Desktop slot #2.',
        ],

        // ==========================================================
        // PROFILE (Profile page + Saved page) — shared placement set
        // ==========================================================
        'profile_top' => [
            'group' => 'Profile Pages',
            'label' => 'Profile – Top',
            'desc'  => 'At the very top of Profile + Saved pages (before everything).',
        ],
        'profile_mid' => [
            'group' => 'Profile Pages',
            'label' => 'Profile – Middle',
            'desc'  => 'After the profile header/card and before posts list (Profile page only).',
        ],
        'profile_bottom' => [
            'group' => 'Profile Pages',
            'label' => 'Profile – Bottom',
            'desc'  => 'Before footer at the end of Profile + Saved pages.',
        ],

        // ==========================================================
        // ✅ SEARCH + TAGS (shared placement set)
        // Used in:
        // - resources/views/search/index.blade.php
        // - resources/views/tags/show.blade.php
        // ==========================================================
        'search_top_a' => [
            'group' => 'Search + Tags Pages',
            'label' => 'Top – Primary',
            'desc'  => 'Top of page (before everything). Visible on all devices.',
        ],
        'search_top_b' => [
            'group' => 'Search + Tags Pages',
            'label' => 'Top – Desktop Extra',
            'desc'  => 'Second top slot. Desktop only.',
        ],

        'search_after_header_a' => [
            'group' => 'Search + Tags Pages',
            'label' => 'After Header – Primary',
            'desc'  => 'After Search box (Search page) / after Tag header (Tag page). Visible on all devices.',
        ],
        'search_after_header_b' => [
            'group' => 'Search + Tags Pages',
            'label' => 'After Header – Desktop Extra',
            'desc'  => 'Extra slot after header. Desktop only.',
        ],

        'search_after6_a' => [
            'group' => 'Search + Tags Pages',
            'label' => 'After 6 Results – Primary',
            'desc'  => 'Inserted right after the 6th result card. Visible on all devices.',
        ],
        'search_after6_b' => [
            'group' => 'Search + Tags Pages',
            'label' => 'After 6 Results – Desktop Extra',
            'desc'  => 'Second slot after 6th result. Desktop only.',
        ],

        'search_bottom_a' => [
            'group' => 'Search + Tags Pages',
            'label' => 'Bottom – Primary',
            'desc'  => 'Bottom of page (before footer). Visible on all devices.',
        ],
        'search_bottom_b' => [
            'group' => 'Search + Tags Pages',
            'label' => 'Bottom – Desktop Extra',
            'desc'  => 'Second bottom slot. Desktop only.',
        ],

        // ==========================================================
        // LINK UNLOCK / DOWNLOAD PAGE ( /link/{code} )
        // ==========================================================

        // PAGE SLOTS (4 ads: 2 top + 2 bottom)
        'link_unlock_top_a' => [
            'group' => 'Link Unlock Pages',
            'label' => 'Top – Primary',
            'desc'  => 'Top of unlock page. Visible on all devices.',
        ],
        'link_unlock_top_b' => [
            'group' => 'Link Unlock Pages',
            'label' => 'Top – Desktop Extra',
            'desc'  => 'Second top slot. Desktop only.',
        ],

        'link_unlock_bottom_a' => [
            'group' => 'Link Unlock Pages',
            'label' => 'Bottom – Primary',
            'desc'  => 'Bottom of unlock page. Visible on all devices.',
        ],
        'link_unlock_bottom_b' => [
            'group' => 'Link Unlock Pages',
            'label' => 'Bottom – Desktop Extra',
            'desc'  => 'Second bottom slot. Desktop only.',
        ],

    ],

];
