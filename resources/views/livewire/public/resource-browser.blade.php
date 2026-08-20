@php
    $results = $this->results;
    $categories = $this->availableCategories;
    $tags = $this->availableTags;
    $total = $this->total;
    $hasActiveFilters = $this->hasActiveFilters;
@endphp

<div class="flex flex-col lg:flex-row gap-8">
    {{-- Mobile filter trigger --}}
    <div class="lg:hidden flex items-center justify-between">
        <button
            wire:click="openDrawer"
            type="button"
            class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-slate-300 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            Filters
            @if($hasActiveFilters)
                <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-600 text-white text-xs font-semibold">
                    {{ count($this->categories) + count($this->tags) + ($this->search !== '' ? 1 : 0) }}
                </span>
            @endif
        </button>

        <span class="text-sm text-slate-600 dark:text-slate-300" role="status">
            {{ $total }} {{ Str::plural('result', $total) }}
        </span>
    </div>

    {{-- Mobile drawer --}}
    @if($drawerOpen)
        <div class="fixed inset-0 z-50 lg:hidden" x-data x-init="$nextTick(() => $refs.drawerPanel.focus())">
            <div class="absolute inset-0 bg-black/40" wire:click="closeDrawer"></div>
            <div
                x-ref="drawerPanel"
                class="absolute inset-y-0 left-0 w-full max-w-sm bg-white dark:bg-slate-900 shadow-xl overflow-y-auto p-6"
                role="dialog"
                aria-modal="true"
                aria-label="Filter resources"
                tabindex="-1"
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-lg font-display font-bold text-slate-900 dark:text-slate-100">Filters</h2>
                    <button wire:click="closeDrawer" type="button" class="p-2 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-primary-500" aria-label="Close filters">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @include('livewire.public.partials._filter-panel')

                <div class="mt-6 pt-4 border-t border-slate-200 dark:border-slate-700">
                    <button wire:click="closeDrawer" type="button" class="w-full inline-flex items-center justify-center px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                        Show {{ $total }} {{ Str::plural('result', $total) }}
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Desktop sidebar --}}
    <aside class="hidden lg:block w-64 shrink-0 space-y-6" aria-label="Filters">
        @include('livewire.public.partials._filter-panel')
    </aside>

    {{-- Main content --}}
    <div class="flex-1 min-w-0">
        {{-- Top bar: search + sort (desktop) --}}
        <div class="hidden lg:flex items-center gap-4 mb-6">
            <div class="relative flex-1">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <label for="browser-search-desktop" class="sr-only">Search resources</label>
                <input
                    type="search"
                    id="browser-search-desktop"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search..."
                    class="w-full pl-11 pr-4 py-2.5 rounded-lg border border-slate-300 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 shadow-card"
                    autocomplete="off"
                    maxlength="255"
                >
            </div>

            <label for="browser-sort-desktop" class="sr-only">Sort by</label>
            <select
                id="browser-sort-desktop"
                wire:model.live="sort"
                class="rounded-lg border border-slate-300 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25"
            >
                <option value="newest">Newest</option>
                <option value="az">A – Z</option>
                <option value="za">Z – A</option>
            </select>
        </div>

        {{-- Active filter chips --}}
        @if($hasActiveFilters)
            <div class="flex flex-wrap items-center gap-2 mb-4">
                @foreach($categories->whereIn('id', $this->categories) as $cat)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-primary-50 text-primary-700 text-xs font-medium dark:bg-primary-900/30 dark:text-primary-300 border border-primary-200 dark:border-primary-800">
                        {{ $cat->name }}
                        <button wire:click="removeCategory({{ $cat->id }})" type="button" class="ml-0.5 p-0.5 rounded-full hover:bg-primary-200 dark:hover:bg-primary-800 focus:outline-none focus:ring-1 focus:ring-primary-500" aria-label="Remove {{ $cat->name }} filter">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endforeach

                @foreach($tags->whereIn('id', $this->tags) as $tag)
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-medium dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600">
                        #{{ $tag->name }}
                        <button wire:click="removeTag({{ $tag->id }})" type="button" class="ml-0.5 p-0.5 rounded-full hover:bg-slate-200 dark:hover:bg-slate-600 focus:outline-none focus:ring-1 focus:ring-primary-500" aria-label="Remove {{ $tag->name }} filter">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endforeach

                @if($search !== '')
                    <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-medium dark:bg-amber-900/30 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                        "{{ $search }}"
                        <button wire:click="$set('search', '')" type="button" class="ml-0.5 p-0.5 rounded-full hover:bg-amber-200 dark:hover:bg-amber-800 focus:outline-none focus:ring-1 focus:ring-primary-500" aria-label="Clear search">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </span>
                @endif

                <button wire:click="clearAll" type="button" class="text-xs font-medium text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 underline transition-colors">
                    Clear all
                </button>
            </div>
        @endif

        {{-- Result count --}}
        <p class="text-sm text-slate-600 dark:text-slate-300 mb-6" role="status">
            Showing <span class="font-semibold">{{ $results->count() }}</span> of <span class="font-semibold">{{ $total }}</span> {{ Str::plural('result', $total) }}
        </p>

        {{-- Results grid --}}
        <div class="relative">
            <div wire:loading class="absolute inset-0 z-10 flex items-center justify-center pointer-events-none">
                <svg class="animate-spin h-8 w-8 text-primary-500" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
            </div>

            @if($results->isEmpty())
                <div class="text-center py-16">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                        <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">No results found</h2>
                    <p class="text-slate-600 dark:text-slate-300 mb-6">
                        @if($hasActiveFilters)
                            No {{ Str::plural(Str::headline($this->type)) }} match your current filters. Try adjusting your search or removing some filters.
                        @else
                            No {{ Str::plural(Str::headline($this->type)) }} are available yet.
                        @endif
                    </p>
                    @if($hasActiveFilters)
                        <button wire:click="clearAll" type="button" class="inline-flex items-center px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-card dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700">
                            Clear filters
                        </button>
                    @endif
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($results as $resource)
                        @include("livewire.public.partials.{$this->type}-card", ['resource' => $resource])
                    @endforeach
                </div>

                @if($results->hasPages())
                    <div class="mt-8">
                        {{ $results->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
