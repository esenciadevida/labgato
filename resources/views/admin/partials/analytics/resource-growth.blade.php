<x-ui.card title="Resource Growth (Last 30 Days)">
    @php
        $maxCount = collect($resourceGrowth)->max('count') ?: 1;
    @endphp

    @if(collect($resourceGrowth)->sum('count') === 0)
        <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No resource growth data available yet.</p>
        </div>
    @else
        <div
            role="img"
            aria-label="Bar chart showing {{ collect($resourceGrowth)->sum('count') }} new resources created over the last 30 days"
            class="mt-4"
        >
            <div class="flex items-end space-x-1 h-40" aria-hidden="true">
                @foreach($resourceGrowth as $day)
                    @php
                        $heightPercent = $maxCount > 0 ? ($day['count'] / $maxCount) * 100 : 0;
                    @endphp
                    <div class="flex-1 flex flex-col items-center justify-end h-full" title="{{ $day['date'] }}: {{ $day['count'] }} new resources">
                        <div
                            class="w-full bg-primary-500 rounded-t transition-all duration-300 hover:bg-primary-600"
                            style="height: {{ max($heightPercent, 2) }}%"
                            role="presentation"
                        ></div>
                    </div>
                @endforeach
            </div>
            <div class="flex space-x-1 mt-2">
                @foreach($resourceGrowth as $index => $day)
                    @if($index % 5 === 0 || $loop->last)
                        <div class="flex-1 text-center">
                            <span class="text-xs text-slate-500 dark:text-slate-400">{{ $day['date'] }}</span>
                        </div>
                    @else
                        <div class="flex-1"></div>
                    @endif
                @endforeach
            </div>
        </div>

        <table class="sr-only">
            <caption>Daily new resources created over the last 30 days</caption>
            <thead>
                <tr>
                    <th scope="col">Date</th>
                    <th scope="col">New Resources</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resourceGrowth as $day)
                    <tr>
                        <td>{{ $day['date'] }}</td>
                        <td>{{ $day['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-ui.card>
