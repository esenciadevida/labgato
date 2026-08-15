@php
    $items = $items ?? collect();
    $isMember = auth()->check() && $items->isNotEmpty();
@endphp

<div class="py-10">
    @if($isMember)
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">Recently Viewed</h2>
            </div>

            <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" role="list" aria-label="Recently viewed resources">
                @foreach($items->take(8) as $item)
                    <li>
                        <a href="{{ $item->url ?? '#' }}" class="flex flex-col h-full p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-300 hover:shadow-md transition-all duration-200 dark:bg-slate-800 dark:border-slate-700 dark:hover:border-primary-700">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 text-xs font-medium mb-2 dark:bg-primary-900/30 dark:text-primary-300">
                                {{ $item->resource_type ?? 'Resource' }}
                            </span>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100 line-clamp-2">{{ $item->name ?? 'Unknown resource' }}</p>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Viewed {{ $item->viewed_at->diffForHumans() }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div x-data="recentlyViewedHome()" x-init="init()">
            <template x-if="items.length > 0">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">Recently Viewed</h2>
                        <button
                            @click="clearHistory()"
                            class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 rounded"
                            aria-label="Clear recently viewed history"
                        >
                            Clear
                        </button>
                    </div>

                    <ul class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4" role="list" aria-label="Recently viewed resources">
                        <template x-for="(item, index) in items" :key="index">
                            <li>
                                <a
                                    :href="item.url"
                                    class="flex flex-col h-full p-4 bg-white rounded-xl border border-slate-200 hover:border-primary-300 hover:shadow-md transition-all duration-200 dark:bg-slate-800 dark:border-slate-700 dark:hover:border-primary-700"
                                >
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 text-xs font-medium mb-2 dark:bg-primary-900/30 dark:text-primary-300" x-text="item.type"></span>
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100 line-clamp-2 dark:text-slate-100" x-text="item.title"></p>
                                </a>
                            </li>
                        </template>
                    </ul>
                </div>
            </template>
        </div>
    @endif
</div>

<script>
    function recentlyViewedHome() {
        return {
            storageKey: 'labgato-recently-viewed',
            items: [],

            init() {
                const stored = localStorage.getItem(this.storageKey);
                if (stored) {
                    try {
                        this.items = JSON.parse(stored);
                    } catch (e) {
                        this.items = [];
                    }
                }
            },

            clearHistory() {
                this.items = [];
                localStorage.removeItem(this.storageKey);
            },
        };
    }
</script>
