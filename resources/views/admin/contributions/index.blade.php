@extends('layouts.admin')

@section('title', 'Contribution Review Queue - ' . config('app.name'))

@section('header', 'Contribution Review Queue')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <form method="GET" action="{{ route('admin.contributions.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center" role="search">
            <x-ui.select name="type" aria-label="Filter by type" class="sm:w-56">
                <option value="">All types</option>
                @foreach(\App\Enums\ContributionType::cases() as $typeOption)
                    <option value="{{ $typeOption->value }}" @selected($type === $typeOption->value)>
                        {{ $typeOption->label() }}
                    </option>
                @endforeach
            </x-ui.select>

            <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>
        </form>
    </div>

    <x-ui.card>
        @if($contributions->isEmpty())
            <x-ui.empty-state
                title="No pending contributions"
                description="{{ $type !== '' ? 'No pending contributions match this type.' : 'Contributions submitted by members will appear here for review.' }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Title</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Type</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Contributor</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Submitted</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                        @foreach($contributions as $entry)
                            <tr>
                                <td scope="row" class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">{{ $entry->name }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-medium text-primary-700 border border-primary-600/20 dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-600/30">
                                        {{ $entry->type_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                    {{ $entry->contributor?->name ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $entry->created_at?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.badge variant="warning">{{ $entry->status->label() }}</x-ui.badge>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <x-ui.button
                                            href="{{ route('admin.contributions.review', ['type' => $entry->type, 'id' => $entry->id]) }}"
                                            variant="secondary"
                                            size="sm"
                                        >
                                            Review
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($contributions->hasPages())
                <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-3">
                    {{ $contributions->links() }}
                </div>
            @endif
        @endif
    </x-ui.card>
</div>
@endsection
