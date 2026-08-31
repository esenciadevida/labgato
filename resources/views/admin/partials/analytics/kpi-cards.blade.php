@php
    $newResources30d = collect($resourceGrowth)->sum('count');
    $onboardingRate = $userMetrics['total_users'] > 0 ? $userMetrics['onboarding_rate'] : 0;
    $activeShare = $userMetrics['total_users'] > 0
        ? round(($userMetrics['active_users'] / $userMetrics['total_users']) * 100, 1)
        : 0;

    $cards = [
        [
            'label' => 'Published Resources',
            'value' => number_format($kpis['published_count']),
            'icon' => 'resources',
            'tone' => 'indigo',
            'sub' => $newResources30d > 0
                ? '+ ' . number_format($newResources30d) . ' added (30d)'
                : 'Across all content types',
        ],
        [
            'label' => 'Pending Review',
            'value' => number_format($kpis['under_review_count']),
            'icon' => 'clock',
            'tone' => 'warning',
            'sub' => $kpis['under_review_count'] > 0
                ? 'Awaiting your review'
                : 'All caught up',
        ],
        [
            'label' => 'Total Users',
            'value' => number_format($userMetrics['total_users']),
            'icon' => 'users',
            'tone' => 'cyan',
            'sub' => 'Onboarding at ' . number_format($onboardingRate, 1) . '%',
        ],
        [
            'label' => 'Active Users',
            'value' => number_format($userMetrics['active_users']),
            'icon' => 'shield',
            'tone' => 'emerald',
            'sub' => number_format($activeShare, 1) . '% of total · ' . number_format($userMetrics['last_7d_active']) . ' active (7d)',
        ],
        [
            'label' => 'Searches Today',
            'value' => number_format($kpis['search_volume_today']),
            'icon' => 'search',
            'tone' => 'info',
            'sub' => 'Site searches performed today',
        ],
        [
            'label' => 'AI Usage Today',
            'value' => number_format($kpis['ai_usage_today']),
            'icon' => 'ai',
            'tone' => 'violet',
            'sub' => number_format($aiUtilization['total_tokens']) . ' tokens (7d)',
        ],
        [
            'label' => 'Exams Taken',
            'value' => number_format($examMetrics['total_attempts']),
            'icon' => 'exam',
            'tone' => 'primary',
            'sub' => 'Pass rate ' . number_format($examMetrics['pass_rate'], 1) . '%',
        ],
        [
            'label' => 'Certificates Issued',
            'value' => number_format($certificateMetrics['total_issued']),
            'icon' => 'certificate',
            'tone' => 'amber',
            'sub' => $certificateMetrics['total_revoked'] > 0
                ? number_format($certificateMetrics['total_revoked']) . ' revoked'
                : 'No revocations',
        ],
    ];

    $tones = [
        'indigo' => [
            'box' => 'bg-indigo-50 dark:bg-indigo-900/30',
            'text' => 'text-indigo-600 dark:text-indigo-300',
        ],
        'warning' => [
            'box' => 'bg-warning-50 dark:bg-warning-900/30',
            'text' => 'text-warning-600 dark:text-warning-400',
        ],
        'cyan' => [
            'box' => 'bg-cyan-50 dark:bg-cyan-900/30',
            'text' => 'text-cyan-600 dark:text-cyan-400',
        ],
        'emerald' => [
            'box' => 'bg-emerald-50 dark:bg-emerald-900/30',
            'text' => 'text-emerald-600 dark:text-emerald-400',
        ],
        'info' => [
            'box' => 'bg-info-50 dark:bg-info-900/30',
            'text' => 'text-info-600 dark:text-info-400',
        ],
        'violet' => [
            'box' => 'bg-violet-50 dark:bg-violet-900/30',
            'text' => 'text-violet-600 dark:text-violet-400',
        ],
        'primary' => [
            'box' => 'bg-primary-50 dark:bg-primary-900/30',
            'text' => 'text-primary-600 dark:text-primary-300',
        ],
        'amber' => [
            'box' => 'bg-amber-50 dark:bg-amber-900/30',
            'text' => 'text-amber-600 dark:text-amber-400',
        ],
    ];

    $icons = [
        'resources' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'clock' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'shield' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
        'search' => 'M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z',
        'ai' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'exam' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'certificate' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z',
    ];
@endphp

<div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach($cards as $card)
        <div class="bg-white rounded-xl border border-slate-200 shadow-card p-4 dark:bg-slate-800 dark:border-slate-700">
            <div class="flex items-center justify-between">
                <div class="flex-shrink-0 p-2.5 rounded-lg {{ $tones[$card['tone']]['box'] }}">
                    <svg class="w-5 h-5 {{ $tones[$card['tone']]['text'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icons[$card['icon']] }}"/>
                    </svg>
                </div>
            </div>
            <p class="mt-3 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ $card['value'] }}</p>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $card['label'] }}</p>
            <p class="mt-1 text-xs text-slate-400 dark:text-slate-500">{{ $card['sub'] }}</p>
        </div>
    @endforeach
</div>
