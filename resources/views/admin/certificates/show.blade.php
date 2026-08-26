@extends('layouts.admin')

@section('title', 'Certificate ' . $certificate->certificate_number . ' - ' . config('app.name'))

@section('header', 'Certificate Details')

@section('content')
@can('certificates.view')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.certificates.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to certificates
        </a>
        <div class="flex items-center gap-2">
            @if(! $certificate->isRevoked() && Auth::user()->hasAnyPermission(['admin.full-access', 'certificates.download']))
                <a href="{{ route('admin.certificates.download', $certificate) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Download PDF
                </a>
            @endif
            @if(! $certificate->isRevoked() && Auth::user()->hasAnyPermission(['admin.full-access', 'certificates.manage']))
                <form method="POST" action="{{ route('admin.certificates.regenerate', $certificate) }}" class="inline">
                    @csrf
                    <button type="submit"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Regenerate PDF
                    </button>
                </form>
            @endif
            @if(! $certificate->isRevoked() && Auth::user()->hasAnyPermission(['admin.full-access', 'certificates.revoke']))
                <button x-data @click="$dispatch('open-revoke', { id: {{ $certificate->id }}, number: '{{ $certificate->certificate_number }}' })"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-red-200 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-800 dark:text-red-400 dark:hover:bg-red-900/20 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    Revoke
                </button>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <x-ui.card>
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-full text-xl font-bold
                        {{ match($certificate->type) {
                            'distinction' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                            'achievement' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                            default => 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-300',
                        } }}">
                        {{ strtoupper(substr($certificate->type, 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="font-display text-lg font-semibold text-slate-900 dark:text-white">{{ $certificate->certificate_number }}</h2>
                            @if($certificate->isRevoked())
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800 dark:bg-red-900/40 dark:text-red-300">Revoked</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300">Valid</span>
                            @endif
                        </div>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ ucfirst($certificate->type) }} Certificate</p>
                    </div>
                </div>
            </x-ui.card>

            <x-ui.card title="PDF Preview">
                <div class="flex justify-center">
                    @if($certificate->pdf_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($certificate->pdf_path))
                        <iframe src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($certificate->pdf_path) }}"
                                class="w-full rounded-lg border border-slate-200 dark:border-slate-700"
                                style="height: 500px;" title="Certificate PDF"></iframe>
                    @else
                        <div class="flex flex-col items-center gap-3 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-10 text-center dark:border-slate-700 dark:bg-slate-800/50">
                            <svg class="w-10 h-10 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            <p class="text-sm text-slate-500 dark:text-slate-400">PDF not yet generated. Download or access the certificate to generate it.</p>
                        </div>
                    @endif
                </div>
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Certificate Info">
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Certificate Number</dt>
                        <dd class="mt-0.5 font-mono text-sm font-semibold text-slate-900 dark:text-white">{{ $certificate->certificate_number }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Verification Code</dt>
                        <dd class="mt-0.5 font-mono text-sm text-slate-900 dark:text-white">{{ $certificate->verification_code }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Type</dt>
                        <dd class="mt-0.5">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium
                                {{ match($certificate->type) {
                                    'distinction' => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                                    'achievement' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                    default => 'bg-slate-100 text-slate-700 dark:bg-slate-700/40 dark:text-slate-300',
                                } }}">{{ $certificate->label() }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Score</dt>
                        <dd class="mt-0.5 text-slate-900 dark:text-white">{{ number_format($certificate->score_percent, 1) }}%</dd>
                    </div>
                    @if($certificate->rank_at_issue !== null)
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Rank</dt>
                            <dd class="mt-0.5 text-slate-900 dark:text-white">#{{ $certificate->rank_at_issue }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Issued</dt>
                        <dd class="mt-0.5 text-slate-900 dark:text-white">{{ $certificate->issued_at->format('F j, Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 dark:text-slate-400">Verification URL</dt>
                        <dd class="mt-0.5">
                            <a href="{{ $certificate->verifyUrl() }}" target="_blank" class="text-primary-600 hover:text-primary-700 dark:text-primary-400 break-all">{{ $certificate->verifyUrl() }}</a>
                        </dd>
                    </div>
                    @if($certificate->isRevoked())
                        <div class="rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20">
                            <dt class="text-xs font-semibold uppercase text-red-600 dark:text-red-400">Revoked</dt>
                            <dd class="mt-1 text-sm text-red-700 dark:text-red-300">{{ $certificate->revoked_at->format('F j, Y g:i A') }}</dd>
                            @if($certificate->revoke_reason)
                                <dd class="mt-1 text-sm text-red-600 dark:text-red-400">Reason: {{ $certificate->revoke_reason }}</dd>
                            @endif
                        </div>
                    @endif
                </dl>
            </x-ui.card>

            <x-ui.card title="Holder">
                <div class="flex items-center gap-3">
                    @if($certificate->user)
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary-100 text-sm font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                            {{ strtoupper(substr($holderName, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-slate-900 dark:text-white truncate">{{ $holderName }}</p>
                            <p class="text-xs text-slate-500 dark:text-slate-400 truncate">{{ $certificate->user->email }}</p>
                        </div>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">User record unavailable</p>
                    @endif
                </div>
            </x-ui.card>

            <x-ui.card title="Assessment">
                @if($certificate->assessment)
                    <div class="space-y-2 text-sm">
                        <p class="font-medium text-slate-900 dark:text-white">{{ $certificate->assessment->title }}</p>
                        @if($certificate->assessment->pass_percentage !== null)
                            <p class="text-slate-500 dark:text-slate-400">Pass threshold: {{ $certificate->assessment->pass_percentage }}%</p>
                        @endif
                    </div>
                @else
                    <p class="text-sm text-slate-500 dark:text-slate-400">Assessment record unavailable.</p>
                @endif
            </x-ui.card>
        </div>
    </div>
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
        <label for="revoke-reason-show" class="mt-4 block text-sm font-medium text-slate-700 dark:text-slate-200">Reason (optional)</label>
        <input id="revoke-reason-show" name="reason" maxlength="255"
               class="mt-1 w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/30 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        <div class="mt-5 flex justify-end gap-2">
            <button type="button" @click="open = false"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700">Cancel</button>
            <button type="submit" class="rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Revoke</button>
        </div>
    </form>
</div>
@endcan
@endsection
