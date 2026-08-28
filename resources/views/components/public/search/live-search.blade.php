@php
    $searchUrl = route('public.search');
    $liveUrl = route('public.search.live');
@endphp

<div
    x-data="liveSearch('{{ $liveUrl }}', '{{ $searchUrl }}')"
    @keydown.escape.window="closePanel()"
    @click.outside="closePanel()"
    class="relative shrink-0"
>
    {{-- Search icon trigger --}}
    <button
        type="button"
        x-show="!panelOpen"
        @click="openPanel()"
        class="w-9 h-9 sm:w-10 sm:h-10 flex items-center justify-center rounded-full text-slate-500 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 transition-colors dark:text-slate-400 dark:hover:text-slate-200 dark:hover:bg-slate-700"
        aria-label="Open search"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
    </button>

    {{-- Expanded live search panel --}}
    <div
        x-cloak
        x-show="panelOpen"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 -translate-y-1"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-1"
        class="fixed left-4 right-4 top-20 z-50 sm:absolute sm:right-0 sm:left-auto sm:top-full sm:mt-2 sm:w-[min(calc(100vw-2rem),380px)] origin-top-right"
        role="search"
    >
        <form
            action="{{ $searchUrl }}"
            method="GET"
            x-on:submit.prevent="selectHighlighted()"
            class="relative flex items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 shadow-pop dark:border-slate-700 dark:bg-slate-800"
        >
            <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <label for="live-search-input" class="sr-only">Search</label>
            <input
                type="text"
                id="live-search-input"
                name="q"
                x-ref="liveSearchInput"
                x-model.debounce.300ms="query"
                x-on:focus="open()"
                x-on:keydown.arrow-down.prevent="highlightNext()"
                x-on:keydown.arrow-up.prevent="highlightPrev()"
                x-on:keydown.enter.prevent="selectHighlighted()"
                role="combobox"
                :aria-expanded="isOpen"
                aria-controls="live-search-listbox"
                aria-autocomplete="list"
                :aria-activedescendant="activeIndex >= 0 ? 'live-search-option-' + activeIndex : null"
                placeholder="Search resources..."
                autocomplete="off"
                class="w-full bg-transparent py-2 text-base sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none dark:text-slate-100 dark:placeholder-slate-500"
            >
            <button
                type="button"
                @click="closePanel()"
                class="shrink-0 p-1 rounded text-slate-400 hover:text-slate-600 focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 dark:text-slate-300 dark:hover:text-slate-100"
                aria-label="Close search"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </form>

        {{-- Live results dropdown --}}
        <div
            x-show="isOpen && query.length >= 2"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="absolute top-full left-0 right-0 mt-1 bg-white rounded-lg shadow-pop border border-slate-200 z-50 max-h-80 overflow-y-auto dark:bg-slate-800 dark:border-slate-700"
            x-cloak
        >
            <div x-show="isLoading" class="p-3 space-y-3">
                <x-ui.skeleton class="h-4 w-3/4" />
                <x-ui.skeleton class="h-4 w-1/2" />
                <x-ui.skeleton class="h-4 w-2/3" />
            </div>

            <div x-show="!isLoading && results.length > 0" class="py-1">
                <div
                    id="live-search-listbox"
                    role="listbox"
                    :aria-label="results.length + ' results found'"
                >
                    <template x-for="(result, index) in results" :key="result.url">
                        <a
                            :id="'live-search-option-' + index"
                            :href="result.url"
                            role="option"
                            :aria-selected="activeIndex === index"
                            x-on:mouseenter="activeIndex = index"
                            x-on:mouseleave="activeIndex = -1"
                            x-on:click="close()"
                            class="flex items-center justify-between px-3 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors dark:text-slate-200 dark:hover:bg-slate-700"
                            :class="activeIndex === index ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-300' : ''"
                        >
                            <span class="truncate" x-text="result.title"></span>
                            <span
                                class="ml-2 flex-shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium text-slate-700 dark:text-slate-200"
                                :class="{
                                    'bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300': result.type === 'calculator',
                                    'bg-success-100 text-success-700 dark:bg-success-900/40 dark:text-success-300': result.type === 'reference-range',
                                    'bg-info-100 text-info-700 dark:bg-info-900/40 dark:text-info-300': result.type === 'interpretation',
                                    'bg-warning-100 text-warning-700 dark:bg-warning-900/40 dark:text-warning-300': result.type === 'knowledge',
                                    'bg-slate-200 text-slate-700 dark:bg-slate-700 dark:text-slate-200': result.type === 'tool',
                                    'bg-danger-100 text-danger-700 dark:bg-danger-900/40 dark:text-danger-300': result.type === 'sop',
                                }"
                                x-text="result.type.replace('_', ' ')"
                            ></span>
                        </a>
                    </template>
                </div>

                <div class="border-t border-slate-100 px-3 py-2 dark:border-slate-800">
                    <a
                        :href="searchUrl + '?q=' + encodeURIComponent(query)"
                        x-on:click="closePanel()"
                        class="text-sm text-primary-600 hover:text-primary-700 font-medium"
                    >
                        View all results
                    </a>
                </div>
            </div>

            <div x-show="!isLoading && results.length === 0 && query.length >= 2" class="p-4 text-center">
                <p class="text-sm text-slate-500 dark:text-slate-400">No results found for "<span x-text="query"></span>"</p>
                <a
                    :href="searchUrl + '?q=' + encodeURIComponent(query)"
                    x-on:click="closePanel()"
                    class="mt-2 inline-block text-sm text-primary-600 hover:text-primary-700 font-medium"
                >
                    Try a full search
                </a>
            </div>

            <div aria-live="polite" class="sr-only" x-text="results.length + ' results available'"></div>
        </div>
    </div>
</div>
