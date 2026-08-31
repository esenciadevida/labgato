<x-ui.card title="Search Insights">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div>
            <p class="text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">Top Searches (Last 7 Days)</p>
            @if($searchInsights['top_searches']->isEmpty())
                <div class="text-sm text-slate-500 dark:text-slate-400 py-6 text-center">No search data available yet.</div>
            @else
                <ul class="divide-y divide-slate-200 dark:divide-slate-700" role="list">
                    @foreach($searchInsights['top_searches'] as $search)
                        <li class="py-3 flex items-center justify-between gap-4">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $search->query }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300">
                                {{ $search->count }} searches
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-semibold text-slate-700 dark:text-slate-300">Content Gaps (Zero-Result Searches)</p>
                @if(!$searchInsights['content_gaps']->isEmpty())
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warning-100 text-warning-800 dark:bg-warning-900/40 dark:text-warning-300">
                        {{ $searchInsights['content_gaps']->count() }} gaps
                    </span>
                @endif
            </div>
            @if($searchInsights['content_gaps']->isEmpty())
                <div class="text-sm text-slate-500 dark:text-slate-400 py-6 text-center">No content gaps found. Great job!</div>
            @else
                <ul class="divide-y divide-slate-200 dark:divide-slate-700" role="list">
                    @foreach($searchInsights['content_gaps'] as $gap)
                        <li class="py-3 flex items-center justify-between gap-4">
                            <p class="text-sm font-medium text-slate-900 dark:text-slate-100 truncate">{{ $gap->query }}</p>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300">
                                {{ $gap->count }} missed
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
</x-ui.card>
