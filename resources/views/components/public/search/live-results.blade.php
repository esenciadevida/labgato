@props([
    'baseUrl' => null,
    'type' => 'all',
    'showTypeSelect' => false,
    'initialQ' => '',
    'initialType' => 'all',
    'initialCategory' => '',
    'initialTag' => '',
    'placeholder' => 'Search resources...',
    'limit' => 10,
    'inputId' => 'q',
    'label' => 'Search',
])

@php
    $categories = $categories ?? collect();
    $tags = $tags ?? collect();
@endphp

<div
    x-data="liveResults({
        liveUrl: @js(route('public.search.live')),
        baseUrl: @js($baseUrl),
        type: @js($type),
        showTypeSelect: {{ $showTypeSelect ? 'true' : 'false' }},
        initialQ: @js($initialQ),
        initialType: @js($initialType),
        initialCategory: @js($initialCategory),
        initialTag: @js($initialTag),
        limit: {{ (int) $limit }},
    })"
    class="space-y-6"
>
    <form action="{{ $baseUrl }}" method="GET" role="search" class="flex flex-col gap-3 sm:flex-row">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <label for="{{ $inputId }}" class="sr-only">{{ $label }}</label>
            <input
                type="search"
                id="{{ $inputId }}"
                name="q"
                x-model.debounce.300ms="q"
                value="{{ $initialQ }}"
                placeholder="{{ $placeholder }}"
                class="w-full pl-11 pr-3 py-2.5 rounded-lg border border-slate-300 bg-white dark:bg-slate-800 text-sm text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25 shadow-card"
                autocomplete="off"
                maxlength="255"
            >
        </div>

        @if($showTypeSelect)
            <select name="type" x-model="type" x-on:change="selectChanged($event)" class="rounded-lg border border-slate-300 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25" aria-label="Type">
                <option value="all" @selected($initialType === 'all')>All Types</option>
                <option value="calculator" @selected($initialType === 'calculator')>Calculators</option>
                <option value="tool" @selected($initialType === 'tool')>Tools</option>
                <option value="reference_range" @selected($initialType === 'reference_range')>Reference Ranges</option>
                <option value="interpretation" @selected($initialType === 'interpretation')>Interpretations</option>
                <option value="knowledge" @selected($initialType === 'knowledge')>Knowledge</option>
                <option value="sop" @selected($initialType === 'sop')>SOPs</option>
            </select>
        @endif

        <select name="category" x-model="category" x-on:change="selectChanged($event)" class="rounded-lg border border-slate-300 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25" aria-label="Filter by category">
            <option value="" @selected($initialCategory === '')>All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->slug }}" @selected($initialCategory === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>

        <select name="tag" x-model="tag" x-on:change="selectChanged($event)" class="rounded-lg border border-slate-300 bg-white dark:bg-slate-800 px-3.5 py-2.5 text-sm text-slate-900 dark:text-slate-100 shadow-card focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25" aria-label="Filter by tag">
            <option value="" @selected($initialTag === '')>All tags</option>
            @foreach($tags as $tag)
                <option value="{{ $tag->slug }}" @selected($initialTag === $tag->slug)>{{ $tag->name }}</option>
            @endforeach
        </select>

        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-lg shadow-card text-sm font-semibold text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors min-h-[44px]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Search
        </button>
    </form>

    {{-- Live results --}}
    <div x-show="active" x-cloak aria-label="Live search results">
        <div aria-live="polite" class="sr-only" x-text="!isLoading && active ? (error ? 'Search failed' : (results.length === 0 ? 'No results found' : total + ' ' + (total === 1 ? 'result' : 'results') + ' found')) : ''"></div>
        <div x-show="isLoading" class="space-y-3">
            <x-ui.skeleton class="h-24 w-full" />
            <x-ui.skeleton class="h-24 w-full" />
            <x-ui.skeleton class="h-24 w-full" />
        </div>

        <template x-if="!isLoading && error">
            <div class="text-center py-16">
                <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">Something went wrong</h2>
                <p class="text-slate-600 dark:text-slate-300 mb-6">We couldn't run your search. Please try again.</p>
                <a :href="viewAllUrl()" class="inline-flex items-center px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-card dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700">
                    Run a full search
                </a>
            </div>
        </template>

        <template x-if="!isLoading && !error && results.length > 0">
            <div>
                <p class="text-sm text-slate-600 dark:text-slate-300 mb-6" role="status">
                    <span x-text="total + (total === 1 ? ' result' : ' results')"></span>
                </p>

                <div class="space-y-4">
                    <template x-for="(result, index) in results" :key="result.url + '-' + index">
                        <article class="bg-white dark:bg-slate-800 rounded-xl shadow-card border border-slate-200 dark:border-slate-700 p-5 hover:border-primary-300 hover:shadow-card-hover transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="shrink-0 mt-0.5">
                                    <div class="w-10 h-10 rounded-lg border flex items-center justify-center" :class="meta(result.type).color">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <template x-for="(path, i) in meta(result.type).paths" :key="i">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="path"></path>
                                            </template>
                                        </svg>
                                    </div>
                                </div>

                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100 mb-1 leading-snug">
                                        <a :href="result.url" class="text-primary-700 hover:text-primary-800 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 rounded" x-text="result.title"></a>
                                    </h3>

                                    <p x-show="result.description" class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed line-clamp-2" x-text="result.description"></p>

                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full border font-medium uppercase tracking-wider" :class="meta(result.type).color" x-text="meta(result.type).label"></span>
                                        <span x-show="result.categoryName" class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-200 border border-slate-200 dark:border-slate-600" x-text="result.categoryName"></span>
                                        <time x-show="result.publishedAt" class="text-slate-400" x-text="formatDate(result.publishedAt)"></time>
                                    </div>

                                    <ul x-show="result.tags.length" class="mt-2 flex flex-wrap gap-1.5" aria-label="Tags">
                                        <template x-for="tag in result.tags" :key="tag">
                                            <li class="inline-flex items-center px-2 py-0.5 rounded-full bg-slate-50 text-slate-600 dark:bg-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-600 text-xs" x-text="'#' + tag"></li>
                                        </template>
                                    </ul>
                                </div>
                            </div>
                        </article>
                    </template>
                </div>

                <div class="mt-8 text-center">
                    <a :href="viewAllUrl()" class="inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-semibold text-primary-700 bg-primary-50 hover:bg-primary-100 dark:text-primary-300 dark:bg-primary-900/30 dark:hover:bg-primary-900/50 transition-colors">
                        View all <span x-text="total"></span> results
                    </a>
                </div>
            </div>
        </template>

        <template x-if="!isLoading && !error && results.length === 0">
            <div class="text-center py-16">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100 mb-2">No results found</h2>
                <p class="text-slate-600 dark:text-slate-300 mb-6">Try different keywords, or check the spelling of your search terms.</p>
                <button type="button" @click="clearFilters()" class="inline-flex items-center px-4 py-2.5 border border-slate-300 rounded-lg text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 transition-colors shadow-card dark:bg-slate-800 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700">
                    Clear search
                </button>
            </div>
        </template>
    </div>

    {{-- Server-rendered results (initial state / no-JS fallback) --}}
    <div x-show="!active">
        {{ $slot }}
    </div>
</div>
