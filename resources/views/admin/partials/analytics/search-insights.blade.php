<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <x-ui.card title="Top Searches (Last 7 Days)">
        @if($searchInsights['top_searches']->isEmpty())
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No search data available yet.</p>
            </div>
        @else
            <ul class="divide-y divide-slate-200 dark:divide-slate-700" role="list">
                @foreach($searchInsights['top_searches'] as $search)
                    <li class="py-3 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $search->query }}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-success-100 text-success-800 dark:bg-success-900/30 dark:text-success-300">
                                {{ $search->count }} searches
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>

    <x-ui.card title="Content Gaps (Zero-Result Searches)">
        @if($searchInsights['content_gaps']->isEmpty())
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No content gaps found. Great job!</p>
            </div>
        @else
            <div class="mb-3">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-warning-300">
                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    These searches returned no results
                </span>
            </div>
            <ul class="divide-y divide-slate-200 dark:divide-slate-700" role="list">
                @foreach($searchInsights['content_gaps'] as $gap)
                    <li class="py-3 flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $gap->query }}</p>
                        </div>
                        <div class="ml-4 flex-shrink-0">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300">
                                {{ $gap->count }} missed
                            </span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-ui.card>
</div>
