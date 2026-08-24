@extends('layouts.public')

@section('title', 'Result - ' . config('app.name'))

@php
    $certificateType = app(\App\Services\CertificateService::class)->eligibleType($attempt);
@endphp

@section('content')
<main class="mx-auto max-w-4xl px-4 py-10">
    @if(! $released)
        <div class="rounded-2xl border border-slate-200 bg-white p-12 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 dark:bg-slate-800">
                <svg class="h-8 w-8 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" /></svg>
            </div>

            <h1 class="mt-6 text-xl font-bold text-slate-900 dark:text-slate-100">Your result is securely recorded.</h1>

            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">{{ $attempt->assessment?->title }}</p>
            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Submitted {{ $attempt->submitted_at?->format('d M Y, H:i') }}</p>

            <div class="mx-auto mt-8 max-w-sm rounded-xl border border-slate-200 bg-slate-50 p-5 dark:border-slate-700 dark:bg-slate-800">
                <p class="text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    Results will be revealed when LabGOTO officially launches.
                </p>
            </div>
        </div>
    @else
        <div class="overflow-hidden rounded-2xl bg-gradient-to-r from-[#0b2545] to-blue-800 p-8 text-white shadow-lg dark:from-slate-900 dark:to-blue-900">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-200 dark:text-blue-300">{{ $attempt->assessment?->title }}</p>

            <div class="mt-4 flex flex-wrap items-end gap-x-8 gap-y-3">
                <span class="text-5xl font-bold tabular-nums">{{ $summary['percent'] }}%</span>
                <span class="pb-1 text-lg font-medium text-blue-100 dark:text-slate-300">{{ $summary['score'] }} / {{ $summary['total'] }} correct</span>
            </div>

            <div class="mt-5 flex flex-wrap items-center gap-3 text-sm">
                <span class="rounded-full bg-white/15 px-3 py-1 font-semibold">Rank #{{ $summary['rank'] }}</span>
                <span class="rounded-full bg-white/15 px-3 py-1">Top {{ $summary['topPercent'] }}%</span>
                @if($summary['integrity_status'] === 'high_risk')
                    <span class="rounded-full bg-red-500/30 px-3 py-1 text-red-100">Flagged for integrity review</span>
                @elseif($summary['integrity_status'] === 'review')
                    <span class="rounded-full bg-amber-400/20 px-3 py-1 text-amber-100">Integrity review</span>
                @endif
            </div>
        </div>

        @if($certificateType !== null)
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('certificates.download', $attempt) }}"
                   class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download Certificate
                </a>
                <a href="{{ route('certificates.share-redirect', $attempt) }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-5 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 12.684a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"/>
                    </svg>
                    Share My Result
                </a>
            </div>
        @endif

        <section class="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Subject Performance</h2>

            <div class="mt-5 space-y-4">
                @foreach($breakdown as $subject)
                    @php
                        $barColor = $subject['percent'] >= 70
                            ? 'bg-emerald-500'
                            : ($subject['percent'] >= 40 ? 'bg-amber-500' : 'bg-red-500');
                        $textColor = $subject['percent'] >= 70
                            ? 'text-emerald-700 dark:text-emerald-400'
                            : ($subject['percent'] >= 40 ? 'text-amber-700 dark:text-amber-400' : 'text-red-700 dark:text-red-400');
                    @endphp
                    <div>
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium text-slate-700 dark:text-slate-300">{{ $subject['name'] }}</span>
                            <span class="tabular-nums {{ $textColor }}">{{ $subject['correct'] }}/{{ $subject['total'] }} ({{ $subject['percent'] }}%)</span>
                        </div>

                        <div class="mt-1.5 h-2 w-full overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                            <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $subject['percent'] }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="mt-8 space-y-4">
            <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Question Review</h2>

            @foreach($reviewItems as $item)
                @include('assessments.partials.question-review', ['item' => $item, 'index' => $loop->iteration])
            @endforeach
        </section>

        <div class="mt-10 flex flex-wrap gap-3 border-t border-slate-200 pt-6 dark:border-slate-700">
            <a href="{{ route('assessments.my-results') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 px-6 py-3 text-sm font-semibold text-slate-700 transition hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                Back to My Results
            </a>
        </div>
    @endif
</main>
@endsection
