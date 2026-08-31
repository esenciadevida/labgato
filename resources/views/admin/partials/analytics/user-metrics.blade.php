<x-ui.card title="New Users (Last 30 Days)" class="flex flex-col">
    @php
        $totalRegistrations = collect($userMetrics['daily_registrations'])->sum('count');
        $regDelta = $userMetrics['total_users'] > 0
            ? round(($totalRegistrations / $userMetrics['total_users']) * 100, 1)
            : 0;

        $userChartConfig = [
            'kind' => 'line',
            'labels' => collect($userMetrics['daily_registrations'])->pluck('date'),
            'datasets' => [[
                'label' => 'Registrations',
                'data' => collect($userMetrics['daily_registrations'])->pluck('count'),
                'color' => 'cyan',
            ]],
            'options' => ['maxTicksLimit' => 7, 'stepSize' => 1],
        ];
    @endphp

    @if($totalRegistrations === 0 && $userMetrics['total_users'] === 0)
        <x-ui.empty-state title="No user data yet" description="User registrations will appear here as people sign up." />
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-3 lg:col-span-1">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">New in last 30 days</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($totalRegistrations) }}</p>
                </div>
            </div>

            <div class="flex-1 min-h-[160px]">
                <canvas
                    data-config="@json($userChartConfig)"
                    data-height="160"
                    role="img"
                    aria-label="Line chart showing user registrations over the last 30 days"
                ></canvas>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-3 border-t border-slate-200 dark:border-slate-700">
                <div class="rounded-lg bg-slate-50 dark:bg-slate-700/40 p-3">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">All-time Onboarded</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($userMetrics['onboarding_rate'], 1) }}%</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($userMetrics['onboarded_users']) }} of {{ number_format($userMetrics['total_users']) }} users</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-700/40 p-3">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Active last 7 days</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($userMetrics['last_7d_active']) }}</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Users with a login this week</p>
                </div>
                <div class="rounded-lg bg-slate-50 dark:bg-slate-700/40 p-3">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">New (30d) share of all users</p>
                    <p class="text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($regDelta, 1) }}%</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">{{ number_format($totalRegistrations) }} of {{ number_format($userMetrics['total_users']) }} total users</p>
                </div>
            </div>
        </div>

        <table class="sr-only">
            <caption>User registration data for the last 30 days</caption>
            <thead><tr><th scope="col">Date</th><th scope="col">Registrations</th></tr></thead>
            <tbody>
                @foreach($userMetrics['daily_registrations'] as $day)
                    <tr><td>{{ $day['date'] }}</td><td>{{ $day['count'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-ui.card>
