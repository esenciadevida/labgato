@props(['q' => null, 'type' => 'all', 'placeholder' => 'Search resources...'])

@php
    $isSearchPage = request()->routeIs('public.search');
    $hasQuery = filled($q);
@endphp

@if(!$isSearchPage)
    {{-- Icon-first expanding search for header use --}}
    <div
        x-data="{ open: {{ $hasQuery ? 'true' : 'false' }} }"
        class="relative"
        @keydown.escape.window="open = false"
    >
        {{-- Search icon trigger --}}
        <button
            type="button"
            x-show="!open"
            @click="open = true; $nextTick(() => $refs.desktopSearchInput.focus())"
            class="p-2 min-h-[44px] min-w-[44px] rounded-md text-slate-500 dark:text-slate-400 hover:text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors"
            aria-label="Open search"
        >
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </button>

        {{-- Expanded search form --}}
        <div
            x-cloak
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            @click.outside="open = false"
            class="absolute right-0 top-0 z-50 w-[min(calc(100vw-2rem),480px)] rounded-lg border border-slate-200 bg-white shadow-pop dark:border-slate-700 dark:bg-slate-800"
        >
            <form action="{{ route('public.search') }}" method="GET" role="search" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <label for="desktop-search-input" class="sr-only">Search</label>
                    <input
                        type="search"
                        x-ref="desktopSearchInput"
                        id="desktop-search-input"
                        name="q"
                        value="{{ old('q', $q) }}"
                        placeholder="{{ $placeholder }}"
                        class="w-full pl-11 pr-10 py-2.5 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-800 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 shadow-lg"
                        autocomplete="off"
                        maxlength="255"
                        @keydown.enter="if($el.value.trim()) $el.form.submit()"
                    >
                    <button
                        type="button"
                        @click="open = false"
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1 rounded text-slate-400 hover:text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-primary-500"
                        aria-label="Close search"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @if($hasQuery)
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
            </form>
        </div>
    </div>
@else
    {{-- Full search form for search page --}}
    <form action="{{ route('public.search') }}" method="GET" role="search">
        <label for="search-input" class="sr-only">Search</label>

        <div class="flex flex-col gap-3">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input
                    type="search"
                    id="search-input"
                    name="q"
                    value="{{ old('q', $q) }}"
                    placeholder="{{ $placeholder }}"
                    class="w-full pl-11 pr-4 py-3 rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 shadow-card"
                    autocomplete="off"
                    maxlength="255"
                >
            </div>

            <div class="flex flex-col sm:flex-row gap-3">
<div class="flex-1">
                    <label for="search-type" class="sr-only">Type</label>
                    <select
                        id="search-type"
                        name="type"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25"
                    >
                        @foreach(['all' => 'All Types', 'calculator' => 'Calculators', 'tool' => 'Tools', 'reference-range' => 'Reference Ranges', 'interpretation' => 'Interpretations', 'knowledge' => 'Knowledge', 'sop' => 'SOPs'] as $value => $label)
                            <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

<div class="flex-1">
                    <label for="search-category" class="sr-only">Category</label>
                    <select
                        id="search-category"
                        name="category"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25"
                    >
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->slug }}" @selected(request()->input('category') === $category->slug)>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-1">
                    <label for="search-tag" class="sr-only">Tag</label>
                    <select
                        id="search-tag"
                        name="tag"
                        class="w-full rounded-lg border border-slate-300 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25"
                    >
                        <option value="">All Tags</option>
                        @foreach($tags as $tag)
                            <option value="{{ $tag->slug }}" @selected(request()->input('tag') === $tag->slug)>
                                {{ $tag->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:w-auto">
                    <button type="submit" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg shadow-card text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors min-h-[44px]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        Search
                    </button>
                </div>
            </div>
        </div>
    </form>
@endif
