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
@endphp

@section('title', $isEdit ? 'Update Post' : 'Create a Post')

@section('content')
<form id="postForm" action="{{ $formAction }}" method="POST" class="space-y-6">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left (main) --}}
        <div class="lg:col-span-2 space-y-6">

            <x-posting.card>
                <div class="space-y-5">
                    <x-posting.field label="Title">
                        <x-posting.input
                            name="title"
                            placeholder="Type a clear title..."
                            value="{{ old('title', $isEdit ? ($post->title ?? '') : '') }}"
                        />
                    </x-posting.field>

                    <x-posting.field label="Forum">
                        <x-posting.select name="forum_id">
                            <option value="">Select a forum</option>
                            @foreach($forums as $forum)
                                <option
                                    value="{{ $forum->id }}"
                                    @selected(old('forum_id', $isEdit ? ($post->forum_id ?? '') : '') == $forum->id)
                                >
                                    {{ $forum->name }}
                                </option>
                            @endforeach
                        </x-posting.select>
                    </x-posting.field>

                    {{-- Tag chip input --}}
                    <x-posting.field
                        label="Tags"
                        hint="Type a tag name and press Enter (or Go/Done on mobile) to add. Then choose one highlight tag."
                    >
                        <div class="space-y-3">
                            <div class="flex gap-2">
                                <input
                                    id="tagSearch"
                                    type="text"
                                    class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 outline-none focus:border-white/20"
                                    inputmode="text"
                                    enterkeyhint="done"
                                    autocomplete="off"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    placeholder="Type a tag and press Enter..."
                                />
                                <button type="button" id="clearTags"
                                    class="rounded-lg border border-white/10 px-3 py-2 text-sm text-white/70 hover:text-white hover:border-white/20">
                                    Clear
                                </button>
                            </div>

                            <div id="tagChips" class="flex flex-wrap gap-2"></div>

                            {{-- hidden inputs will be injected here --}}
                            <div id="tagHiddenInputs"></div>

                            <div>
                                <label class="block text-sm text-white/80 mb-2">Highlight Tag (optional)</label>
                                <select
                                    name="highlight_tag_name"
                                    id="highlightTagSelect"
                                    class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 outline-none focus:border-white/20"
                                >
                                    <option value="">Select highlight tag</option>
                                    {{-- options filled by JS based on selected tags --}}
                                </select>
                            </div>
                        </div>
                    </x-posting.field>
                </div>
            </x-posting.card>

            {{-- Content editor (no 3rd party) --}}
            <x-posting.card>
                <x-posting.field
                    label="Content"
                    hint="You can paste multiple links line by line and image URLs. Keep sections like: Download Links:, Watch Online:, Click image to see in high quality:"
                >
                    <x-posting.textarea
                        name="content"
                        rows="16"
                        placeholder="Download Links:
https://...

Watch Online:
https://...

Click image to see in high quality:
https://imagehost.com/your-image.jpg
"
                    >{{ old('content', $isEdit ? ($post->content ?? '') : '') }}</x-posting.textarea>
                </x-posting.field>
            </x-posting.card>

            {{-- Paragraph template picker --}}
            <x-posting.card>
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold">Paragraph Templates</h3>
                        <p class="text-sm text-white/60">Pick a template, edit it, and we’ll save it to post_paragraphs.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="block text-sm text-white/80">Template Category</label>
                            <select id="paraCategory"
                                class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 outline-none focus:border-white/20"
                            >
                                <option value="">Select category</option>
                                @foreach($templates->keys() as $cat)
                                    <option value="{{ $cat }}">{{ $cat }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm text-white/80">Templates</label>
                            <select name="paragraph_template_id" id="paraTemplate"
                                class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 outline-none focus:border-white/20"
                            >
                                <option value="">Select template</option>
                            </select>
                        </div>
                    </div>

                    <div id="paraPreview" class="rounded-lg border border-white/10 bg-black/30 p-3 text-sm text-white/70 hidden"></div>

                    <div class="space-y-2">
                        <label class="block text-sm text-white/80">Edit Paragraph</label>
                        <textarea
                            name="paragraph_content"
                            id="paraEdit"
                            rows="6"
                            class="w-full rounded-lg border border-white/10 bg-black/40 px-3 py-2 outline-none focus:border-white/20"
                            placeholder="Select a template to start editing..."
                        >{{ old('paragraph_content', $isEdit ? ($existingParagraph->content ?? '') : '') }}</textarea>
                    </div>
                </div>
            </x-posting.card>

            {{-- Optional edit reason --}}
            @if($isEdit)
                <x-posting.card>
                    <x-posting.field
                        label="Edit reason (optional)"
                        hint="This is saved to edit logs (helps moderators track changes)."
                    >
                        <x-posting.textarea name="edit_reason" rows="3" placeholder="Why are you updating this post?">{{ old('edit_reason') }}</x-posting.textarea>
                    </x-posting.field>
                </x-posting.card>
            @endif

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 font-medium hover:bg-indigo-500">
                    {{ $submitText }}
                </button>
                <a href="/" class="text-white/60 hover:text-white">Cancel</a>
            </div>

        </div>

        {{-- Right (sidebar) --}}
        <div class="space-y-6">
            <x-posting.card>
                <h3 class="font-semibold mb-2">SEO Tips</h3>
                <ul class="text-sm text-white/60 space-y-2 list-disc pl-5">
                    <li>Use a clear title with keywords.</li>
                    <li>Put image URLs on their own line (jpg/png/webp) so we can detect them for JSON-LD.</li>
                    <li>Keep “Download Links / Watch Online” as headings.</li>
                    <li>Your saved paragraph will be rendered as a normal &lt;p&gt; later for extra indexable content.</li>
                </ul>
            </x-posting.card>

            <x-posting.card>
                <h3 class="font-semibold mb-2">Tags</h3>
                <p class="text-sm text-white/60">Type any tag and press Enter/Done to add it. (Autocomplete can be added later.)</p>
            </x-posting.card>
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
    chip.className = 'px-3 py-1 rounded-lg bg-white/10 border border-white/10 text-sm hover:border-white/20';
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
