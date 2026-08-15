@props([
    'resourceType' => '',
    'resourceTitle' => '',
    'resourceUrl' => '',
    'items' => collect(),
])

@php
    $items = $items ?? collect();
    $isMember = auth()->check() && $items->isNotEmpty();
@endphp

<div class="mt-8">
    @if($isMember)
        <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Recently Viewed</h3>
                <form method="POST" action="{{ route('recently-viewed.clear') }}" class="inline">
                    @csrf
                    <button type="submit" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 rounded" aria-label="Clear recently viewed history">
                        Clear history
                    </button>
                </form>
            </div>
            <ul class="grid grid-cols-2 sm:grid-cols-4 gap-3" role="list" aria-label="Recently viewed resources">
                @foreach($items->take(8) as $item)
                    <li>
                        <a href="{{ $item->url ?? '#' }}" class="flex flex-col h-full p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-medium mb-2">
                                {{ $item->resource_type ?? 'Resource' }}
                            </span>
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100 line-clamp-2">{{ $item->name ?? 'Unknown resource' }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div x-data="recentlyViewed()" x-init="init()" data-resource-type="{{ $resourceType }}" data-resource-title="{{ $resourceTitle }}" data-resource-url="{{ $resourceUrl }}">
            <template x-if="items.length > 0">
                <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Recently Viewed</h3>
                        <button
                            @click="clearHistory()"
                            class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 rounded"
                            aria-label="Clear recently viewed history"
                        >
                            Clear
                        </button>
                    </div>

                    <ul class="grid grid-cols-2 sm:grid-cols-4 gap-3" role="list" aria-label="Recently viewed resources">
                        <template x-for="(item, index) in items" :key="index">
                            <li>
                                <a
                                    :href="item.url"
                                    class="flex flex-col h-full p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700 hover:border-primary-300 dark:hover:border-primary-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 text-xs font-medium mb-2" x-text="item.type"></span>
                                    <p class="text-sm font-medium text-slate-900 dark:text-slate-100 line-clamp-2" x-text="item.title"></p>
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
    function recentlyViewed() {
        return {
            resourceType: '',
            resourceTitle: '',
            resourceUrl: '',
            storageKey: 'labgato-recently-viewed',
            items: [],
            maxItems: 8,

            init() {
                this.resourceType = this.$el.getAttribute('data-resource-type') || '';
                this.resourceTitle = this.$el.getAttribute('data-resource-title') || '';
                this.resourceUrl = this.$el.getAttribute('data-resource-url') || '';

                if (!this.resourceUrl) {
                    this.resourceUrl = window.location.pathname;
                    this.resourceType = this.resourceUrl.includes('/calculators/') ? 'Calculator'
                        : this.resourceUrl.includes('/tools/') ? 'Tool'
                        : this.resourceUrl.includes('/knowledge/') ? 'Knowledge'
                        : this.resourceUrl.includes('/sops/') ? 'SOP'
                        : this.resourceUrl.includes('/reference-ranges/') ? 'ReferenceRange'
                        : this.resourceUrl.includes('/interpretation/') ? 'Interpretation' : '';
                    this.resourceTitle = document.title || '';
                }

                const stored = localStorage.getItem(this.storageKey);
                if (stored) {
                    try {
                        this.items = JSON.parse(stored);
                    } catch (e) {
                        this.items = [];
                    }
                }

                if (this.resourceUrl) {
                    this.addToHistory({
                        type: this.resourceType,
                        title: this.resourceTitle,
                        url: this.resourceUrl,
                    });
                }
            },

            addToHistory(item) {
                this.items = this.items.filter((i) => i.url !== item.url);
                this.items.unshift(item);
                if (this.items.length > this.maxItems) {
                    this.items = this.items.slice(0, this.maxItems);
                }
                localStorage.setItem(this.storageKey, JSON.stringify(this.items));
            },

            clearHistory() {
                this.items = [];
                localStorage.removeItem(this.storageKey);
            },
        };
    }
</script>
