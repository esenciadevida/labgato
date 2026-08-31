<x-ui.card title="Resource Growth (Last 30 Days)">
    @php
        $totalGrowth = collect($resourceGrowth)->sum('count');
    @endphp

    @if($totalGrowth === 0)
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No resource growth data available yet.</p>
        </div>
    @else
        <div x-data="{ init() { this.createChart() } }" x-init="init()">
            <canvas id="resourceGrowthChart" height="180" aria-label="Line chart showing {{ number_format($totalGrowth) }} new resources created over the last 30 days" role="img"></canvas>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const canvas = document.getElementById('resourceGrowthChart');
                    if (!canvas || !window.Chart) return;
                    const ctx = canvas.getContext('2d');
                    const isDark = document.documentElement.classList.contains('dark');
                    const gridColor = isDark ? 'rgba(148,163,184,0.1)' : 'rgba(148,163,184,0.2)';
                    const textColor = isDark ? '#94a3b8' : '#64748b';
                    new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: @json(collect($resourceGrowth)->pluck('date')),
                            datasets: [{
                                label: 'New Resources',
                                data: @json(collect($resourceGrowth)->pluck('count')),
                                backgroundColor: 'rgba(99,102,241,0.5)',
                                borderColor: '#6366f1',
                                borderWidth: 1,
                                borderRadius: 3,
                                maxBarThickness: 16,
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: { legend: { display: false } },
                            scales: {
                                x: { grid: { display: false }, ticks: { color: textColor, maxTicksLimit: 10, font: { size: 11 } } },
                                y: { beginAtZero: true, grid: { color: gridColor }, ticks: { color: textColor, stepSize: 1, font: { size: 11 } } }
                            }
                        }
                    });
                });
            </script>
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
