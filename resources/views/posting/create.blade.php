{{-- C:\xampp\htdocs\AuraNexus\resources\views\posting\create.blade.php --}}
@extends('layouts.posting')

@php
    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit';

    $formAction = $isEdit
        ? route('post.update', $post->slug)
        : route('posting.store');

    $submitText = $isEdit ? 'Update' : 'Publish';

    // Prefill helpers (passed by controller in edit mode)
    $existingTagNames = $existingTagNames ?? [];
    $existingHighlightName = $existingHighlightName ?? '';
    $existingParagraph = $existingParagraph ?? null;

    // Theme helpers
    $glass  = 'rounded-3xl mx-2 border border-[var(--an-border)] bg-[color:var(--an-card)]/70 backdrop-blur-xl';
    $shadow = 'shadow-[0_16px_55px_rgba(0,0,0,0.28)]';

    $label = 'text-sm font-semibold text-[var(--an-text)]';
    $hint  = 'text-xs text-[var(--an-text-muted)] mt-1';

    $inputBase = 'w-full rounded-2xl border border-[var(--an-border)] bg-[color:var(--an-bg)]/35
                  px-4 py-3 text-sm text-[var(--an-text)]
                  outline-none focus:ring-2 focus:ring-[var(--an-ring)]/60 focus:border-[var(--an-border)]
                  placeholder:text-[var(--an-text-muted)]';

    $btnPrimary = 'inline-flex items-center justify-center gap-2 rounded-2xl px-5 py-3 text-sm font-extrabold
                   border transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]
                   active:scale-[0.98]';
@endphp

@section('title', $isEdit ? 'Update Post' : 'Create a Post')

