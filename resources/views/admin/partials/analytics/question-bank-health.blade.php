<x-ui.card title="Question Bank Health" class="flex flex-col">
    @php
        $statusChartConfig = [
            'kind' => 'doughnut',
            'labels' => $questionBankHealth['status_labels'],
            'datasets' => [[
                'label' => 'Status',
                'data' => $questionBankHealth['status_values'],
                'colors' => ['#22c55e', '#3b82f6', '#94a3b8'],
            ]],
            'options' => ['legend' => true],
        ];
        $difficultyChartConfig = [
            'kind' => 'doughnut',
            'labels' => $questionBankHealth['difficulty_labels'],
            'datasets' => [[
                'label' => 'Difficulty',
                'data' => $questionBankHealth['difficulty_values'],
                'colors' => ['#22c55e', '#f59e0b', '#ef4444'],
            ]],
            'options' => ['legend' => true],
        ];
    @endphp

    @if($questionBankHealth['total'] === 0)
        <x-ui.empty-state title="No questions in the bank yet" description="Question bank metrics will appear here as you add questions." />
    @else
        <div class="space-y-5">
            <div class="grid grid-cols-3 gap-4">
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total</p>
                    <p class="mt-1 text-xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($questionBankHealth['total']) }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Active</p>
                    <p class="mt-1 text-xl font-bold text-success-600">{{ number_format($questionBankHealth['status_breakdown']['active'] ?? 0) }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Image Coverage</p>
                    <p class="mt-1 text-xl font-bold {{ $questionBankHealth['image_coverage'] >= 30 ? 'text-success-600' : 'text-warning-600' }}">{{ number_format($questionBankHealth['image_coverage'], 1) }}%</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="min-h-[170px]">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">By Status</p>
                    <canvas
                        data-config="@json($statusChartConfig)"
                        data-height="160"
                        role="img"
                        aria-label="Doughnut chart showing question status distribution"
                    ></canvas>
                </div>

                <div class="min-h-[170px]">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">By Difficulty</p>
                    <canvas
                        data-config="@json($difficultyChartConfig)"
                        data-height="160"
                        role="img"
                        aria-label="Doughnut chart showing question difficulty distribution"
                    ></canvas>
                </div>
            </div>

            @if(!empty($questionBankHealth['category_counts']))
                <div class="pt-3 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-3">Top Categories</p>
                    <div class="space-y-2.5">
                        @php
                            $maxCatCount = max(array_column($questionBankHealth['category_counts'], 'count'));
                        @endphp
                        @foreach($questionBankHealth['category_counts'] as $cat)
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-600 dark:text-slate-400 w-32 truncate" title="{{ $cat['name'] }}">{{ $cat['name'] }}</span>
                                <div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                    <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $maxCatCount > 0 ? ($cat['count'] / $maxCatCount) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 w-10 text-right">{{ number_format($cat['count']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif
</x-ui.card>
