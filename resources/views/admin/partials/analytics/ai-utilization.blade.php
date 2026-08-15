<x-ui.card title="AI Utilization (Last 7 Days)">
    @if($aiUtilization['total_requests'] === 0)
        <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No AI usage data available yet.</p>
        </div>
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Requests</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($aiUtilization['total_requests']) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Tokens</p>
                    <p class="text-2xl font-semibold text-slate-900 dark:text-slate-100">{{ number_format($aiUtilization['total_tokens']) }}</p>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Success Rate</p>
                    <p class="text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $aiUtilization['success_rate'] }}%</p>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5" role="progressbar" aria-valuenow="{{ $aiUtilization['success_rate'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="AI success rate: {{ $aiUtilization['success_rate'] }}%">
                    <div
                        class="h-2.5 rounded-full transition-all duration-500 {{ $aiUtilization['success_rate'] >= 90 ? 'bg-success-500' : ($aiUtilization['success_rate'] >= 70 ? 'bg-warning-500' : 'bg-danger-500') }}"
                        style="width: {{ $aiUtilization['success_rate'] }}%"
                    ></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-slate-200 dark:border-slate-700">
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Successful</p>
                    <p class="text-lg font-semibold text-success-600">{{ number_format($aiUtilization['successful_requests']) }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Failed / Filtered</p>
                    <p class="text-lg font-semibold text-danger-600">{{ number_format($aiUtilization['failed_requests']) }}</p>
                </div>
            </div>
        </div>

        <table class="sr-only">
            <caption>AI utilization metrics for the last 7 days</caption>
            <thead>
                <tr>
                    <th scope="col">Metric</th>
                    <th scope="col">Value</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Total Requests</td>
                    <td>{{ $aiUtilization['total_requests'] }}</td>
                </tr>
                <tr>
                    <td>Successful Requests</td>
                    <td>{{ $aiUtilization['successful_requests'] }}</td>
                </tr>
                <tr>
                    <td>Failed Requests</td>
                    <td>{{ $aiUtilization['failed_requests'] }}</td>
                </tr>
                <tr>
                    <td>Success Rate</td>
                    <td>{{ $aiUtilization['success_rate'] }}%</td>
                </tr>
                <tr>
                    <td>Total Tokens</td>
                    <td>{{ $aiUtilization['total_tokens'] }}</td>
                </tr>
            </tbody>
        </table>
    @endif
</x-ui.card>
