{{-- resources/views/post/show.blade.php --}}
@extends('layouts.post')

@php
    use Illuminate\Support\Str;

    // ✅ Safety fallbacks (avoid undefined variable crashes)
    $post = $post ?? request()->route('post');

    $paragraph = $paragraph ?? null;

    $rendered = $rendered ?? [
        'plainText' => '',
        'images'    => [],
        'sections'  => [],
    ];

    $jsonLd = $jsonLd ?? [];

    $comments = $comments ?? collect();
    $pendingComments = $pendingComments ?? collect();

    $reactionCount = $reactionCount ?? ($post->reactions_count ?? 0);
    $userReacted = $userReacted ?? false;

    $isSaved = $isSaved ?? false;

    // Theme helpers (match categories/forums)
    $glass  = 'bg-[color:var(--an-card)]/72 backdrop-blur-xl border border-[var(--an-border)]';
    $shadow = 'shadow-[0_16px_55px_rgba(0,0,0,0.28)]';
@endphp

@section('title', ($post?->title ?? 'Post') . ' • ' . config('app.name'))

{{-- ✅ Feed meta into the NEW post layout sections --}}
@php
    $metaText = ($rendered['plainText'] ?? '');
    if (!empty($paragraph?->content)) {
        $metaText .= ' ' . $paragraph->content;
    }

    $desc = Str::limit(strip_tags($metaText), 160);
    $url = url()->current();
    // ✅ Prefer first image THUMB (img69) for OG/Twitter (direct asset URL)
    $firstPostImage = null;

    foreach (($rendered['sections'] ?? []) as $block) {
        if (($block['type'] ?? null) === 'image') {
            $thumb = trim((string)($block['thumb'] ?? ''));
            $full  = trim((string)($block['full'] ?? ''));

            // Prefer thumb if it's a direct http(s) URL (img69...)
            if ($thumb !== '' && Str::startsWith($thumb, ['http://','https://'])) {
                $firstPostImage = $thumb;
                break;
            }

            // Fallback to full if thumb missing
            if ($full !== '' && Str::startsWith($full, ['http://','https://'])) {
                $firstPostImage = $full;
                break;
            }
        }
    }

    $ogImage = $firstPostImage;
@endphp

@section('meta_title', $post?->title ?? 'Post')
@section('meta_description', $desc)
@section('meta_keywords', implode(', ', collect($post->tags)->pluck('name')->take(12)->all()))

@section('canonical', $url)
@section('og_type', 'article')
@if($ogImage)
    @section('og_image', $ogImage)
@endif

@section('json_ld')
{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
@endsection

@section('content')
@php
    $appName = config('app.name','AuraNexus');

    $category = $post->forum?->category;
    $forum = $post->forum;
    $author = $post->user;

    $isLoggedIn = auth()->check();

    // Permission flags
    $canApprove = $canApprove ?? (bool) session('can_approve_post', false);
    $canDeletePost = $isLoggedIn && auth()->user()->hasPermission('delete_post');

    // ✅ Edit permission (owner OR edit_post)
    $isOwner = $isLoggedIn && auth()->id() === $post->user_id;
    $canEditAny = $isLoggedIn && auth()->user()->hasPermission('edit_post');
    $canEditPost = $isOwner || $canEditAny;

    // Pending flag
    $isPending = $isPending ?? ($post->status !== 'published');

    // Reaction data passed by controller
    $reactionCount = $reactionCount ?? ($post->reactions_count ?? 0);
    $reactedByMe = $userReacted ?? false;

    // Report message (admin editable) passed by controller
    $reportMessage = $reportMessage ?? 'Please explain what is wrong with this post. Reports are reviewed by moderators.';

    $shareCount = $shareCount ?? 0;

    // UI helpers
    $pill = 'inline-flex items-center gap-1.5 px-2 py-1.5 rounded-full
             border border-[var(--an-border)] bg-[color:var(--an-card)]/60
             text-[11px] sm:text-xs text-[var(--an-text-muted)]';
    $pillStrong = 'font-semibold text-[var(--an-text)]';

    $btnBase = 'inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-sm font-semibold
                border transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)] active:scale-[0.98]';

    // ✅ Post ads (new helper-based setup)
    $adHtml = function (string $key) {
        if (function_exists('ad_html')) return ad_html($key);
        if (function_exists('ad')) return ad($key); // fallback if your helper name is "ad"
        return null;
    };

    $adTopM  = $adHtml('post_show_top_a');
    $adTopD1 = $adHtml('post_show_top_b');
    $adTopD2 = $adHtml('post_show_top_c');

    $adMid1M  = $adHtml('post_show_mid1_a');
    $adMid1D1 = $adHtml('post_show_mid1_b');
    $adMid1D2 = $adHtml('post_show_mid1_c');

    $adMid2M  = $adHtml('post_show_mid2_a');
    $adMid2D1 = $adHtml('post_show_mid2_b');
    $adMid2D2 = $adHtml('post_show_mid2_c');

    $adEndM  = $adHtml('post_show_end_a');
    $adEndD1 = $adHtml('post_show_end_b');
    $adEndD2 = $adHtml('post_show_end_c');
