@extends('layouts.public')

@section('title', $pageTitle)

@section('content')
<section class="py-14">
    <div class="mx-auto max-w-xl px-4 sm:px-6 lg:px-8">
        <div class="overflow-hidden rounded-2xl bg-gradient-to-br from-blue-950 via-blue-900 to-slate-900 shadow-panel ring-1 ring-white/10">
            <div class="border-b border-white/10 px-8 py-5 text-center">
                <p class="font-display text-lg font-bold tracking-[0.25em] text-white">LabGOTO</p>
                <p class="mt-0.5 text-[10px] uppercase tracking-[0.3em] text-amber-400">Verified Result</p>
            </div>

            <div class="px-8 py-10 text-center">
                <p class="text-sm font-medium text-slate-300">{{ $holderName }} scored</p>
                <p class="mt-2 font-display text-6xl font-bold text-white">{{ number_format($certificate->score_percent, 1) }}<span class="text-3xl text-slate-300">%</span></p>
                <p class="mt-3 text-sm text-slate-300">
                    in the <span class="font-semibold text-white">{{ $certificate->assessment->title }}</span>
                </p>

                @if($certificate->rank_at_issue !== null)
                    <p class="mt-4 inline-flex items-center gap-1.5 rounded-full bg-white/10 px-4 py-1.5 text-sm font-semibold text-amber-300 ring-1 ring-white/15">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Rank #{{ $certificate->rank_at_issue }} &middot; Top {{ $topPercent }}%
                    </p>
                @endif

                <p class="mt-8 break-all text-xs text-slate-400">
                    Verify at
                    <a href="{{ $certificate->verifyUrl() }}" class="font-medium text-amber-300 underline underline-offset-2 hover:text-amber-200">{{ $certificate->verifyUrl() }}</a>
                </p>
            </div>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-2 sm:grid-cols-4">
            @foreach(['whatsapp' => 'WhatsApp', 'x' => 'X', 'linkedin' => 'LinkedIn', 'facebook' => 'Facebook'] as $network => $label)
                <a href="{{ $intents[$network] }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex min-h-[44px] items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:border-slate-600 dark:bg-slate-900 dark:text-slate-200 dark:hover:bg-slate-800">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <footer class="mt-8 text-center">
            <p class="text-xs leading-relaxed text-slate-400 dark:text-slate-500">
                LabGOTO certificates recognize performance in LabGOTO assessments and are NOT ASCP certifications nor official ASCP credentials.
            </p>
        </footer>
    </div>
</section>
@endsection
