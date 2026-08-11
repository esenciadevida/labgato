@extends('layouts.admin')

@section('title', 'Calculators - ' . config('app.name'))

@section('header', 'Calculators')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <x-ui.alert type="success">{{ session('success') }}</x-ui.alert>
    @endif
    @if(session('error'))
        <x-ui.alert type="error">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.calculators.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center" role="search">
            <x-ui.input
                type="search"
                name="search"
                value="{{ $search }}"
                placeholder="Search calculators..."
                aria-label="Search calculators"
            />

            <select name="status" class="rounded-md border border-neutral-300 bg-white shadow-card text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20" aria-label="Filter by status">
                <option value="">All statuses</option>
                @foreach(\App\Enums\ResourceStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>
                        {{ $statusOption->label() }}
                    </option>
                @endforeach
            </select>

            <select name="category" class="rounded-md border border-neutral-300 bg-white shadow-card text-sm focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20" aria-label="Filter by category">
                <option value="">All categories</option>
                @foreach(\App\Models\Category::query()->where('is_active', true)->orderBy('name')->get() as $category)
                    <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>

            <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>
        </form>

        @can('create', \App\Models\Calculator::class)
            <x-ui.button href="{{ route('admin.calculators.create') }}">
                New Calculator
            </x-ui.button>
        @endcan
    </div>

    <x-ui.card>
        @if($calculators->isEmpty())
            <x-ui.empty-state
                title="No calculators found"
                description="{{ $search !== '' || $status !== '' || $categoryId ? 'No calculators match your filters.' : 'Create your first calculator to get started.' }}"
            >
                @can('create', \App\Models\Calculator::class)
                    @if($search === '' && $status === '' && ! $categoryId)
                        <x-slot:action>
                            <x-ui.button href="{{ route('admin.calculators.create') }}">
                                New Calculator
                            </x-ui.button>
                        </x-slot:action>
                    @endif
                @endcan
            </x-ui.empty-state>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-neutral-200">
                    <thead class="bg-neutral-50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500">Name</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500">Categories</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500">Created</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-neutral-500">Updated</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-neutral-500">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-200 bg-white">
                        @foreach($calculators as $calculator)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-neutral-900">{{ $calculator->name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.badge :variant="match($calculator->status->color()) {
                                        'green' => 'success',
                                        'red' => 'danger',
                                        'yellow' => 'warning',
                                        'blue' => 'info',
                                        default => 'default',
                                    }">
                                        {{ $calculator->status->label() }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-500">
                                    {{ $calculator->categories->pluck('name')->implode(', ') ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-neutral-500">{{ $calculator->created_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-neutral-500">{{ $calculator->updated_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-right">
                                    @can('update', $calculator)
                                        <div class="inline-flex items-center gap-2">
                                            <x-ui.button href="{{ route('admin.calculators.edit', $calculator) }}" variant="secondary" size="sm">
                                                Edit
                                            </x-ui.button>
                                        </div>
                                    @endcan
                                    @can('delete', $calculator)
                                        <form method="POST" action="{{ route('admin.calculators.destroy', $calculator) }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <x-ui.button type="submit" variant="danger" size="sm" onclick="return confirm('Are you sure you want to delete this calculator?');">
                                                Delete
                                            </x-ui.button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($calculators->hasPages())
                <div class="border-t border-neutral-200 px-4 py-3">
                    {{ $calculators->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
@endsection
