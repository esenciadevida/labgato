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
            <div class="flex-shrink-0 p-3 rounded-lg bg-cyan-50 dark:bg-cyan-900/30">
                <svg class="w-6 h-6 text-cyan-600 dark:text-cyan-300 dark:text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Users</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($userMetrics['total_users']) }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/30">
                <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-300 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Active Users</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($userMetrics['active_users']) }}</p>
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

    <x-ui.card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 rounded-lg bg-violet-50 dark:bg-violet-900/30">
                <svg class="w-6 h-6 text-violet-600 dark:text-violet-300 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Exams Taken</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($examMetrics['total_attempts']) }}</p>
            </div>
        </div>
    </x-ui.card>

    <x-ui.card>
        <div class="flex items-center">
            <div class="flex-shrink-0 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/30">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-300 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                </svg>
            </div>
            <div class="ml-4">
                <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Certificates Issued</p>
                <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($certificateMetrics['total_issued']) }}</p>
            </div>
        </div>
    </x-ui.card>
</div>
