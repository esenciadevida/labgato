@extends('layouts.admin')

@section('title', 'Certificates - ' . config('app.name'))

@section('header', 'Certificates')

@php
    $tierBadges = [
        'participation' => 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-300',
        'achievement' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
        'distinction' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    ];

    $tierLabels = [
        'participation' => 'Participation',
        'achievement' => 'Achievement',
        'distinction' => 'Distinction',
    ];

    $statusTabs = [
        '' => 'All',
        'valid' => 'Valid',
        'revoked' => 'Revoked',
    ];

    $filterUrl = fn ($tabStatus) => route('admin.certificates.index', array_filter([
        'search' => $filters['search'],
        'assessment' => $filters['assessment'],
        'tier' => $filters['tier'],
        'status' => $tabStatus ?: null,
    ]));
@endphp

@section('content')
@can('certificates.view')
<div class="space-y-6" x-data="{ selected: [], selectAll: false }" x-init="$watch('selectAll', v => { selected = v ? @js($certificates->pluck('id')->toArray()) : []; })">
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-5">
        <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Issued</p>
            <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats['total']) }}</p>
        </div>
        @foreach(['participation', 'achievement', 'distinction'] as $tier)
            <div class="rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-900">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">{{ $tierLabels[$tier] }}</p>
                <p class="mt-1 text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($stats[$tier]) }}</p>
            </div>
        @endforeach
        <div class="rounded-xl border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20">
            <p class="text-xs font-semibold uppercase tracking-wide text-red-600 dark:text-red-300">Revoked</p>
            <p class="mt-1 text-2xl font-bold text-red-700 dark:text-red-200">{{ number_format($stats['revoked']) }}</p>
        </div>
    </div>

    <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex flex-wrap gap-1.5">
            @foreach($statusTabs as $tabStatus => $label)
                <a href="{{ $filterUrl($tabStatus) }}"
                   class="rounded-lg px-3 py-1.5 text-sm font-medium transition {{ ($filters['status'] ?? '') === $tabStatus
                       ? 'bg-blue-700 text-white dark:bg-blue-600'
                       : 'border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-300 dark:hover:bg-slate-800' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.certificates.index') }}" class="flex flex-wrap items-center gap-2">
            <input type="hidden" name="status" value="{{ $filters['status'] }}">

            <x-ui.input type="search" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Search name, number..." aria-label="Search certificates" class="w-48" />

            <select name="assessment" aria-label="Filter by assessment"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <option value="">All assessments</option>
                @foreach($assessments as $assessment)
                    <option value="{{ $assessment->id }}" @selected((string) $filters['assessment'] === (string) $assessment->id)>{{ $assessment->title }}</option>
                @endforeach
            </select>

            <select name="tier" aria-label="Filter by tier"
                    class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-900 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100">
                <option value="">All tiers</option>
                @foreach($tiers as $tier)
                    <option value="{{ $tier }}" @selected($filters['tier'] === $tier)>{{ $tierLabels[$tier] }}</option>
                @endforeach
            </select>

            <button type="submit"
                    class="rounded-lg bg-blue-700 px-3 py-2 text-sm font-medium text-white hover:bg-blue-800 dark:bg-blue-600 dark:hover:bg-blue-500">
                Filter
            </button>
        </form>
    </div>

    <div x-show="selected.length > 0" x-cloak class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2.5 dark:border-amber-800 dark:bg-amber-900/20">
        <span class="text-sm font-medium text-amber-800 dark:text-amber-300" x-text="selected.length + ' selected'"></span>
        @if(Auth::user()->hasAnyPermission(['admin.full-access', 'certificates.revoke']))
            <button type="button" @click="$dispatch('open-bulk-revoke')"
                    class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20">
                Bulk Revoke
            </button>
        @endif
        <button type="button" @click="selected = []; selectAll = false" class="ml-auto text-xs text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200">Clear</button>
    </div>

    <x-ui.card class="!p-0 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800/60">
                    <tr>
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox" x-model="selectAll" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Number</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Holder</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Assessment</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Tier</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Score</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Issued</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white dark:divide-slate-800 dark:bg-slate-900">
                    @forelse($certificates as $certificate)
                        <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50">
                            <td class="w-10 px-4 py-3">
                                <input type="checkbox" value="{{ $certificate->id }}" x-model="selected" class="rounded border-slate-300 text-primary-500 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-800">
                            </td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <a href="{{ route('admin.certificates.show', $certificate) }}" class="font-mono text-xs font-medium text-primary-600 hover:text-primary-700 dark:text-primary-400">{{ $certificate->certificate_number }}</a>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $certificate->user?->name }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-200">{{ $certificate->course?->title ?? $certificate->assessment?->title }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $tierBadges[$certificate->type] }}">{{ $tierLabels[$certificate->type] }}</span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm tabular-nums text-slate-700 dark:text-slate-200">{{ number_format($certificate->score_percent, 1) }}%</td>
                            <td class="whitespace-nowrap px-4 py-3 text-sm text-slate-500 dark:text-slate-400">{{ $certificate->issued_at->format('M j, Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-3">
                                @if($certificate->isRevoked())
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-300">Revoked</span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Valid</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.certificates.show', $certificate) }}"
                                       class="rounded-lg px-2 py-1.5 text-xs font-medium text-slate-600 hover:bg-slate-100 dark:text-slate-400 dark:hover:bg-slate-800 transition-colors">View</a>
                                    @if(! $certificate->isRevoked() && Auth::user()->hasAnyPermission(['admin.full-access', 'certificates.revoke']))
                                        <button x-data @click="$dispatch('open-revoke', { id: {{ $certificate->id }}, number: '{{ $certificate->certificate_number }}' })"
                                                class="rounded-lg px-2 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors">
                                            Revoke
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                                No certificates match the current filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($certificates->hasPages())
            <div class="border-t border-slate-100 px-4 py-3 dark:border-slate-800">
                {{ $certificates->links() }}
            </div>
        @endif
    </x-ui.card>
</div>

<div
    x-data="{ open: false, id: null, number: '', init() { window.addEventListener('open-revoke', (e) => { this.id = e.detail.id; this.number = e.detail.number; this.open = true; }); } }"
    x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div class="absolute inset-0 bg-slate-950/60" @click="open = false"></div>
    <form method="POST" x-show="id" :action="'{{ url('admin/certificates') }}/' + id + '/revoke'"
          class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-panel dark:border-slate-700 dark:bg-slate-900">
        @csrf
        <h2 class="font-display text-base font-semibold text-slate-900 dark:text-white">Revoke certificate <span x-text="number"></span></h2>
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">Verification will show the certificate as revoked and downloads are blocked.</p>
        <label for="revoke-reason" class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Reason (optional)</label>
        <input id="revoke-reason" name="reason" maxlength="255"
               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" @click="open = false"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Revoke</button>
        </div>
    </form>
</div>

<div
    x-data="{
        open: false,
        init() { window.addEventListener('open-bulk-revoke', () => { this.open = true; }); },
    }"
    x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center p-4"
>
    <div class="absolute inset-0 bg-slate-950/60" @click="open = false"></div>
    <form method="POST" action="{{ route('admin.certificates.bulk-revoke') }}"
          class="relative w-full max-w-md rounded-2xl border border-slate-200 bg-white p-6 shadow-panel dark:border-slate-700 dark:bg-slate-900">
        @csrf
        <h2 class="font-display text-base font-semibold text-slate-900 dark:text-white">Bulk revoke <span x-text="selected.length"></span> certificate(s)</h2>
        <p class="mt-1.5 text-sm text-slate-600 dark:text-slate-300">This will revoke all selected valid certificates.</p>
        <template x-for="id in selected" :key="id">
            <input type="hidden" name="certificate_ids[]" :value="id">
        </template>
        <label for="bulk-revoke-reason" class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Reason (optional)</label>
        <input id="bulk-revoke-reason" name="reason" maxlength="255"
               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" @click="open = false"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Revoke All</button>
        </div>
    </form>
</div>
@endcan
@endsection
