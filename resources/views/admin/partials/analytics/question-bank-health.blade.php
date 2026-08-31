<x-ui.card title="Question Bank Health">
    @if($questionBankHealth['total'] === 0)
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No questions in the bank yet.</p>
        </div>
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total</p>
                    <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($questionBankHealth['total']) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Active</p>
                    <p class="text-xl font-semibold text-success-600">{{ number_format($questionBankHealth['status_breakdown']['active'] ?? 0) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Image Coverage</p>
                    <p class="text-xl font-semibold {{ $questionBankHealth['image_coverage'] >= 30 ? 'text-success-600' : 'text-warning-600' }}">{{ $questionBankHealth['image_coverage'] }}%</p>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div x-data="{ init() { this.createChart() } }" x-init="init()">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">By Status</p>
                    <canvas id="questionStatusChart" height="150" aria-label="Doughnut chart showing question status distribution" role="img"></canvas>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const canvas = document.getElementById('questionStatusChart');
                            if (!canvas || !window.Chart) return;
                            const ctx = canvas.getContext('2d');
                            const isDark = document.documentElement.classList.contains('dark');
                            const textColor = isDark ? '#94a3b8' : '#64748b';
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: @json($questionBankHealth['status_labels']),
                                    datasets: [{
                                        data: @json($questionBankHealth['status_values']),
                                        backgroundColor: ['#22c55e', '#3b82f6', '#94a3b8'],
                                        borderWidth: 0,
                                        hoverOffset: 4,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '60%',
                                    plugins: {
                                        legend: { position: 'bottom', labels: { color: textColor, font: { size: 11 }, padding: 12, usePointStyle: true, pointStyleWidth: 8 } }
                                    }
                                }
                            });
                        });
                    </script>
                </div>

                <div x-data="{ init() { this.createChart() } }" x-init="init()">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">By Difficulty</p>
                    <canvas id="questionDiffChart" height="150" aria-label="Doughnut chart showing question difficulty distribution" role="img"></canvas>
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const canvas = document.getElementById('questionDiffChart');
                            if (!canvas || !window.Chart) return;
                            const ctx = canvas.getContext('2d');
                            const isDark = document.documentElement.classList.contains('dark');
                            const textColor = isDark ? '#94a3b8' : '#64748b';
                            new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: @json($questionBankHealth['difficulty_labels']),
                                    datasets: [{
                                        data: @json($questionBankHealth['difficulty_values']),
                                        backgroundColor: ['#22c55e', '#f59e0b', '#ef4444'],
                                        borderWidth: 0,
                                        hoverOffset: 4,
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    cutout: '60%',
                                    plugins: {
                                        legend: { position: 'bottom', labels: { color: textColor, font: { size: 11 }, padding: 12, usePointStyle: true, pointStyleWidth: 8 } }
                                    }
                                }
                            });
                        });
                    </script>
                </div>
            </div>

            @if(!empty($questionBankHealth['category_counts']))
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">Top Categories</p>
                    <div class="space-y-2">
                        @php
                            $maxCatCount = max(array_column($questionBankHealth['category_counts'], 'count'));
                        @endphp
                        @foreach($questionBankHealth['category_counts'] as $cat)
                            <div class="flex items-center space-x-3">
                                <span class="text-xs text-slate-600 dark:text-slate-400 w-32 truncate" title="{{ $cat['name'] }}">{{ $cat['name'] }}</span>
                                <div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                                    <div class="bg-primary-500 h-2 rounded-full" style="width: {{ $maxCatCount > 0 ? ($cat['count'] / $maxCatCount) * 100 : 0 }}%"></div>
                                </div>
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400 w-10 text-right">{{ number_format($cat['count']) }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <table class="sr-only">
            <caption>Question bank health metrics</caption>
            <thead><tr><th scope="col">Metric</th><th scope="col">Value</th></tr></thead>
            <tbody>
                <tr><td>Total Questions</td><td>{{ $questionBankHealth['total'] }}</td></tr>
                <tr><td>Active Questions</td><td>{{ $questionBankHealth['status_breakdown']['active'] ?? 0 }}</td></tr>
                <tr><td>Image Coverage</td><td>{{ $questionBankHealth['image_coverage'] }}%</td></tr>
            </tbody>
        </table>
    @endif
</x-ui.card>
