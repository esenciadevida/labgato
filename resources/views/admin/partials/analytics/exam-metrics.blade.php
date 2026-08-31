<x-ui.card title="Exam Performance" class="flex flex-col">
    @php
        $examChartConfig = [
            'kind' => 'line',
            'labels' => collect($examMetrics['score_trend'])->pluck('date'),
            'datasets' => [[
                'label' => 'Avg Score %',
                'data' => collect($examMetrics['score_trend'])->pluck('avg_score')->map(fn ($v) => $v === null ? null : (float) $v),
                'color' => 'violet',
            ]],
            'options' => ['min' => 0, 'max' => 100, 'suffix' => '%', 'maxTicksLimit' => 7],
        ];
    @endphp

    @if($examMetrics['total_attempts'] === 0)
        <x-ui.empty-state title="No exam data yet" description="Pass rates and scores will appear as members take exams." />
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Average Score</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($examMetrics['avg_score'], 1) }}%</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pass Rate</p>
                    <p class="mt-1 text-2xl font-bold {{ $examMetrics['pass_rate'] >= 70 ? 'text-success-600' : 'text-danger-600' }}">{{ number_format($examMetrics['pass_rate'], 1) }}%</p>
                </div>
            </div>

            <div class="flex-1 min-h-[140px]">
                <canvas
                    data-config="@json($examChartConfig)"
                    data-height="140"
                    role="img"
                    aria-label="Line chart showing average exam scores over the last 30 days"
                ></canvas>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-200 dark:border-slate-700">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Official</p>
                    <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ number_format($examMetrics['official_count']) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Practice</p>
                    <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ number_format($examMetrics['practice_count']) }}</p>
                </div>
            </div>

            @if($examMetrics['integrity_review'] > 0 || $examMetrics['integrity_high_risk'] > 0)
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Integrity Flags</p>
                    <div class="flex flex-wrap gap-2">
                        @if($examMetrics['integrity_review'] > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-warning-100 text-warning-800 dark:bg-warning-900/30 dark:text-warning-300">
                                {{ $examMetrics['integrity_review'] }} under review
                            </span>
                        @endif
                        @if($examMetrics['integrity_high_risk'] > 0)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-danger-100 text-danger-800 dark:bg-danger-900/30 dark:text-danger-300">
                                {{ $examMetrics['integrity_high_risk'] }} high risk
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    @endif
</x-ui.card>
