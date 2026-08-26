@extends('layouts.admin')

@section('title', 'Knowledge Resources - ' . config('app.name'))

@section('header', 'Knowledge Resources')

@section('content')
@can('knowledge.view')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.knowledge.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center" role="search">
            <x-ui.input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search knowledge resources..."
                aria-label="Search knowledge resources"
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
        </form>

        @can('create', \App\Models\KnowledgeResource::class)
            <x-ui.button href="{{ route('admin.knowledge.create') }}">
                New Knowledge Resource
            </x-ui.button>
        @endcan
    </div>

    <x-ui.card>
        @if($knowledge->isEmpty())
            <x-ui.empty-state
                title="No knowledge resources found"
                description="{{ $search !== '' || $status !== '' || $categoryId ? 'No knowledge resources match your filters.' : 'Create your first knowledge resource to get started.' }}"
            >
                @can('create', \App\Models\KnowledgeResource::class)
                    @if($search === '' && $status === '' && ! $categoryId)
                        <x-slot:action>
                            <x-ui.button href="{{ route('admin.knowledge.create') }}">
                                New Knowledge Resource
                            </x-ui.button>
                        </x-slot:action>
                    @endif
                @endcan
            </x-ui.empty-state>
        @else
            <x-admin.listing-select :ids="$knowledge->pluck('id')->all()" class="space-y-4">
                <x-admin.bulk-actions-bar
                    type="knowledge"
                    route="{{ route('admin.bulk', ['type' => 'knowledge']) }}"
                />

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-700/50">
                            <tr>
                                <th scope="col" class="px-4 py-3 w-10">
                                    <input type="checkbox" :checked="selectAll" @change="toggleAll()" aria-label="Select all on this page" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600">
                                </th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Name</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Topic</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Difficulty</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                                <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Updated</th>
                                <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                            </tr>
                        </thead>
                    <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                        @foreach($knowledge as $resource)
                            <tr>
                                <td class="px-4 py-3"><input type="checkbox" :checked="isSelected({{ $resource->id }})" @change="toggle({{ $resource->id }})" aria-label="Select {{ $resource->name }}" class="rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:border-slate-600"></td>
                                <td scope="row" class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $resource->name }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $resource->topic_category ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $resource->difficulty_level ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.badge :variant="match($resource->status->color()) {
                                        'green' => 'success',
                                        'red' => 'danger',
                                        'yellow' => 'warning',
                                        'blue' => 'info',
                                        default => 'default',
                                    }">
                                        {{ $resource->status->label() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $resource->updated_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2 whitespace-nowrap">
                                        @include('admin.partials.row-actions', ['resource' => $resource, 'route' => 'admin.knowledge', 'label' => 'knowledge resource'])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            </x-admin.listing-select>

            @if($knowledge->hasPages())
                <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-3">
                    {{ $knowledge->links() }}
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
