<x-ui.card title="Resource Growth (Last 30 Days)" class="flex flex-col">
    @php
        $totalGrowth = collect($resourceGrowth)->sum('count');
        $prevPeriod = collect($resourceGrowth)->take(15)->sum('count');
        $recentPeriod = collect($resourceGrowth)->skip(15)->sum('count');
        $delta = $prevPeriod > 0 ? round((($recentPeriod - $prevPeriod) / $prevPeriod) * 100, 0) : null;

        $resourceChartConfig = [
            'kind' => 'bar',
            'labels' => collect($resourceGrowth)->pluck('date'),
            'datasets' => [[
                'label' => 'New Resources',
                'data' => collect($resourceGrowth)->pluck('count'),
                'color' => 'indigo',
            ]],
            'options' => ['maxTicksLimit' => 10, 'stepSize' => 1],
        ];
    @endphp

    @if($totalGrowth === 0)
        <x-ui.empty-state title="No resource growth yet" description="New resources will appear here as you create them." />
    @else
        <div class="flex flex-wrap items-end justify-between gap-2 mb-4">
            <div>
                <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($totalGrowth) }}</p>
                <p class="text-xs text-slate-500 dark:text-slate-400">New resources this month</p>
            </div>
            @if($delta !== null)
                <span class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold {{ $delta >= 0 ? 'bg-success-100 text-success-700 dark:bg-success-900/30 dark:text-success-300' : 'bg-danger-100 text-danger-700 dark:bg-danger-900/30 dark:text-danger-300' }}">
                    @if($delta >= 0)
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/></svg>
                        {{ $delta }}%
                    @else
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        {{ abs($delta) }}%
                    @endif
                    <span class="font-normal text-slate-500 dark:text-slate-400">vs prior 15d</span>
                </span>
            @endif
        </div>

        <div class="flex-1 min-h-[180px]">
            <canvas
                data-config="@json($resourceChartConfig)"
                data-height="180"
                role="img"
                aria-label="Bar chart showing {{ number_format($totalGrowth) }} new resources created over the last 30 days"
            ></canvas>
        </div>

        <table class="sr-only">
            <caption>Daily new resources created over the last 30 days</caption>
            <thead><tr><th scope="col">Date</th><th scope="col">New Resources</th></tr></thead>
            <tbody>
                @foreach($resourceGrowth as $day)
                    <tr><td>{{ $day['date'] }}</td><td>{{ $day['count'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-ui.card>
