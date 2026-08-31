<x-ui.card title="Exam Performance">
    @if($examMetrics['total_attempts'] === 0)
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No exam data available yet.</p>
        </div>
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Average Score</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ $examMetrics['avg_score'] }}%</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Pass Rate</p>
                    <p class="text-2xl font-semibold {{ $examMetrics['pass_rate'] >= 70 ? 'text-success-600' : 'text-danger-600' }}">{{ $examMetrics['pass_rate'] }}%</p>
                </div>
            </div>

            <div x-data="{ init() { this.createChart() } }" x-init="init()">
                <canvas id="examScoreChart" height="140" aria-label="Line chart showing average exam scores over the last 30 days" role="img"></canvas>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const canvas = document.getElementById('examScoreChart');
                        if (!canvas || !window.Chart) return;
                        const ctx = canvas.getContext('2d');
                        const isDark = document.documentElement.classList.contains('dark');
                        const gridColor = isDark ? 'rgba(148,163,184,0.1)' : 'rgba(148,163,184,0.2)';
                        const textColor = isDark ? '#94a3b8' : '#64748b';
                        const trendData = @json($examMetrics['score_trend']);
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: trendData.map(d => d.date),
                                datasets: [{
                                    label: 'Avg Score %',
                                    data: trendData.map(d => d.avg_score),
                                    borderColor: '#8b5cf6',
                                    backgroundColor: 'rgba(139,92,246,0.1)',
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 0,
                                    pointHitRadius: 10,
                                    borderWidth: 2,
                                    spanGaps: true,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { display: false }, ticks: { color: textColor, maxTicksLimit: 7, font: { size: 11 } } },
                                    y: { min: 0, max: 100, grid: { color: gridColor }, ticks: { color: textColor, font: { size: 11 }, callback: v => v + '%' } }
                                }
                            }
                        });
                    });
                </script>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-200 dark:border-slate-700">
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
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-1">Integrity Flags</p>
                    <div class="flex space-x-3">
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

        <table class="sr-only">
            <caption>Exam performance metrics</caption>
            <thead><tr><th scope="col">Metric</th><th scope="col">Value</th></tr></thead>
            <tbody>
                <tr><td>Total Attempts</td><td>{{ $examMetrics['total_attempts'] }}</td></tr>
                <tr><td>Average Score</td><td>{{ $examMetrics['avg_score'] }}%</td></tr>
                <tr><td>Pass Rate</td><td>{{ $examMetrics['pass_rate'] }}%</td></tr>
                <tr><td>Official Exams</td><td>{{ $examMetrics['official_count'] }}</td></tr>
                <tr><td>Practice Exams</td><td>{{ $examMetrics['practice_count'] }}</td></tr>
            </tbody>
        </table>
    @endif
</x-ui.card>
