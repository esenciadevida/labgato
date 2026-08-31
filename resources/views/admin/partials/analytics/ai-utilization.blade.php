<x-ui.card title="AI Utilization (Last 7 Days)" class="flex flex-col">
    @if($aiUtilization['total_requests'] === 0)
        <x-ui.empty-state title="No AI usage data yet" description="AI usage will appear here as features are used." />
    @else
        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Requests</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($aiUtilization['total_requests']) }}</p>
                </div>
                <div class="rounded-lg bg-slate-50 p-3 dark:bg-slate-700/40">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Total Tokens</p>
                    <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-slate-100">{{ number_format($aiUtilization['total_tokens']) }}</p>
                </div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Success Rate</p>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ number_format($aiUtilization['success_rate'], 1) }}%</p>
                </div>
                <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2.5" role="progressbar" aria-valuenow="{{ $aiUtilization['success_rate'] }}" aria-valuemin="0" aria-valuemax="100" aria-label="AI success rate: {{ $aiUtilization['success_rate'] }}%">
                    <div
                        class="h-2.5 rounded-full transition-all duration-500 {{ $aiUtilization['success_rate'] >= 90 ? 'bg-success-500' : ($aiUtilization['success_rate'] >= 70 ? 'bg-warning-500' : 'bg-danger-500') }}"
                        style="width: {{ $aiUtilization['success_rate'] }}%"
                    ></div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-3 border-t border-slate-200 dark:border-slate-700">
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
    @endif
</x-ui.card>
