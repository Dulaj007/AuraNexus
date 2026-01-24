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

                </div>
            </div>

            {{-- Content editor --}}
            <div class="{{ $glass }} {{ $shadow }} overflow-hidden">
                <div class="p-3 sm:p-6 space-y-3">
                    <div class="{{ $label }} pl-1 pt-1 text-base">Content</div>
                    <div class="{{ $hint }} pl-2">
                        You can paste multiple links line by line and image URLs. Keep sections like: Download Links:, Watch Online:, Then image hotlinks 
                    </div>

                    <textarea
                        name="content"
                        rows="16"
                        class="{{ $inputBase }} min-h-[320px] font-mono text-[13px] leading-6"
                        placeholder="Download Links:
https://...

Watch Online:
https://...

Images:
https://imagehost.com/your-image.jpg
"
                    >{{ old('content', $isEdit ? ($post->content ?? '') : '') }}</textarea>
                </div>
            </div>

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
