<div class="space-y-6">
    {{-- Search (mobile only) --}}
    <div class="lg:hidden">
        <label for="browser-search-mobile" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Search</label>
        <div class="relative">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input
                type="search"
                id="browser-search-mobile"
                wire:model.live.debounce.300ms="search"
                placeholder="Search..."
                class="w-full pl-11 pr-4 py-2.5 rounded-lg border border-slate-300 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25"
                autocomplete="off"
                maxlength="255"
            >
        </div>
    </div>

    {{-- Sort (mobile only) --}}
    <div class="lg:hidden">
        <label for="browser-sort-mobile" class="block text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Sort by</label>
        <select
            id="browser-sort-mobile"
            wire:model.live="sort"
            class="w-full rounded-lg border border-slate-300 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25"
        >
            <option value="newest">Newest</option>
            <option value="az">A – Z</option>
            <option value="za">Z – A</option>
        </select>
    </div>

    {{-- Categories --}}
    @if($categories->isNotEmpty())
        <div>
            <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">Categories</h3>
            <ul class="space-y-1" role="group" aria-label="Filter by category">
                @foreach($categories as $cat)
                    <li>
                        <label class="flex items-center gap-2.5 px-2 py-1.5 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 cursor-pointer transition-colors group">
                            <input
                                type="checkbox"
                                value="{{ $cat->id }}"
                                wire:click="toggleCategory({{ $cat->id }})"
                                @checked(in_array($cat->id, $this->categories))
                                class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-700"
                                aria-label="{{ $cat->name }}"
                            >
                            <span class="text-sm text-slate-700 dark:text-slate-200 flex-1">{{ $cat->name }}</span>
                            <span class="text-xs text-slate-400 dark:text-slate-500 tabular-nums" aria-label="{{ $cat->count }} {{ Str::plural('result', $cat->count) }}">{{ $cat->count }}</span>
                        </label>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Clear all --}}
    @if($hasActiveFilters)
        <button
            wire:click="clearAll"
            wire:loading.attr="disabled"
            type="button"
            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-slate-300 text-sm font-medium text-slate-700 bg-white hover:border-danger-400 hover:bg-danger-50 hover:text-danger-600 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:border-danger-500 dark:hover:bg-danger-900/30 dark:hover:text-danger-400 disabled:opacity-50"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            Clear all filters
        </button>
    @endif
</div>
