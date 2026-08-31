@extends('layouts.admin')

@section('title', 'Analytics Dashboard - ' . config('app.name'))

@section('header', 'Analytics Dashboard')

@section('content')
<div class="space-y-6">
    <div>
        <h2 class="text-2xl font-bold text-slate-900 dark:text-slate-100">Analytics Dashboard</h2>
        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Real-time and historical analytics for your platform.</p>
    </div>

    @include('admin.partials.analytics.kpi-cards')

    @if(!empty($pendingItems))
        <x-ui.card title="Pending Review" class="mt-6">
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">Items awaiting publication. You can publish them directly from here.</p>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Type</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Name</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Quick Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                        @foreach($pendingItems as $item)
                            @php
                                $type = match(get_class($item)) {
                                    App\Models\Calculator::class => 'Calculator',
                                    App\Models\LaboratoryTool::class => 'Tool',
                                    App\Models\ReferenceRange::class => 'Reference Range',
                                    App\Models\Sop::class => 'SOP',
                                    App\Models\InterpretationResource::class => 'Interpretation',
                                    App\Models\KnowledgeResource::class => 'Knowledge',
                                    default => class_basename(get_class($item)),
                                };

                                $route = match(get_class($item)) {
                                    App\Models\Calculator::class => route('admin.calculators.publish', $item),
                                    App\Models\LaboratoryTool::class => route('admin.tools.publish', $item),
                                    App\Models\ReferenceRange::class => route('admin.reference-ranges.publish', $item),
                                    App\Models\Sop::class => route('admin.sops.publish', $item),
                                    App\Models\InterpretationResource::class => route('admin.interpretation.publish', $item),
                                    App\Models\KnowledgeResource::class => route('admin.knowledge.publish', $item),
                                    default => '#',
                                };
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $type }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $item->name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.badge :variant="match($item->status->color()) {
                                        'green' => 'success',
                                        'red' => 'danger',
                                        'yellow' => 'warning',
                                        'blue' => 'info',
                                        default => 'default',
                                    }">
                                        {{ $item->status->label() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    @can('update', $item)
                                        <form method="POST" action="{{ $route }}" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <x-ui.button type="submit" variant="success" size="sm">
                                                Publish
                                            </x-ui.button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('admin.partials.analytics.resource-growth')
        @include('admin.partials.analytics.user-metrics')
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('admin.partials.analytics.exam-metrics')
        @include('admin.partials.analytics.certificate-metrics')
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('admin.partials.analytics.question-bank-health')
        @include('admin.partials.analytics.ai-utilization')
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('admin.partials.analytics.search-insights')
        @include('admin.partials.analytics.activity-feed')
    </div>
</div>
@endsection
