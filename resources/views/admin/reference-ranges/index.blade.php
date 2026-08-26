@extends('layouts.admin')

@section('title', 'Reference Ranges - ' . config('app.name'))

@section('header', 'Reference Ranges')

@section('content')
@can('reference-ranges.view')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.reference-ranges.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center" role="search">
            <x-ui.input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search reference ranges..."
                aria-label="Search reference ranges"
            />

            <select name="status" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card dark:border-slate-700 dark:bg-slate-800 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach(\App\Enums\ResourceStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>
                        {{ $statusOption->label() }}
                    </option>
                @endforeach
            </select>

            <select name="category" class="rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-sm shadow-card dark:border-slate-700 dark:bg-slate-800 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/25" aria-label="Filter by category">
                <option value="">All categories</option>
                @foreach(\App\Models\Category::query()->where('is_active', true)->orderBy('name')->get() as $category)
                    <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>

        @can('import', 'admin.reference-ranges.import')
            <x-ui.button href="{{ route('admin.reference-ranges.import') }}" class="ml-3">
                Import CSV
            </x-ui.button>
        @endcan
        </form>

        @can('create', \App\Models\ReferenceRange::class)
            <x-ui.button href="{{ route('admin.reference-ranges.create') }}">
                New Reference Range
            </x-ui.button>
        @endcan
    </div>

    <x-ui.card>
        @if($referenceRanges->isEmpty())
            <x-ui.empty-state
                title="No reference ranges found"
                description="{{ $search !== '' || $status !== '' || $categoryId ? 'No reference ranges match your filters.' : 'Create your first reference range to get started.' }}"
            >
                @can('create', \App\Models\ReferenceRange::class)
                    @if($search === '' && $status === '' && ! $categoryId)
                        <x-slot:action>
                            <x-ui.button href="{{ route('admin.reference-ranges.create') }}">
                                New Reference Range
                            </x-ui.button>
                        </x-slot:action>
                    @endif
                @endcan
            </x-ui.empty-state>
        @else
            <x-admin.listing-select :ids="$referenceRanges->pluck('id')->all()" class="space-y-4">
                <x-admin.bulk-actions-bar
                    type="reference-range"
                    route="{{ route('admin.bulk', ['type' => 'reference-range']) }}"
                />

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th scope="col" class="px-4 py-3 w-10">
                                    <input type="checkbox" :checked="selectAll" @change="toggleAll()" aria-label="Select all on this page" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Name</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Analyte</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Demographics</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Range</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Updated</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                            </tr>
                        </thead>
                    <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                        @foreach($referenceRanges as $referenceRange)
                            <tr>
                                <td class="px-4 py-3"><input type="checkbox" :checked="isSelected({{ $referenceRange->id }})" @change="toggle({{ $referenceRange->id }})" aria-label="Select {{ $referenceRange->name }}" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600"></td>
                                <td scope="row" class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $referenceRange->name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $referenceRange->analyte_name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                    @php
                                        $parts = collect([$referenceRange->sex !== 'any' ? ucfirst($referenceRange->sex) : null, $referenceRange->population_group, $referenceRange->age_min !== null && $referenceRange->age_max !== null ? $referenceRange->age_min.'-'.$referenceRange->age_max.'y' : null])->filter()->values();
                                    @endphp
                                    {{ $parts->isEmpty() ? '—' : $parts->implode(' · ') }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                    @if($referenceRange->lower_limit !== null || $referenceRange->upper_limit !== null)
                                        <span class="font-medium text-slate-900 dark:text-slate-100">
                                            {{ $referenceRange->lower_limit !== null ? rtrim(rtrim(number_format($referenceRange->lower_limit, 4, '.', ''), '0'), '.') : '≤' }}
                                            – {{ $referenceRange->upper_limit !== null ? rtrim(rtrim(number_format($referenceRange->upper_limit, 4, '.', ''), '0'), '.') : '≥' }}
                                        </span>
                                        <span class="text-slate-400">{{ $referenceRange->unit }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.badge :variant="match($referenceRange->status->color()) {
                                        'green' => 'success',
                                        'red' => 'danger',
                                        'yellow' => 'warning',
                                        'blue' => 'info',
                                        default => 'default',
                                    }">
                                        {{ $referenceRange->status->label() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $referenceRange->updated_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2">
                                        @include('admin.partials.row-actions', ['resource' => $referenceRange, 'route' => 'admin.reference-ranges', 'label' => 'reference range'])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            </x-admin.listing-select>

            @if($referenceRanges->hasPages())
                <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-3">
                    {{ $referenceRanges->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
@else
    <div class="rounded-xl border border-dashed border-slate-300 bg-white p-10 text-center shadow-card dark:border-slate-700 dark:bg-slate-800">
        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">You do not have permission to view this page.</p>
    </div>
@endcan
@endsection
