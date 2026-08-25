@extends('layouts.public')

@section('title', 'My Certificates')

@section('content')
<section class="py-10">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <header>
            <h1 class="font-display text-3xl font-bold text-slate-900 dark:text-slate-100">My Certificates</h1>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
                Verifiable certificates for completed assessments. Each certificate carries a QR code that can be scanned to verify it publicly.
            </p>
        </header>

        @if(session('error'))
            <div class="mt-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300">
                {{ session('error') }}
            </div>
        @endif

        <div class="mt-8 space-y-4">
            @forelse($certificates as $certificate)
                <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-5 shadow-sm sm:flex-row sm:items-center sm:justify-between dark:border-slate-700 dark:bg-slate-900">
                    <div class="flex items-start gap-4">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-primary-50 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                            </svg>
                        </span>
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <h2 class="font-semibold text-slate-900 dark:text-white">{{ $certificate->assessment?->title }}</h2>
                                <span class="rounded-full bg-primary-50 px-2 py-0.5 text-[11px] font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">{{ ucfirst($certificate->type) }}</span>
                                @if($certificate->isRevoked())
                                    <span class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300">Revoked</span>
                                @endif
                            </div>
                            <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                                {{ number_format($certificate->score_percent, 1) }}% &middot; Issued {{ $certificate->issued_at->format('M j, Y') }} &middot;
                                <span class="font-mono">{{ $certificate->certificate_number }}</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex shrink-0 gap-2">
                        @unless($certificate->isRevoked())
                            <a href="{{ route('certificates.download', $certificate->attempt) }}"
                               class="inline-flex min-h-[40px] items-center gap-1.5 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Download
                            </a>
                            <a href="{{ route('certificates.share-redirect', $certificate->attempt) }}"
                               class="inline-flex min-h-[40px] items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                                Share
                            </a>
                        @endunless
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center dark:border-slate-700 dark:bg-slate-900">
                    <p class="font-medium text-slate-700 dark:text-slate-200">No certificates yet</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Complete an assessment - your certificate appears here once results are revealed.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
