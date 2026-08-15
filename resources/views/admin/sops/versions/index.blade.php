@extends('layouts.admin')

@section('title', "Version History - {$sop->name} - " . config('app.name'))

@section('header', "Version History: {$sop->name}")

@section('content')
<div class="space-y-6">

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-slate-500 dark:text-slate-400">
            Current version <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $sop->version ?: '1.0' }}</span>
        </p>
        <x-ui.button href="{{ route('admin.sops.edit', $sop) }}" variant="secondary" size="sm">
            Back to SOP
        </x-ui.button>
    </div>

    <x-ui.card>
        @if($versions->isEmpty())
            <x-ui.empty-state
                title="No versions yet"
                description="This SOP has no version snapshots yet."
            />
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-700/50">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Version</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Change summary</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Saved by</th>
                            <th scope="col" class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Saved at</th>
                            <th scope="col" class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white dark:bg-slate-800">
                        @foreach($versions as $version)
                            <tr class="{{ $version->version_number === $sop->version ? 'bg-primary-50 dark:bg-slate-700/40' : '' }}">
                                <td class="px-4 py-3 text-sm font-medium text-slate-900 dark:text-slate-100">
                                    {{ $version->version_number }}
                                    @if($version->version_number === $sop->version)
                                        <x-ui.badge variant="success">Current</x-ui.badge>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $version->change_summary ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $version->creator?->name ?? 'System' }}</td>
                                <td class="px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $version->created_at?->format('M j, Y g:i A') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2 whitespace-nowrap">
                                        <x-ui.button href="{{ route('admin.sops.versions.show', [$sop, $version]) }}" variant="secondary" size="sm">
                                            View
                                        </x-ui.button>
                                        @if($version->version_number !== $sop->version)
                                            <form method="POST" action="{{ route('admin.sops.versions.restore', [$sop, $version]) }}" data-confirm="Restore version {{ $version->version_number }}? This creates a new version from its content.">
                                                @csrf
                                                <x-ui.button type="submit" variant="danger" size="sm">
                                                    Restore
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
        @endif
    </x-ui.card>
</div>
@endsection
