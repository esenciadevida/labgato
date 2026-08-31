<x-ui.card title="Certificate Tracking" class="flex flex-col">
    @php
        $recentIssuance = collect($certificateMetrics['issuance_trend'])->sum('count');
        $certChartConfig = [
            'kind' => 'bar',
            'labels' => collect($certificateMetrics['issuance_trend'])->pluck('date'),
            'datasets' => [[
                'label' => 'Certificates',
                'data' => collect($certificateMetrics['issuance_trend'])->pluck('count'),
                'color' => 'amber',
            ]],
            'options' => ['maxTicksLimit' => 7, 'stepSize' => 1],
        ];
    @endphp

    @if($certificateMetrics['total_issued'] === 0)
        <x-ui.empty-state title="No certificates issued yet" description="Issued certificates will appear here as members earn them." />
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Avg Score</p>
                    <p class="mt-1 text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($certificateMetrics['avg_score'], 1) }}%</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Revoked</p>
                    <p class="mt-1 text-xl font-bold {{ $certificateMetrics['total_revoked'] > 0 ? 'text-danger-600' : 'text-slate-900 dark:text-slate-100' }}">{{ number_format($certificateMetrics['total_revoked']) }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Issued (30d)</p>
                    <p class="mt-1 text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($recentIssuance) }}</p>
                </div>
            </div>

            <div class="flex-1 min-h-[140px]">
                <canvas
                    data-config="@json($certChartConfig)"
                    data-height="140"
                    role="img"
                    aria-label="Bar chart showing certificate issuances over the last 30 days"
                ></canvas>
            </div>

            @if(!empty($certificateMetrics['type_distribution']))
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">By Type</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($certificateMetrics['type_distribution'] as $index => $count)
                            <div class="text-center p-2 rounded-lg bg-slate-50 dark:bg-slate-700/40">
                                <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ number_format($count) }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $certificateMetrics['type_labels'][$index] ?? 'Unknown' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</x-ui.card>
