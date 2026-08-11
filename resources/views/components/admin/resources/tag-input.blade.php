@props([
    'name' => 'tags[]',
    'selected' => [],
    'tags' => null,
])

@php
    $tags = $tags ?? \App\Models\Tag::query()->orderBy('name')->get();
    $selectedIds = collect($selected)->map(fn ($id) => (int) $id)->toArray();
@endphp

<div>
    @if($tags->isEmpty())
        <p class="text-sm text-neutral-500">No tags available.</p>
    @else
        <div class="rounded-md border border-neutral-200">
            <div class="max-h-64 overflow-y-auto p-2">
                @foreach($tags as $tag)
                    <label class="flex items-center gap-2 rounded px-2 py-1 text-sm text-neutral-700 hover:bg-neutral-50">
                        <input
                            type="checkbox"
                            name="{{ $name }}"
                            value="{{ $tag->id }}"
                            @checked(in_array((int) $tag->id, $selectedIds))
                            class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                        >
                        <span>{{ \Illuminate\Support\Str::lower($tag->name) }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <p class="mt-1 text-xs text-neutral-500">Select one or more tags.</p>
    @endif
</div>