@section('content')
{{-- Display general validation errors --}}
@if ($errors->any())
    <div class="mb-4 rounded-2xl border px-4 py-3 text-sm"
         style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                background: color-mix(in srgb, var(--an-danger) 12%, transparent);
                color: color-mix(in srgb, var(--an-text) 85%, var(--an-danger));">
        <ul class="list-disc pl-5 space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<form id="postForm" action="{{ $formAction }}" method="POST" class="space-y-4 sm:space-y-6">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    {{-- Header --}}
    <div class="px-3 sm:px-0">
        <div class="flex flex-col gap-1">
            <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight text-[var(--an-text)]">
                {{ $isEdit ? 'Update Post' : 'Create a Post' }}
            </h1>
            <p class="text-sm text-[var(--an-text-muted)]">
                Read posting rules before posting. Any rule breaking can end up perminant bann of your account 
            </p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6">

        {{-- Left (main) --}}
        <div class="lg:col-span-2 space-y-4 sm:space-y-6">

            {{-- Basic info --}}
            <div class="{{ $glass }} {{ $shadow }} overflow-hidden">
                <div class="p-4 sm:p-6 space-y-3">
                    <div>
                        <div class="{{ $label }}">Title</div>
                        <div class="{{ $hint }}">Keep it short and clear.</div>

                        <input
                            name="title"
                            class="{{ $inputBase }} mt-3"
                            placeholder="Type a clear title..."
                            value="{{ old('title', $isEdit ? ($post->title ?? '') : '') }}"
                        />
                    </div>

                    <div>
                        <div class="{{ $label }}">Forum</div>
                        <div class="{{ $hint }}">Choose where this post belongs.</div>

                        <select name="forum_id" class="{{ $inputBase }} mt-3">
                            <option value="">Select a forum</option>
                            @foreach($forums as $forum)
                                <option
                                    value="{{ $forum->id }}"
                                    @selected(old('forum_id', $isEdit ? ($post->forum_id ?? '') : '') == $forum->id)
                                >
                                    {{ $forum->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-4">
                        <div class="{{ $label }}">Model / Actress name (optional)</div>
                        <div class="{{ $hint }}">Leave empty if unknown.</div>

                        <input
                            name="model_name"
                            class="{{ $inputBase }} mt-3"
                            placeholder="Type model/actress name..."
                            value="{{ old('model_name', $isEdit ? ($post->model?->name ?? '') : '') }}"
                        />

                        @error('model_name')
                            <p class="text-red-400 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tag chip input --}}
                    <div>
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="{{ $label }}">Tags</div>
                                <div class="{{ $hint }}">
                                    Try to add short tags.
                                </div>
                            </div>
                            <button type="button" id="clearTags"
                                class="shrink-0 rounded-2xl border border-[var(--an-border)]
                                       bg-[color:var(--an-card)]/40 px-4 py-2 text-xs font-semibold
                                       text-[var(--an-text-muted)] hover:text-[var(--an-text)]
                                       hover:bg-[color:var(--an-card)]/70">
                                Clear
                            </button>
                        </div>

                        <div class="mt-3 space-y-3">
                            <input
                                id="tagSearch"
                                type="text"
                                class="{{ $inputBase }}"
                                inputmode="text"
                                enterkeyhint="done"
                                autocomplete="off"
                                autocapitalize="none"
                                spellcheck="false"
                                placeholder="Type a tag and press Enter..."
                            />

                            <div id="tagChips" class="flex flex-wrap gap-2"></div>

                            {{-- hidden inputs will be injected here --}}
                            <div id="tagHiddenInputs"></div>

                            <div>
                                <label class="block {{ $label }} mb-2">Highlight Tag (optional)</label>
                                <select
                                    name="highlight_tag_name"
                                    id="highlightTagSelect"
                                    class="{{ $inputBase }}"
                                >
                                    <option value="">Select highlight tag</option>
                                    {{-- options filled by JS based on selected tags --}}
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-[var(--an-text)]">
                            Thumbnail Image (hotlink)
                        </label>

                        <input
                            type="url"
                            name="thumbnail_url"
                            value="{{ old('thumbnail_url', $post->thumbnail_url ?? '') }}"
                            placeholder="https://example.com/thumb.jpg"
                            class="mt-1 w-full rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/60 px-3 py-2 text-sm text-[var(--an-text)] outline-none"
                        >

                        <div class="mt-1 text-xs text-[var(--an-text-muted)]">
                            Paste a direct image link (jpg/png/webp/gif). Used for previews on home/forums.
                        </div>
                    </div>


                </div>
            </div>


            {{-- Content editor --}}
            <div class="{{ $glass }} {{ $shadow }} overflow-hidden">
                <div class="p-3 sm:p-6 space-y-3">
                    <div class="{{ $label }} pl-1 pt-1 text-base">Content</div>
                    <div class="{{ $hint }} pl-2">
                        You can paste multiple links line by line and image URLs. Keep sections like: Download Links:, Watch Online:, Then image hotlinks 
                    </div>
                    <div class="flex gap-2 flex-wrap mb-3">
                        <button type="button" onclick="format('bold')" class="px-3 py-1.5 rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40 backdrop-blur-md text-sm font-semibold text-[var(--an-text)] hover:bg-[color:var(--an-card)]/70 hover:scale-[1.03] active:scale-[0.95] transition">B</button>
                        <button type="button" onclick="format('italic')" class="px-3 py-1.5 rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40 backdrop-blur-md text-sm italic text-[var(--an-text)] hover:bg-[color:var(--an-card)]/70 hover:scale-[1.03] active:scale-[0.95] transition">I</button>
                        <button type="button" onclick="format('insertUnorderedList')" class="px-3 py-1.5 rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40 backdrop-blur-md text-sm text-[var(--an-text)] hover:bg-[color:var(--an-card)]/70 hover:scale-[1.03] active:scale-[0.95] transition">• List</button>
                        <button type="button" onclick="format('formatBlock','h2')" class="px-3 py-1.5 rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40 backdrop-blur-md text-sm font-bold text-[var(--an-text)] hover:bg-[color:var(--an-card)]/70 hover:scale-[1.03] active:scale-[0.95] transition">H2</button>
                        <button type="button" onclick="format('formatBlock','h3')" class="px-3 py-1.5 rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40 backdrop-blur-md text-sm font-semibold text-[var(--an-text)] hover:bg-[color:var(--an-card)]/70 hover:scale-[1.03] active:scale-[0.95] transition">H3</button>
                        <button type="button" onclick="toggleAlign('center')" class="px-3 py-1.5 rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40 backdrop-blur-md text-sm font-semibold text-[var(--an-text)] hover:bg-[color:var(--an-card)]/70 hover:scale-[1.03] active:scale-[0.95] transition">Center</button>
                        <button type="button" onclick="insertImage()" class="px-3 py-1.5 rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40 backdrop-blur-md text-sm font-semibold text-[var(--an-text)] hover:bg-[color:var(--an-card)]/70 hover:scale-[1.03] active:scale-[0.95] transition">Image</button>
                        <button type="button" onclick="insertSocial()" class="px-3 py-1.5 rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40 backdrop-blur-md text-sm font-semibold text-[var(--an-text)] hover:bg-[color:var(--an-card)]/70 hover:scale-[1.03] active:scale-[0.95] transition">Social Media</button>
                        <button type="button" onclick="clearFormatting()" class="px-3 py-1.5 rounded-xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40 backdrop-blur-md text-sm text-[var(--an-text)] hover:bg-red-500/20 hover:text-red-400 transition">Clear</button>
                    </div>

                    <div id="editor" contenteditable="true" class="{{ $inputBase }} min-h-[320px] text-sm leading-6 post-content">
                        {!! old('content', $isEdit ? ($post->content ?? '') : '') !!}
                    </div>

                    <input type="hidden" name="content" id="hiddenContent">
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                const editor = document.getElementById('editor');
                const hiddenInput = document.getElementById('hiddenContent');

                // initial sync
                hiddenInput.value = editor.innerHTML.trim();

                // generic formatting
                window.format = function(command, value = null) {
                    editor.focus();
                    document.execCommand(command, false, value);
                };

                // toggle center alignment
                window.toggleAlign = function(align) {
                    const selection = window.getSelection();
                    if (!selection.rangeCount) return;

                    const range = selection.getRangeAt(0);
                    const wrapper = document.createElement('div');

                    wrapper.style.textAlign = align;

                    // wrap selected content
                    wrapper.appendChild(range.extractContents());
                    range.insertNode(wrapper);

                    // toggle: if already aligned, remove alignment
                    if (wrapper.style.textAlign === editor.style.textAlign) {
                        wrapper.style.textAlign = '';
                    }

                    // move cursor after inserted node
                    range.setStartAfter(wrapper);
                    range.setEndAfter(wrapper);
                    selection.removeAllRanges();
                    selection.addRange(range);
                };

                // insert image
                window.insertImage = function() {
                    const selection = window.getSelection();
                    const text = selection.toString().trim();
                    if (!text) return;

                    let url = text;
                    const bbcodeMatch = text.match(/\[url=(.*?)\]\[img\](.*?)\[\/img\]\[\/url\]/i);
                    if (bbcodeMatch) url = bbcodeMatch[2];

                    const filename = url.split('/').pop().replace(/[-_]/g, ' ').replace(/\.\w+$/, '');
                    const imgHTML = `<a href="${url}" target="_blank" rel="noopener noreferrer"><img src="${url}" alt="${filename}"/></a>`;

                    document.execCommand('insertHTML', false, imgHTML);
                };

                // insert social media
                window.insertSocial = function() {
                    const selection = window.getSelection();
                    const url = selection.toString().trim();
                    if (!url) return;

                    let html = '';

                    if (url.includes('facebook.com')) {
                        html = `<a href="${url}" target="_blank" 
            class="flex items-center justify-center gap-2 px-4 py-2 bg-blue-600/10 hover:bg-blue-600/20 backdrop-blur-md rounded-xl border border-white/20 shadow-lg transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M22.675 0H1.325C.593 0 0 .593 0 1.326v21.348C0 23.406.593 24 1.325 24h11.495v-9.294H9.691V11.04h3.129V8.414c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.667h-3.12V24h6.116C23.406 24 24 23.406 24 22.674V1.326C24 .593 23.406 0 22.675 0z"/>
            </svg>
            <span class="hidden sm:inline text-blue-700 font-semibold">Facebook</span>
            </a>`;
                    } else if (url.includes('wa.me')) {
                        html = `<a href="${url}" target="_blank"
                class="flex items-center justify-center gap-2 px-4 py-2 bg-green-600/10 hover:bg-green-600/20 backdrop-blur-md rounded-xl border border-white/20 shadow-lg transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.937 3.659 1.432 5.631 1.433h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <span class="hidden sm:inline text-green-500 font-medium">WhatsApp</span>
            </a>`;
                    } else if (url.includes('youtube.com')) {
                        html = `<a href="${url}" target="_blank"
            class="flex items-center justify-center gap-2 px-4 py-2 bg-red-600/10 hover:bg-red-600/20 backdrop-blur-md rounded-xl border border-white/20 shadow-lg transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
            </svg>
            <span class="hidden sm:inline text-red-700 font-semibold">YouTube</span>
            </a>`;
                    } else if (url.includes('linkedin.com')) {
                        html = `<a href="${url}" target="_blank"
            class="flex items-center justify-center gap-2 px-4 py-2 bg-blue-700/10 hover:bg-blue-700/20 backdrop-blur-md rounded-xl border border-white/20 shadow-lg transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-blue-700" fill="currentColor" viewBox="0 0 24 24">
                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452z"/>
            </svg>
            <span class="hidden sm:inline text-blue-800 font-semibold">LinkedIn</span>
            </a>`;
                    } else if (url.includes('spotify.com')) {
                        html = `<a href="${url}" target="_blank"
            class="flex items-center justify-center gap-2 px-4 py-2 bg-green-500/10 hover:bg-green-500/20 backdrop-blur-md rounded-xl border border-white/20 shadow-lg transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.494 17.308c-.216.354-.676.467-1.03.25-2.863-1.748-6.466-2.144-10.71-1.173-.404.093-.812-.162-.905-.566-.093-.404.162-.812.566-.905 4.646-1.063 8.625-.61 11.83 1.344.354.216.467.676.25 1.03zm1.467-3.262c-.272.443-.848.583-1.29.31-3.277-2.013-8.272-2.597-12.147-1.42-.5-.152-1.04-.23-1.192-.73s.23-1.04.73-1.192c4.43-1.344 9.936-.688 13.59 1.56.442.272.582.848.31 1.29zm.126-3.415C15.394 8.276 9.07 8.067 5.414 9.178c-.57.172-1.173-.153-1.345-.722s.153-1.173.722-1.345c4.2-1.275 11.201-1.032 15.483 1.51.512.304.678.968.374 1.48s-.968.678-1.48.374z"/>
            </svg>
            <span class="hidden sm:inline text-green-600 font-semibold">Spotify</span>
            </a>`;
                    }

                    document.execCommand('insertHTML', false, html);
                };

                // clear formatting
                window.clearFormatting = function() {
                    document.execCommand('removeFormat', false, null);
                };

                // force <p> on Enter
                editor.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        document.execCommand('insertHTML', false, '<p><br></p>');
                        return false;
                    }
                });

                // clean paste
                editor.addEventListener('paste', function(e) {
                    e.preventDefault();
                    const text = (e.clipboardData || window.clipboardData).getData('text/plain');
                    document.execCommand('insertHTML', false, text);
                });

                // sync to hidden input before submit
                editor.closest('form')?.addEventListener('submit', function() {
                    hiddenInput.value = editor.innerHTML.trim();
                });
            });
            </script>

            {{-- Paragraph template picker --}}
            <div class="{{ $glass }} {{ $shadow }} overflow-hidden">
                <div class="p-4 sm:p-6 space-y-4">
                    <div>
                        <h3 class="text-sm font-semibold text-[var(--an-text)]">Paragraph Templates</h3>
                        <p class="text-sm pl-1 text-[var(--an-text-muted)] mt-1">
                            Pick a template, edit it according to your post. Do not use the same template
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block {{ $label }}">Template Category</label>
                            <select id="paraCategory" class="{{ $inputBase }}">
                                <option value="">Select category</option>
                                @foreach($templates->keys() as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block {{ $label }}">Templates</label>
                            <select name="paragraph_template_id" id="paraTemplate" class="{{ $inputBase }}">
                                <option value="">Select template</option>
                            </select>
                        </div>
                    </div>

                    <div id="paraPreview"
                         class="hidden rounded-2xl border border-[var(--an-border)]
                                bg-[color:var(--an-bg)]/25 p-4 text-sm text-[var(--an-text)]/80"></div>

                    <div class="space-y-2">
                        <label class="block {{ $label }}">Edit Paragraph</label>
                        <textarea
                            name="paragraph_content"
                            id="paraEdit"
                            rows="6"
                            class="{{ $inputBase }}"
                            placeholder="Select a template to start editing..."
                        >{{ old('paragraph_content', $isEdit ? ($existingParagraph->content ?? '') : '') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Optional edit reason --}}
            @if($isEdit)
                <div class="{{ $glass }} {{ $shadow }} overflow-hidden">
                    <div class="p-4 sm:p-6 space-y-3">
                        <div class="{{ $label }}">Edit reason (optional)</div>
                        <div class="{{ $hint }}">This is saved to edit logs (helps moderators track changes).</div>

                        <textarea
                            name="edit_reason"
                            rows="3"
                            class="{{ $inputBase }}"
                            placeholder="Why are you updating this post?"
                        >{{ old('edit_reason') }}</textarea>
                    </div>
                </div>
            @endif

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row sm:items-center mx-2  gap-3">
                <button type="submit"
                        class="{{ $btnPrimary }}"
                        style="border-color: color-mix(in srgb, var(--an-primary) 35%, var(--an-border));
                               background: color-mix(in srgb, var(--an-primary) 18%, transparent);
                               color: var(--an-text);">
                    {{ $submitText }}
                </button>

                <a href="/"
                   class="inline-flex items-center justify-center rounded-2xl px-5 py-3 text-sm font-semibold
                          border border-[var(--an-border)]
                          bg-[color:var(--an-card)]/40
                          text-[var(--an-text-muted)] hover:text-[var(--an-text)]
                          hover:bg-[color:var(--an-card)]/70">
                    Cancel
                </a>
            </div>

        </div>

        {{-- Right (sidebar) --}}
        <div class="space-y-4 sm:space-y-6">

            <div class="{{ $glass }} {{ $shadow }} overflow-hidden">
                <div class="p-4 sm:p-6">
                    <h3 class="text-base font-extrabold text-[var(--an-text)] mb-2">Posting Tips</h3>
                    <ul class="text-sm text-[var(--an-text-muted)] space-y-2 list-disc pl-5">
                        <li>Use a clear title with keywords.</li>
                        <li>Put image URLs on their own line (jpg/png/webp).</li>
                        <li>Keep “Download Links / Watch Online” as headings.</li>
                        <li>The saved paragraph renders later as a normal text block.</li>
                    </ul>
                </div>
            </div>



        </div>
    </div>
</form>

{{-- JS (no packages) --}}
<script>
/**
 * IMPORTANT:
 * - We prevent Enter/Go/Done from submitting the form while in the tag input.
 * - We also block "implicit submit" on Enter for the entire form except textarea.
 */

const form = document.getElementById('postForm');
const tagSearch = document.getElementById('tagSearch');
const tagChips = document.getElementById('tagChips');
const tagHiddenInputs = document.getElementById('tagHiddenInputs');
const highlightSelect = document.getElementById('highlightTagSelect');
const clearTagsBtn = document.getElementById('clearTags');

// Restore old values after validation fail
const isEdit = @json($isEdit);
const oldTagNames = @json(old('tag_names', $isEdit ? ($existingTagNames ?? []) : []));
const oldHighlightName = @json(old('highlight_tag_name', $isEdit ? ($existingHighlightName ?? '') : ''));

const selected = new Set();

function normalizeTag(text) {
  return String(text || '').trim().toLowerCase();
}

function addTagByName(name) {
  const tag = normalizeTag(name);
  if (!tag) return;

  if (tag.length > 30) {
    alert('Tag too long (max 30 chars).');
    return;
  }

  selected.add(tag);
  renderTags();
}

function renderTags() {
  // chips
  tagChips.innerHTML = '';
  [...selected].forEach((name) => {
    const chip = document.createElement('button');
    chip.type = 'button';
    chip.className =
      'px-3 py-1.5 rounded-2xl border border-[var(--an-border)] ' +
      'bg-[color:var(--an-card)]/55 text-sm text-[var(--an-text)] ' +
      'hover:bg-[color:var(--an-card)]/75 active:scale-[0.98]';
    chip.textContent = name + ' ×';
    chip.addEventListener('click', () => {
      selected.delete(name);
      if (highlightSelect.value === name) highlightSelect.value = '';
      renderTags();
    });
    tagChips.appendChild(chip);
  });

  // hidden inputs tag_names[]
  tagHiddenInputs.innerHTML = '';
  [...selected].forEach((name) => {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'tag_names[]';
    input.value = name;
    tagHiddenInputs.appendChild(input);
  });

  // highlight dropdown options
  const current = highlightSelect.value;
  highlightSelect.innerHTML = '<option value="">Select highlight tag</option>';
  [...selected].forEach((name) => {
    const opt = document.createElement('option');
    opt.value = name;
    opt.textContent = name;
    highlightSelect.appendChild(opt);
  });

  if (current && selected.has(current)) {
    highlightSelect.value = current;
  }
}

// 1) HARD BLOCK implicit submit on Enter anywhere in form except textarea
form.addEventListener('keydown', (e) => {
  if (e.key !== 'Enter') return;

  const tag = e.target?.tagName?.toUpperCase();
  const isTextarea = tag === 'TEXTAREA';

  // allow Enter in textarea
  if (isTextarea) return;

  // allow Enter in tag input ONLY for adding tag (handled below)
  if (e.target === tagSearch) return;

  // block implicit submit for other inputs/selects
  e.preventDefault();
});

// 2) Tag input: Enter/Go/Done adds tag, never submits
tagSearch.addEventListener('keydown', (e) => {
  if (e.key === 'Enter') {
    e.preventDefault();
    e.stopPropagation();
    addTagByName(tagSearch.value);
    tagSearch.value = '';
  }
});

// 3) Mobile fallback: if browser triggers submit while focused on tag input
form.addEventListener('submit', (e) => {
  if (document.activeElement === tagSearch) {
    e.preventDefault();
    addTagByName(tagSearch.value);
    tagSearch.value = '';
  }
});

clearTagsBtn.addEventListener('click', () => {
  selected.clear();
  highlightSelect.value = '';
  renderTags();
});

// restore old tags and highlight
(oldTagNames || []).forEach(t => selected.add(normalizeTag(t)));
renderTags();

if (oldHighlightName) {
  const h = normalizeTag(oldHighlightName);
  if (selected.has(h)) highlightSelect.value = h;
}

// ----- PARAGRAPH TEMPLATES PICKER -----
const templates = @json($templates);
const catSelect = document.getElementById('paraCategory');
const tplSelect = document.getElementById('paraTemplate');
const preview = document.getElementById('paraPreview');
const edit = document.getElementById('paraEdit');
const oldTplId = @json(old('paragraph_template_id'));

function loadTemplatesForCategory(cat) {
  tplSelect.innerHTML = '<option value="">Select template</option>';
  preview.classList.add('hidden');
  preview.textContent = '';

  if (!cat || !templates[cat]) return;

  templates[cat].forEach(t => {
    const opt = document.createElement('option');
    opt.value = t.id;
    opt.textContent = `Template #${t.id}`;
    tplSelect.appendChild(opt);
  });
}

catSelect.addEventListener('change', () => {
  loadTemplatesForCategory(catSelect.value);
});

tplSelect.addEventListener('change', () => {
  const cat = catSelect.value;
  const id = tplSelect.value;

  if (!cat || !id) {
    preview.classList.add('hidden');
    preview.textContent = '';
    return;
  }

  const t = (templates[cat] || []).find(x => String(x.id) === String(id));
  if (!t) return;

  preview.textContent = t.content;
  preview.classList.remove('hidden');

  if (!edit.value || edit.value.trim().length === 0) {
    edit.value = t.content;
  }
});

// restore old template selection after validation fail
if (oldTplId) {
  for (const cat in templates) {
    const found = (templates[cat] || []).find(x => String(x.id) === String(oldTplId));
    if (found) {
      catSelect.value = cat;
      loadTemplatesForCategory(cat);
      tplSelect.value = String(oldTplId);
      preview.textContent = found.content;
      preview.classList.remove('hidden');
      break;
    }
  }
}
</script>

@endsection