@endphp

<div class="max-w-7xl mx-auto  sm:py-6 space-y-1">

    {{-- ✅ Post Ads: TOP (between nav and title) --}}
    <div class="">
        {{-- Mobile (1) --}}
        @if($adTopM)
            <div class="block lg:hidden">
                <div class="flex justify-center">
                    {!! $adTopM !!}
                </div>
            </div>
        @endif

        {{-- Desktop (2) --}}
        @if($adTopD1 || $adTopD2)
            <div class="hidden  flex-row lg:flex justify-center">
                @if($adTopD1)
                    <div class="flex ">
                        {!! $adTopD1 !!}
                    </div>
                @endif
                @if($adTopD2)
                    <div class="flex ">
                        {!! $adTopD2 !!}
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Pending banner --}}
    @if($isPending)
        <x-post.card class="{{ $glass }} {{ $shadow }} rounded-3xl overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold" style="color: color-mix(in srgb, var(--an-text) 85%, #f59e0b);">
                        Pending Approval
                    </div>
                    <div class="text-xs text-[var(--an-text-muted)] mt-1">
                        This post is not published yet.
                        @if(!$canApprove)
                            Only moderators/admins can view full content until it’s approved.
                        @endif
                    </div>
                </div>

                @if($canApprove)
                    <form method="POST" action="{{ route('post.approve', $post) }}">
                        @csrf
                        <button class="{{ $btnBase }}"
                                style="border-color: color-mix(in srgb, #f59e0b 35%, var(--an-border));
                                       background: color-mix(in srgb, #f59e0b 18%, transparent);
                                       color: var(--an-text);">
                            Publish
                        </button>
                    </form>
                @endif
            </div>
        </x-post.card>
    @endif

    <div class=" space-y-1 ">
      
        {{-- Breadcrumb --}}
        <div class=" py-2">
            <x-ui.breadcrumb
                :items="[
                    ['label' => $category->name ?? 'Category', 'url' => $category ? route('categories.show',$category) : '#'],
                    ['label' => $forum->name ?? 'Forum', 'url' => $forum ? route('forums.show',$forum) : '#'],
                ]"
                :current="$post->title"
            />
        </div>
        {{-- HERO --}}
        <x-ui.forum-hero
            :title="$post->title"
            :description="$forum?->name ?? 'Post'"
            :posts-total="$post->views ?? 0"
            :base-path="url()->current()"
            :show-sort="false"
            type="views"
        />

      
                {{-- Tags --}}
                <div class="flex flex-wrap gap-2 mx-2  py-3">
                    @if($post->highlightTag)
                        <x-post.tag :href="route('tags.show', $post->highlightTag->slug) ?? '#'" variant="highlight" >
                            {{ $post->highlightTag->name }}
                        </x-post.tag>
                    @endif

                    @foreach($post->tags as $t)
                        @continue($post->highlightTag && $t->id === $post->highlightTag->id)
                        <x-post.tag :href="route('tags.show', $t->slug) ?? '#'">
                            {{ $t->name }}
                        </x-post.tag>
                    @endforeach
                </div>

         
        </div>

       
  

    {{-- ✅ Post Ads: AFTER FIRST ACTION BUTTONS (between like/save/share and content) --}}
    <div class="px-3">
        {{-- Mobile (1) --}}
        @if($adMid1M)
            <div class="block lg:hidden">
                <div class="flex justify-center">
                    {!! $adMid1M !!}
                </div>
            </div>
        @endif

        {{-- Desktop (2) --}}
        @if($adMid1D1 || $adMid1D2)
            <div class="hidden  flex-row lg:flex justify-center">
                @if($adMid1D1)
                    <div class="flex ">
                        {!! $adMid1D1 !!}
                    </div>
                @endif
                @if($adMid1D2)
                    <div class="flex ">
                        {!! $adMid1D2 !!}
                    </div>
                @endif
            </div>
        @endif
    </div>

    {{-- Content (only show if published OR approver) --}}
    @if(!$isPending || $canApprove)

    <x-post.card class="{{ $glass }} {{ $shadow }} overflow-hidden {{ $isPending ? 'opacity-90' : '' }}">
        <div class="prose prose-invert max-w-none">
            @php
                $imgIndex = 0;
                $linksShown = false;
                $imgHintShown = false;

                // Needed for heading section detection
                $currentSection = null;
            @endphp

            @foreach(($rendered['sections'] ?? []) as $block)

                @if(($block['type'] ?? null) === 'heading')

                @php
                    $heading = trim($block['text'] ?? '');
                    $headingLower = strtolower($heading);
                @endphp

                @if($headingLower === 'download links')
                    @php $currentSection = 'download'; @endphp
                    <h2 class="pt-3 pl-3 font-semibold">Download Links</h2>

                @elseif($headingLower === 'watch online')
                    @php $currentSection = 'watch'; @endphp
                    <h2 class="pt-3 pl-3 font-semibold">Watch Online Links</h2>

                @elseif($headingLower === 'credits')
                    @php $currentSection = 'credits'; @endphp
                    <h2 class="pt-3 pl-3 font-semibold">Credits</h2>

                @else
                    @php $currentSection = null; @endphp
                    <h3 class="mx-2">{{ $heading }}</h3>
                @endif


            @elseif(($block['type'] ?? null) === 'link')

                @php
                    $url = $block['url'] ?? '';
                    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');

                    $icon = null;

                    if(str_contains($host,'facebook')){
                        $icon='facebook';
                    }elseif(str_contains($host,'instagram')){
                        $icon='instagram';
                    }elseif(str_contains($host,'twitter') || str_contains($host,'x.com')){
                        $icon='twitter';
                    }elseif(str_contains($host,'linkedin')){
                        $icon='linkedin';
                    }
                @endphp


                {{-- Credits section buttons --}}
                @if(($currentSection ?? null) === 'credits')

                    <div class="not-prose my-2 mx-2 flex flex-wrap gap-2">

                        <a href="{{ $url }}"
                        target="_blank"
                        rel="nofollow noopener"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl
                                border border-[var(--an-border)]
                                bg-[color:var(--an-card)]/60
                                text-sm font-semibold
                                hover:bg-[color:var(--an-card)]/80
                                transition">

                            {{-- Facebook --}}
                            @if($icon === 'facebook')
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M22 12a10 10 0 1 0-11.5 9.9v-7h-2.1v-2.9h2.1V9.7c0-2.1 1.2-3.3 3.2-3.3.9 0 1.8.1 1.8.1v2h-1c-1 0-1.3.6-1.3 1.3v1.6h2.6l-.4 2.9h-2.2v7A10 10 0 0 0 22 12"/>
                            </svg>
                            @endif

                            {{-- Instagram --}}
                            @if($icon === 'instagram')
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 2C4.2 2 2 4.2 2 7v10c0 2.8 2.2 5 5 5h10c2.8 0 5-2.2 5-5V7c0-2.8-2.2-5-5-5H7zm5 5a5 5 0 1 1 0 10 5 5 0 0 1 0-10zm6.5-.9a1.4 1.4 0 1 1-2.8 0 1.4 1.4 0 0 1 2.8 0z"/>
                            </svg>
                            @endif

                            {{-- Twitter / X --}}
                            @if($icon === 'twitter')
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M18.9 2H22l-7.5 8.6L23 22h-6.7l-5.3-7-6.1 7H2l8-9.1L1 2h6.8l4.8 6.4L18.9 2z"/>
                            </svg>
                            @endif

                            {{-- LinkedIn --}}
                            @if($icon === 'linkedin')
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M20.4 20.4h-3.6v-5.6c0-1.3-.5-2.2-1.7-2.2-.9 0-1.4.6-1.6 1.1-.1.2-.1.5-.1.8v5.9H9.8s.1-9.5 0-10.5h3.6v1.5c.5-.8 1.3-1.9 3.2-1.9 2.3 0 4 1.5 4 4.7v6.2zM5.3 8.3a2.1 2.1 0 1 1 0-4.3 2.1 2.1 0 0 1 0 4.3zM7.1 20.4H3.5V9.9h3.6v10.5z"/>
                            </svg>
                            @endif

                            <span>
                                {{ $block['label'] ?? parse_url($url, PHP_URL_HOST) }}
                            </span>

                        </a>

                    </div>


                {{-- normal links (existing system unchanged) --}}
                @else

                    <div class="not-prose my-2 mx-2">
                        <x-post.link-card
                            :url="$block['gate_url'] ?? $block['url']"
                            :label="$block['label'] ?? null"
                            :display="$block['display'] ?? null"
                        />
                    </div>

                @endif

                @elseif(($block['type'] ?? null) === 'image')
                    {{-- Show this once: after links and before first image --}}
                    @if($linksShown && !$imgHintShown)
                        @php $imgHintShown = true; @endphp
                        <div class="text-xs text-[var(--an-text-muted)] mt-4 mx-[3vh]">
                            Click image to view in high quality
                        </div>
                    @endif

                    @php
                        $imgIndex++;

                        $src = (string)($block['thumb'] ?? $block['full'] ?? '');
                        $path = parse_url($src, PHP_URL_PATH) ?? '';
                        $isGif = \Illuminate\Support\Str::endsWith(strtolower($path), '.gif');

                        $imgWidthClass = $isGif ? 'w-[15vh]' : 'w-full';
                    @endphp

                    <div class="not-prose my-4 mx-[3vh]">
                        <a href="{{ $block['full'] }}" target="_blank" rel="nofollow noopener">
                            <img
                                src="{{ $block['thumb'] }}"
                                alt="{{ $post->title }} - Image {{ $imgIndex }}"
                                class="{{ $imgWidthClass }} max-w-3xl rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40"
                                loading="lazy"
                            >
                        </a>
                    </div>

                @elseif(($block['type'] ?? null) === 'text')
                    <p class="mx-2 text-sm">{{ $block['text'] }}</p>
                @endif

            @endforeach
        </div>

        {{-- Extra paragraph saved (SEO-friendly) --}}
        @if($paragraph && $paragraph->content)
            <div class="mt-6 mx-2 text-sm border-t border-[var(--an-border)] pt-4 text-[var(--an-text)]/80">
                <p>{{ $paragraph->content }}</p>
            </div>
        @endif
    </x-post.card>

