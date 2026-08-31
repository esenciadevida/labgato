<x-ui.card title="Recent Activity">
    @if($recentActivity->isEmpty())
        <div class="text-center py-8">
            <svg class="mx-auto h-12 w-12 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">No recent activity recorded.</p>
        </div>
    @else
        <div class="flow-root">
            <ul class="-mb-8" role="list">
                @foreach($recentActivity as $log)
                    @php
                        $actionParts = explode('.', $log->action);
                        $verb = $actionParts[0] ?? '';
                        $iconColor = match(true) {
                            in_array($verb, ['create', 'store']) => 'bg-success-400',
                            in_array($verb, ['update', 'edit', 'publish']) => 'bg-info-400',
                            in_array($verb, ['delete', 'destroy', 'archive', 'revoke']) => 'bg-danger-400',
                            $verb === 'login' => 'bg-violet-400',
                            default => 'bg-slate-400',
                        };
                    @endphp
                    <li>
                        <div class="relative pb-8">
                            @if(!$loop->last)
                                <span class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-slate-200 dark:bg-slate-700" aria-hidden="true"></span>
                            @endif
                            <div class="relative flex items-start space-x-3">
                                <div class="relative">
                                    <div class="h-8 w-8 rounded-full {{ $iconColor }} flex items-center justify-center ring-8 ring-white dark:ring-slate-900">
                                        <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div>
                                        <div class="text-sm">
                                            <span class="font-medium text-slate-900 dark:text-slate-100">{{ $log->user->name ?? 'System' }}</span>
                                            <span class="text-slate-500 dark:text-slate-400"> {{ strtolower(str_replace('.', ' ', $log->action)) }}</span>
                                        </div>
                                        <p class="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                                            {{ $log->resource_type ? class_basename($log->resource_type) : '' }}
                                            @if($log->resource_id)
                                                #{{ $log->resource_id }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="mt-2 text-xs text-slate-400 dark:text-slate-500">
                                        <time datetime="{{ $log->created_at->toIso8601String() }}">{{ $log->created_at->diffForHumans() }}</time>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                @endforeach
            </ul>
        </div>

        <table class="sr-only">
            <caption>Recent admin activity log</caption>
            <thead><tr><th scope="col">User</th><th scope="col">Action</th><th scope="col">Resource</th><th scope="col">Time</th></tr></thead>
            <tbody>
                @foreach($recentActivity as $log)
                    <tr>
                        <td>{{ $log->user->name ?? 'System' }}</td>
                        <td>{{ $log->action_label }}</td>
                        <td>{{ $log->resource_type ? class_basename($log->resource_type) . ' #' . $log->resource_id : '-' }}</td>
                        <td>{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</x-ui.card>
