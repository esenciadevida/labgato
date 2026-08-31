<x-ui.card title="User Registrations (Last 30 Days)">
    @php
        $totalRegistrations = collect($userMetrics['daily_registrations'])->sum('count');
    @endphp

    @if($totalRegistrations === 0 && $userMetrics['total_users'] === 0)
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No user data available yet.</p>
        </div>
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-3 gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">7-Day Active</p>
                    <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($userMetrics['last_7d_active']) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Onboarded</p>
                    <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ $userMetrics['onboarding_rate'] }}%</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">New (30d)</p>
                    <p class="text-xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($totalRegistrations) }}</p>
                </div>
            </div>

            <div class="relative" x-data="{ init() { this.createChart() } }" x-init="init()">
                <canvas id="userRegistrationsChart" height="160" aria-label="Line chart showing user registrations over the last 30 days" role="img"></canvas>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const canvas = document.getElementById('userRegistrationsChart');
                        if (!canvas || !window.Chart) return;
                        const ctx = canvas.getContext('2d');
                        const isDark = document.documentElement.classList.contains('dark');
                        const gridColor = isDark ? 'rgba(148,163,184,0.1)' : 'rgba(148,163,184,0.2)';
                        const textColor = isDark ? '#94a3b8' : '#64748b';
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: @json(collect($userMetrics['daily_registrations'])->pluck('date')),
                                datasets: [{
                                    label: 'Registrations',
                                    data: @json(collect($userMetrics['daily_registrations'])->pluck('count')),
                                    borderColor: '#06b6d4',
                                    backgroundColor: 'rgba(6,182,212,0.1)',
                                    fill: true,
                                    tension: 0.3,
                                    pointRadius: 0,
                                    pointHitRadius: 10,
                                    borderWidth: 2,
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

            <table class="sr-only">
                <caption>User registration data for the last 30 days</caption>
                <thead><tr><th scope="col">Date</th><th scope="col">Registrations</th></tr></thead>
                <tbody>
                    @foreach($userMetrics['daily_registrations'] as $day)
                        <tr><td>{{ $day['date'] }}</td><td>{{ $day['count'] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-ui.card>
