<x-ui.card title="Certificate Tracking">
    @if($certificateMetrics['total_issued'] === 0)
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
            </svg>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No certificates issued yet.</p>
        </div>
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Avg Score</p>
                    <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $certificateMetrics['avg_score'] }}%</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Revoked</p>
                    <p class="text-xl font-semibold {{ $certificateMetrics['total_revoked'] > 0 ? 'text-danger-600' : 'text-slate-900 dark:text-slate-100' }}">{{ number_format($certificateMetrics['total_revoked']) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Issued (30d)</p>
                    @php
                        $recentIssuance = collect($certificateMetrics['issuance_trend'])->sum('count');
                    @endphp
                    <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($recentIssuance) }}</p>
                </div>
            </div>

            <div x-data="{ init() { this.createChart() } }" x-init="init()">
                <canvas id="certIssuanceChart" height="140" aria-label="Bar chart showing certificate issuances over the last 30 days" role="img"></canvas>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const canvas = document.getElementById('certIssuanceChart');
                        if (!canvas || !window.Chart) return;
                        const ctx = canvas.getContext('2d');
                        const isDark = document.documentElement.classList.contains('dark');
                        const gridColor = isDark ? 'rgba(148,163,184,0.1)' : 'rgba(148,163,184,0.2)';
                        const textColor = isDark ? '#94a3b8' : '#64748b';
                        const trendData = @json($certificateMetrics['issuance_trend']);
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: trendData.map(d => d.date),
                                datasets: [{
                                    label: 'Certificates',
                                    data: trendData.map(d => d.count),
                                    backgroundColor: 'rgba(245,158,11,0.6)',
                                    borderColor: '#f59e0b',
                                    borderWidth: 1,
                                    borderRadius: 3,
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: { legend: { display: false } },
                                scales: {
                                    x: { grid: { display: false }, ticks: { color: textColor, maxTicksLimit: 7, font: { size: 11 } } },
                                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1, font: { size: 11 } } }
                                }
                            }
                        });
                    });
                </script>
            </div>

            @if(!empty($certificateMetrics['type_distribution']))
                <div class="pt-2 border-t border-slate-200 dark:border-slate-700">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">By Type</p>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach($certificateMetrics['type_distribution'] as $index => $count)
                            <div class="text-center p-2 rounded-lg bg-slate-50 dark:bg-slate-800">
                                <p class="text-lg font-semibold text-slate-900 dark:text-slate-100">{{ number_format($count) }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $certificateMetrics['type_labels'][$index] ?? 'Unknown' }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <table class="sr-only">
            <caption>Certificate metrics</caption>
            <thead><tr><th scope="col">Metric</th><th scope="col">Value</th></tr></thead>
            <tbody>
                <tr><td>Total Issued</td><td>{{ $certificateMetrics['total_issued'] }}</td></tr>
                <tr><td>Revoked</td><td>{{ $certificateMetrics['total_revoked'] }}</td></tr>
                <tr><td>Average Score</td><td>{{ $certificateMetrics['avg_score'] }}%</td></tr>
            </tbody>
        </table>
    @endif
</x-ui.card>
