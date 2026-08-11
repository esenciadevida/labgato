@php
    $categories = \App\Models\Category::query()->where('is_active', true)->orderBy('name')->get();
    $tags = \App\Models\Tag::query()->orderBy('name')->get();
@endphp

@props(['q' => null, 'type' => 'all', 'placeholder' => 'Search LabGato...'])

<form action="{{ route('public.search') }}" method="GET" role="search">
    <label for="search-input" class="sr-only">Search</label>

    <div class="flex flex-col gap-3">
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input
                type="search"
                id="search-input"
                name="q"
                value="{{ old('q', $q) }}"
                placeholder="{{ $placeholder }}"
                class="w-full pl-10 pr-4 py-2.5 rounded-md border border-neutral-300 bg-white shadow-card text-neutral-900 placeholder-neutral-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                autocomplete="off"
                maxlength="255"
            >
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
            <div>
                <label for="search-type" class="sr-only">Type</label>
                <select
                    id="search-type"
                    name="type"
                    class="w-full rounded-md border border-neutral-300 bg-white shadow-card text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                    @foreach(['all' => 'All Types', 'calculator' => 'Calculators', 'tool' => 'Tools'] as $value => $label)
                        <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="search-category" class="sr-only">Category</label>
                <select
                    id="search-category"
                    name="category"
                    class="w-full rounded-md border border-neutral-300 bg-white shadow-card text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->slug }}" @selected(request()->input('category') === $category->slug)>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="search-tag" class="sr-only">Tag</label>
                <select
                    id="search-tag"
                    name="tag"
                    class="w-full rounded-md border border-neutral-300 bg-white shadow-card text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20"
                >
                    <option value="">All Tags</option>
                    @foreach($tags as $tag)
                        <option value="{{ $tag->slug }}" @selected(request()->input('tag') === $tag->slug)>
                            {{ $tag->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-md shadow-card text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors">
                    Search
                </button>
            </div>
        </div>
    </div>
</form>
