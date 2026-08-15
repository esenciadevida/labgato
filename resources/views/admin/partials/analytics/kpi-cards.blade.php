<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <x-ui.card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 rounded-lg bg-success-50 dark:bg-success-900/30">
                <svg class="w-6 h-6 text-success-600 dark:text-success-300 dark:text-success-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Published Resources</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($kpis['published_count']) }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 rounded-lg bg-warning-50 dark:bg-warning-900/30">
                <svg class="w-6 h-6 text-warning-600 dark:text-warning-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pending Review</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($kpis['under_review_count']) }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 rounded-lg bg-info-50 dark:bg-info-900/30">
                <svg class="w-6 h-6 text-info-600 dark:text-info-300 dark:text-info-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Searches Today</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($kpis['search_volume_today']) }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 rounded-lg bg-primary-50 dark:bg-primary-900/30">
                <svg class="w-6 h-6 text-primary-600 dark:text-primary-300 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">AI Usage Today</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($kpis['ai_usage_today']) }}</p>
            </div>
        </div>
    </x-ui.card>
</div>
