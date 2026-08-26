@extends('layouts.admin')

@section('title', 'Contact Messages - ' . config('app.name'))

@section('header', 'Contact Messages')

@section('content')
@can('contact-messages.view')
<div class="space-y-6">

    <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center" role="search">
        <x-ui.select name="status" aria-label="Filter by status" class="sm:w-56">
            <option value="">All statuses</option>
            @foreach(['new', 'replied', 'closed'] as $statusOption)
                <option value="{{ $statusOption }}" @selected($status === $statusOption)>
                    {{ ucfirst($statusOption) }}
                </option>
            @endforeach
        </x-ui.select>

        <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>

        @if($status !== '')
            <x-ui.button href="{{ route('admin.contact-messages.index') }}" variant="ghost" size="sm">Clear</x-ui.button>
        @endif
    </form>

    <x-ui.card>
        @if($messages->isEmpty())
            <x-ui.empty-state
                title="No contact messages"
                description="{{ $status !== '' ? 'No messages match this status.' : 'Messages submitted through the public contact form will appear here.' }}"
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Reference</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Subject</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">From</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Status</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Received</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                        @foreach($messages as $message)
                            <tr class="{{ $message->isUnread() ? 'bg-primary-50/40 dark:bg-primary-900/10' : 'bg-white dark:bg-slate-800' }}">
                                <td class="px-4 py-3 text-xs font-mono text-slate-500 dark:text-slate-400">{{ $message->reference_code }}</td>
                                <td scope="row" class="px-4 py-3 text-sm {{ $message->isUnread() ? 'font-semibold text-slate-900 dark:text-slate-100' : 'font-medium text-slate-700 dark:text-slate-200' }}">
                                    <div class="flex items-center gap-2">
                                        @if($message->isUnread())
                                            <span class="h-2 w-2 shrink-0 rounded-full bg-danger-500" aria-hidden="true"></span>
                                            <span class="text-[10px] font-bold uppercase tracking-wide text-danger-600 dark:text-danger-400">New</span>
                                        @endif
                                        <a href="{{ route('admin.contact-messages.show', $message) }}" class="hover:text-primary-600 dark:hover:text-primary-400">
                                            {{ $message->subject }}
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">
                                    <span class="block font-medium text-slate-700 dark:text-slate-200">{{ $message->name }}</span>
                                    <span class="block text-xs">{{ $message->email }}</span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <x-ui.badge :variant="match($message->status) {
                                        'new' => 'warning',
                                        'replied' => 'success',
                                        'closed' => 'default',
                                        default => 'default',
                                    }">
                                        {{ ucfirst($message->status) }}
                                    </x-ui.badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $message->created_at?->format('M j, Y g:i A') ?? '—' }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <x-ui.button
                                            href="{{ route('admin.contact-messages.show', $message) }}"
                                            variant="secondary"
                                            size="sm"
                                        >
                                            View
                                        </x-ui.button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($messages->hasPages())
                <div class="border-t border-slate-200 dark:border-slate-700 px-4 py-3">
                    {{ $messages->links() }}
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
