@extends('layouts.admin')

@section('title', 'Analytics Dashboard - ' . config('app.name'))

@section('header', 'Analytics Dashboard')

@section('content')
<div class="space-y-6">
    @include('admin.partials.analytics.kpi-cards')

    <section class="bg-white rounded-xl border border-slate-200 shadow-card dark:bg-slate-800 dark:border-slate-700">
        <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Pending Review</h2>
            @if(!empty($pendingItems))
                <x-ui.badge variant="warning">{{ count($pendingItems) }} to review</x-ui.badge>
            @endif
        </div>

        @if(empty($pendingItems))
            <x-ui.empty-state title="Nothing pending" description="All resources have been published or reviewed." />
        @else
            <p class="text-sm text-slate-500 dark:text-slate-400 px-4 sm:px-6 py-3 border-b border-slate-200 dark:border-slate-700">Items awaiting publication. You can publish them directly from here.</p>
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
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
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
        @endif
    </section>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('admin.partials.analytics.resource-growth')
        @include('admin.partials.analytics.user-metrics')
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('admin.partials.analytics.question-bank-health')
        @include('admin.partials.analytics.exam-metrics')
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        @include('admin.partials.analytics.certificate-metrics')
        @include('admin.partials.analytics.ai-utilization')
    </div>

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2">
            @include('admin.partials.analytics.search-insights')
        </div>
        <div>
            @include('admin.partials.analytics.activity-feed')
        </div>
    </div>
</div>
@endsection