{{-- 🔷 Unified Header + Actions Container --}}
<div class="w-full {{ $glass }} {{ $shadow }}  overflow-hidden p-3 md:p-4 flex flex-col gap-4">

    {{-- 🔹 Top Section (Author + Reactions) --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">

        {{-- 👤 Author --}}
        <div class="flex items-center gap-3 min-w-0">

            @php
                $authorAvatarUrl = null;

                if ($author?->avatar) {
                    $authorAvatarUrl = \Illuminate\Support\Str::startsWith($author->avatar, ['http://','https://'])
                        ? $author->avatar
                        : asset('storage/' . ltrim($author->avatar, '/'));
                }
            @endphp

            <a href="{{ route('profile.show', $author->username) }}"
               class="flex items-center gap-3 min-w-0 hover:opacity-90 transition"
               aria-label="View profile: {{ $author?->username ?? 'Member' }}">

                <div class="h-12 w-12 md:h-14 md:w-14 rounded-full overflow-hidden border border-[var(--an-border)] bg-[color:var(--an-card)]/60">
                    @if($authorAvatarUrl)
                        <img src="{{ $authorAvatarUrl }}"
                             alt="{{ $author->username }} avatar"
                             class="h-full w-full object-cover"
                             loading="lazy">
                    @else
                        <div class="h-full w-full flex items-center justify-center text-xs text-[var(--an-text-muted)]">
                            {{ strtoupper(substr($author?->username ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div class="min-w-0">
                    <div class="text-sm md:text-base font-semibold text-[var(--an-text)] truncate">
                        {{ $author?->name ?? $author?->username ?? 'Member' }}
                    </div>
                    <div class="text-xs text-[var(--an-text-muted)]">
                        {{ optional($post->created_at)->diffForHumans() }}
                    </div>
                </div>

            </a>
        </div>

        {{-- ❤️ 💾 🔗 Actions --}}
        <div class="flex items-center justify-end  flex-wrap">
        <div>
            <x-post.report-button
                :post="$post"
                :message="$reportMessage"
                class="{{ $btnBase }}"
                title="Report"
                aria-label="Report" />
        </div>

        {{-- ✏️ 🗑️ --}}
        <div class="flex items-center gap-1 flex-wrap">

            @if($canEditPost)
                <a href="{{ route('post.edit', $post->slug) }}"
                   class=" !px-3 !py-2"
                   title="Edit"
                   aria-label="Edit"
                   style="
                   
                       color: var(--an-link);
                
                   ">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <path d="M20,16v4a2,2,0,0,1-2,2H4a2,2,0,0,1-2-2V6A2,2,0,0,1,4,4H8"
                              stroke="currentColor" stroke-width="2"/>
                        <polygon points="12.5 15.8 22 6.2 17.8 2 8.3 11.5 8 16 12.5 15.8"
                                 stroke="currentColor" stroke-width="2" fill="none"/>
                    </svg>
                </a>
            @endif

            @if($canDeletePost)
                <button type="button"
                        class=" !px-3 !py-2"
                        title="Remove"
                        aria-label="Remove"
                        style="
                          
                            color: var(--an-danger);
                           
                        "
                        onclick="openRemovePostModal()">
                    <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none">
                        <path d="M10 12V17" stroke="currentColor" stroke-width="2"/>
                        <path d="M14 12V17" stroke="currentColor" stroke-width="2"/>
                        <path d="M4 7H20" stroke="currentColor" stroke-width="2"/>
                        <path d="M6 10V18C6 19.6569 7.34315 21 9 21H15C16.6569 21 18 19.6569 18 18V10"
                              stroke="currentColor" stroke-width="2"/>
                        <path d="M9 5C9 3.89543 9.89543 3 11 3H13C14.1046 3 15 3.89543 15 5V7H9V5Z"
                              stroke="currentColor" stroke-width="2"/>
                    </svg>
                </button>
            @endif

        </div>
            {{-- ❤️ Like --}}
            <x-post.reaction-button
                :post="$post"
                :count="$reactionCount"
                :reacted="$userReacted"
                :can-react="$isLoggedIn"
            />

            {{-- 💾 Save --}}
            @if($isLoggedIn)
                <form method="POST" action="{{ route('post.save.toggle', $post) }}">
                    @csrf
                    <button type="submit"
                            class="!px-3 !py-2"
                            title="{{ $isSaved ? 'Saved' : 'Save' }}"
                            aria-label="{{ $isSaved ? 'Saved' : 'Save' }}"
                            style="
                               
                                color: {{ $isSaved ? 'var(--an-success)' : 'var(--an-info)' }};
                              
                            ">
                        <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none">
                            <path fill-rule="evenodd" clip-rule="evenodd"
                                d="M6.75 6L7.5 5.25H16.5L17.25 6V19.3162L12 16.2051L6.75 19.3162V6ZM8.25 6.75V16.6838L12 14.4615L15.75 16.6838V6.75H8.25Z"
                                fill="currentColor"/>
                        </svg>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}"
                   class=" !px-3 !py-2"
                   title="Save"
                   aria-label="Save"
                   style="
                       
                        color: var(--an-info);
                       
                   ">
                    <svg class="w-7 h-7" viewBox="0 0 24 24" fill="none">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M6.75 6L7.5 5.25H16.5L17.25 6V19.3162L12 16.2051L6.75 19.3162V6ZM8.25 6.75V16.6838L12 14.4615L15.75 16.6838V6.75H8.25Z"
                            fill="currentColor"/>
                    </svg>
                </a>
            @endif

            {{-- 🔗 Share --}}
            <button type="button"
                    class=" !px-3 !py-2"
                    title="Share"
                    aria-label="Share"
                    style="
                     
                        color: var(--an-primary);
                     
                    "
                    onclick="sharePost()">
                <svg class="w-5 h-5" viewBox="0 0 512 512">
                    <path d="M512,230.431L283.498,44.621v94.807C60.776,141.244-21.842,307.324,4.826,467.379
                            c48.696-99.493,149.915-138.677,278.672-143.14v92.003L512,230.431z"
                        fill="currentColor"/>
                </svg>
            </button>

        </div>
    </div>



</div>

        {{-- ✅ Post Ads: AFTER REPORT/EDIT/REMOVE (before comments box) --}}
        <div class="px-3">
            {{-- Mobile (1) --}}
            @if($adMid2M)
                <div class="block lg:hidden">
                    <div class="flex justify-center">
                        {!! $adMid2M !!}
                    </div>
                </div>
            @endif

            {{-- Desktop (2) --}}
            @if($adMid2D1 || $adMid2D2)
                 <div class="hidden  flex-row lg:flex justify-center">
                    @if($adMid2D1)
                        <div class="flex ">
                            {!! $adMid2D1 !!}
                        </div>
                    @endif
                    @if($adMid2D2)
                        <div class="flex">
                            {!! $adMid2D2 !!}
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <x-post.related-posts :posts="$relatedPosts" />

                {{-- Comments --}}
                <x-post.card class="{{ $glass }} {{ $shadow }} overflow-hidden {{ $isPending ? 'opacity-90' : '' }}">
                    <x-post.comments
                        :post="$post"
                        :post-id="$post->id"
                        :is-logged-in="$isLoggedIn"
                        :can-approve="$canApprove"
                        :can-delete="$canDeletePost"
                        :comments="$comments"
                        :pending-comments="$pendingComments"
                    />
                </x-post.card>

                {{-- ✅ Post Ads: END (after comments, before footer) --}}
                <div class="px-3">
                    {{-- Mobile (1) --}}
                    @if($adEndM)
                        <div class="block lg:hidden">
                            <div class="flex justify-center">
                                {!! $adEndM !!}
                            </div>
                        </div>
                    @endif

                    {{-- Desktop (2) --}}
                    @if($adEndD1 || $adEndD2)
                        <div class="hidden  flex-row lg:flex justify-center">
                            @if($adEndD1)
                                <div class="flex ">
                                    {!! $adEndD1 !!}
                                </div>
                            @endif
                            @if($adEndD2)
                                <div class="flex ">
                                    {!! $adEndD2 !!}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

            @else
                {{-- Not allowed to see content --}}
                <x-post.card class="{{ $glass }} {{ $shadow }} rounded-3xl overflow-hidden opacity-90">
                    <div class="text-sm text-[var(--an-text-muted)]">
                        This post is currently pending approval.
                    </div>
                </x-post.card>
            @endif

        </div>

<script>
async function sharePost() {
    const url = @json(url()->current());
    const title = @json($post->title);

    if (navigator.share) {
        try {
            await navigator.share({ title, url });
            await trackShare('native');
            return;
        } catch (e) {}
    }

    try {
        await navigator.clipboard.writeText(url);
        alert('Link copied!');
        await trackShare('copy');
    } catch (e) {
        prompt('Copy this link:', url);
        await trackShare('copy_prompt');
    }
}

async function trackShare(channel) {
    try {
        await fetch(@json(route('post.share', $post)), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': @json(csrf_token()),
            },
            body: JSON.stringify({ channel }),
        });
    } catch (e) {}
}
</script>

{{-- ✅ Remove Post Modal --}}
@if($canDeletePost)
<div id="removePostModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/70 p-4">
    <div class="w-full max-w-lg rounded-3xl border border-[var(--an-border)] bg-[color:var(--an-card)]/90 backdrop-blur-xl p-5">
        <div class="flex items-start justify-between gap-4">
            <div>
                <div class="text-sm font-semibold text-[var(--an-text)]">Remove Post</div>
                <div class="text-xs text-[var(--an-text-muted)] mt-1">
                    Add a reason. This will permanently remove the post from public view.
                </div>
            </div>
            <button type="button" class="text-[var(--an-text-muted)] hover:text-[var(--an-text)]" onclick="closeRemovePostModal()">✕</button>
        </div>

        <form method="POST" action="{{ route('post.remove', $post) }}" class="mt-4 space-y-3">
            @csrf

            <textarea
                name="reason"
                required
                minlength="3"
                maxlength="500"
                rows="4"
                class="w-full rounded-2xl bg-[color:var(--an-bg)]/40 border border-[var(--an-border)]
                       p-3 text-sm text-[var(--an-text)]
                       focus:outline-none focus:ring-2 focus:ring-[var(--an-danger)]/30"
                placeholder="Reason for removal..."
            ></textarea>

            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-2xl border border-[var(--an-border)] px-4 py-2 text-sm
                           text-[var(--an-text)]/80 hover:bg-[color:var(--an-card)]/60"
                    onclick="closeRemovePostModal()"
                >
                    Cancel
                </button>
                <button type="submit"
                        class="rounded-2xl px-4 py-2 text-sm font-semibold"
                        style="background: color-mix(in srgb, var(--an-danger) 22%, transparent);
                               border: 1px solid color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                               color: var(--an-text);">
                    Remove
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openRemovePostModal() {
    const m = document.getElementById('removePostModal');
    m.classList.remove('hidden');
    m.classList.add('flex');
}
function closeRemovePostModal() {
    const m = document.getElementById('removePostModal');
    m.classList.add('hidden');
    m.classList.remove('flex');
}
</script>
@endif
@endsection
