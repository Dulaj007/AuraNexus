@extends('layouts.admin')

@section('title', 'UI: Home Tag Cards')

@section('content')
@php
    $glass = 'rounded-3xl border border-[var(--an-border)]
              bg-[color:var(--an-card)]/65 backdrop-blur-xl';
    $btn = 'inline-flex items-center justify-center gap-2 rounded-2xl px-4 py-2 text-sm font-extrabold
            border border-[var(--an-border)]
            bg-[color:var(--an-card)]/55 hover:bg-[color:var(--an-card)]/75
            transition focus:outline-none focus:ring-2 focus:ring-[var(--an-ring)]';
@endphp

<div class="max-w-6xl mx-auto space-y-4">

    <div class="{{ $glass }} p-5">
        <div class="text-lg font-extrabold">Home Tag Cards</div>
        <div class="text-sm text-[var(--an-text-muted)] mt-1">
            Admin-curated tag tiles shown on the home page. Clicking a tile goes to the tag page.
        </div>
    </div>

    {{-- Create --}}
    <div class="{{ $glass }} p-5">
        <form method="POST" action="{{ route('admin.ui.home-tag-cards.store') }}" enctype="multipart/form-data"
              class="grid gap-3 sm:grid-cols-3">
            @csrf

            <div class="sm:col-span-1">
                <label class="text-xs text-[var(--an-text-muted)]">Tag name</label>
                <input name="tag_name" value="{{ old('tag_name') }}"
                       class="mt-1 w-full rounded-2xl border border-[var(--an-border)]
                              bg-[color:var(--an-bg)]/40 px-3 py-2 text-sm"
                       placeholder="e.g. Pics">
                @error('tag_name') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
            </div>

            <div class="sm:col-span-1">
                <label class="text-xs text-[var(--an-text-muted)]">Image</label>
                <input type="file" name="image" accept="image/*"
                       class="mt-1 w-full rounded-2xl border border-[var(--an-border)]
                              bg-[color:var(--an-bg)]/40 px-3 py-2 text-sm">
                @error('image') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror
            </div>

            <div class="sm:col-span-1">
                <label class="text-xs text-[var(--an-text-muted)]">Sort order (optional)</label>
                <input name="sort_order" value="{{ old('sort_order') }}"
                       type="number" min="0" max="9999"
                       class="mt-1 w-full rounded-2xl border border-[var(--an-border)]
                              bg-[color:var(--an-bg)]/40 px-3 py-2 text-sm"
                       placeholder="Auto">
                @error('sort_order') <div class="text-xs mt-1 text-red-400">{{ $message }}</div> @enderror

                <label class="mt-2 inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="is_enabled" value="1" checked>
                    <span class="text-[var(--an-text-muted)]">Enabled</span>
                </label>
            </div>

            <div class="sm:col-span-3 flex justify-end">
                <button class="{{ $btn }}" type="submit">Add</button>
            </div>
        </form>
    </div>

    {{-- List --}}
    <div class="{{ $glass }} overflow-hidden">
        <div class="px-5 py-4 border-b border-[var(--an-border)] font-extrabold">
            Current Cards ({{ $cards->count() }})
        </div>

        <div class="divide-y divide-[var(--an-border)]">
            @forelse($cards as $card)
                @php
                    $imgUrl = $card->image_path ? asset('storage/'.$card->image_path) : null;
                @endphp

                <div class="p-4 flex items-center gap-4">
                    <div class="h-16 w-16 overflow-hidden rounded-2xl border border-[var(--an-border)] bg-[color:var(--an-card)]/40">
                        @if($imgUrl)
                            <img src="{{ $imgUrl }}" class="h-full w-full object-cover" loading="lazy" alt="">
                        @endif
                    </div>

                    <div class="min-w-0 flex-1">
                        <div class="font-extrabold">
                            #{{ $card->tag?->name ?? '—' }}
                            <span class="text-xs ml-2 px-2 py-1 rounded-full border border-[var(--an-border)]
                                         {{ $card->is_enabled ? 'bg-[var(--an-success)]/15' : 'bg-[var(--an-danger)]/15' }}">
                                {{ $card->is_enabled ? 'Enabled' : 'Disabled' }}
                            </span>
                        </div>
                        <div class="text-xs text-[var(--an-text-muted)] truncate">
                            Image: {{ $card->image_path ?? '—' }}
                        </div>
                    </div>

                    {{-- order --}}
                    <form method="POST" action="{{ route('admin.ui.home-tag-cards.order', $card) }}" class="flex items-center gap-2">
                        @csrf
                        @method('PATCH')
                        <input name="sort_order" type="number" min="0" max="9999"
                               value="{{ (int) $card->sort_order }}"
                               class="w-24 rounded-2xl border border-[var(--an-border)]
                                      bg-[color:var(--an-bg)]/40 px-3 py-2 text-sm">
                        <button class="{{ $btn }} !px-3 !py-2" type="submit">Save</button>
                    </form>

                    {{-- toggle --}}
                    <form method="POST" action="{{ route('admin.ui.home-tag-cards.toggle', $card) }}">
                        @csrf
                        @method('PATCH')
                        <button class="{{ $btn }} !px-3 !py-2" type="submit">
                            {{ $card->is_enabled ? 'Disable' : 'Enable' }}
                        </button>
                    </form>

                    {{-- delete --}}
                    <form method="POST" action="{{ route('admin.ui.home-tag-cards.destroy', $card) }}"
                          onsubmit="return confirm('Remove this home tag card?');">
                        @csrf
                        @method('DELETE')
                        <button class="{{ $btn }} !px-3 !py-2"
                                style="border-color: color-mix(in srgb, var(--an-danger) 35%, var(--an-border));
                                       background: color-mix(in srgb, var(--an-danger) 12%, transparent);">
                            Remove
                        </button>
                    </form>
                </div>
            @empty
                <div class="p-6 text-sm text-[var(--an-text-muted)]">
                    No home tag cards yet.
                </div>
            @endforelse
        </div>
    </div>

</div>
@endsection
