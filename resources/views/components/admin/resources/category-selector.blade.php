@props([
    'name' => 'categories[]',
    'selected' => [],
    'categories' => null,
])

@php
    $categories = $categories ?? \App\Models\Category::query()
        ->where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    $selectedIds = collect($selected)->map(fn ($id) => (int) $id)->toArray();

    $buildRows = function ($all, $parentId = null, $depth = 0) use (&$buildRows, $selectedIds) {
        $rows = [];

        foreach ($all->where('parent_id', $parentId) as $category) {
            $isSelected = in_array((int) $category->id, $selectedIds);
            $isInactive = ! (bool) $category->is_active;

            if (! $isInactive || $isSelected) {
                $rows[] = [
                    'category' => $category,
                    'depth' => $depth,
                    'selected' => $isSelected,
                ];
            }

            foreach ($buildRows($all, $category->id, $depth + 1) as $child) {
                $rows[] = $child;
            }
        }

        return $rows;
    };

    $rows = $buildRows($categories);
@endphp

<fieldset>
    <legend class="sr-only">Categories</legend>
    @if(empty($rows))
        <p class="text-sm text-neutral-500">No categories available.</p>
    @else
        <div class="max-h-64 space-y-1 overflow-y-auto rounded-md border border-neutral-200 p-3">
            @foreach($rows as $row)
                <label class="flex items-center gap-2 text-sm text-neutral-700" style="padding-left: {{ $row['depth'] * 16 }}px;">
                    <input
                        type="checkbox"
                        name="{{ $name }}"
                        value="{{ $row['category']->id }}"
                        @checked($row['selected'])
                        class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500"
                    >
                    <span>{{ $row['category']->name }}</span>
                    @if(! $row['category']->is_active)
                        <span class="text-xs text-neutral-400">(inactive)</span>
                    @endif
                </label>
            @endforeach
        </div>
        <p class="mt-1 text-xs text-neutral-500">Select one or more categories.</p>
    @endif
</fieldset>
