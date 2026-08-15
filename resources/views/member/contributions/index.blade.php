@extends('layouts.public')

@section('title', 'My Contributions - ' . config('app.name'))

@section('content')
<section class="py-12">
    <div class="mx-auto max-w-6xl px-4 sm:px-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="font-display text-2xl font-bold text-slate-900 dark:text-slate-100">
                    My Contributions
                </h1>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Share your laboratory knowledge with the community.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <x-ui.button href="{{ route('account.contributions.create', ['type' => 'sop']) }}" size="sm">New SOP</x-ui.button>
                <x-ui.button href="{{ route('account.contributions.create', ['type' => 'knowledge']) }}" size="sm">New Knowledge</x-ui.button>
                <x-ui.button href="{{ route('account.contributions.create', ['type' => 'interpretation']) }}" size="sm">New Interpretation</x-ui.button>
            </div>
        </div>

        <form method="GET" action="{{ route('account.contributions.index') }}" class="mt-8 flex flex-col gap-2 sm:flex-row sm:items-center" role="search">
            <x-ui.select name="type" aria-label="Filter by type" class="sm:w-56">
                <option value="">All types</option>
                @foreach(\App\Enums\ContributionType::cases() as $typeOption)
                    <option value="{{ $typeOption->value }}" @selected($type === $typeOption->value)>
                        {{ $typeOption->label() }}
                    </option>
                @endforeach
            </x-ui.select>

            <x-ui.select name="status" aria-label="Filter by status" class="sm:w-56">
                <option value="">All statuses</option>
                @foreach(\App\Enums\ResourceStatus::cases() as $statusOption)
                    <option value="{{ $statusOption->value }}" @selected($status === $statusOption->value)>
                        {{ $statusOption->label() }}
                    </option>
                @endforeach
            </x-ui.select>

            <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>
        </form>

        <div class="mt-6">
            <x-ui.card>
                @if($contributions->isEmpty())
                    <x-ui.empty-state
                        title="No contributions yet"
                        description="{{ $type !== '' || $status !== '' ? 'No contributions match your filters.' : 'Create your first contribution to share laboratory knowledge with the community.' }}"
                    >
                        @if($type === '' && $status === '')
                            <x-slot:action>
                                <x-ui.button href="{{ route('account.contributions.create', ['type' => 'sop']) }}">
                                    New Contribution
                                </x-ui.button>
                            </x-slot:action>
                        @endif
                    </x-ui.empty-state>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                            <thead class="bg-slate-50 dark:bg-slate-700/50">
                                <tr>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Title</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Type</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                                    <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Updated</th>
                                    <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                                @foreach($contributions as $entry)
                                    <tr>
                                        <td scope="row" class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                                            <a href="{{ route('account.contributions.show', ['type' => $entry->type, 'id' => $entry->id]) }}" class="hover:text-primary-600 dark:hover:text-primary-400">
                                                {{ $entry->name }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <span class="inline-flex items-center rounded-full bg-primary-50 px-2.5 py-0.5 text-xs font-medium text-primary-700 border border-primary-600/20 dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-600/30">
                                                {{ $entry->type_label }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-sm">
                                            <x-ui.badge :variant="match($entry->status->color()) {
                                                'green' => 'success',
                                                'red' => 'danger',
                                                'yellow' => 'warning',
                                                'blue' => 'info',
                                                default => 'default',
                                            }">
                                                {{ $entry->status->label() }}
                                            </x-ui.badge>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $entry->updated_at?->format('M j, Y') ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap items-center justify-end gap-2 whitespace-nowrap">
                                                <x-ui.button
                                                    href="{{ route('account.contributions.show', ['type' => $entry->type, 'id' => $entry->id]) }}"
                                                    variant="secondary"
                                                    size="sm"
                                                >
                                                    View
                                                </x-ui.button>

                                                @if($entry->status->canMemberEdit())
                                                    <x-ui.button
                                                        href="{{ route('account.contributions.edit', ['type' => $entry->type, 'id' => $entry->id]) }}"
                                                        variant="secondary"
                                                        size="sm"
                                                    >
                                                        Edit
                                                    </x-ui.button>
                                                @endif

                                                @if($entry->status->canMemberWithdraw())
                                                    <form method="POST" action="{{ route('account.contributions.withdraw', ['type' => $entry->type, 'id' => $entry->id]) }}">
                                                        @csrf
                                                        <x-ui.button type="submit" variant="secondary" size="sm">
                                                            Withdraw
                                                        </x-ui.button>
                                                    </form>
                                                @endif

                                                @if($entry->status->canMemberDelete())
                                                    <form method="POST" action="{{ route('account.contributions.destroy', ['type' => $entry->type, 'id' => $entry->id]) }}"
                                                          data-confirm="Are you sure you want to delete this contribution? This cannot be undone.">
                                                        @csrf
                                                        @method('DELETE')
                                                        <x-ui.button type="submit" variant="danger" size="sm">
                                                            Delete
                                                        </x-ui.button>
                                                    </form>
                                                @endif
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
    </div>
</section>
@endsection
