@php
    $pageTitle = 'Launch Waiting Room';
    $metaNoindex = true;
@endphp

@extends('layouts.member')

@section('content')
<section class="py-10">
    <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
        <div class="text-center">
            <h1 class="font-display text-3xl font-black tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">
                Welcome, {{ $user->profile?->display_name ?: $user->name }}
            </h1>
            <p class="mx-auto mt-3 max-w-md text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                You are registered for the Labgoto launch. Sit tight - everything unlocks the moment we go live.
            </p>
        </div>

        <div class="relative isolate mt-10 overflow-hidden rounded-3xl border border-primary-800/60 bg-gradient-to-b from-primary-950 via-primary-900 to-slate-950 p-6 shadow-panel sm:p-10">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="absolute -top-24 left-1/2 h-72 w-[36rem] -translate-x-1/2 rounded-full bg-primary-500/20 blur-3xl"></div>
                <div class="absolute bottom-0 right-0 h-48 w-48 translate-x-1/4 rounded-full bg-cyan-400/10 blur-3xl"></div>
                <div class="absolute bottom-8 left-0 h-44 w-44 -translate-x-1/3 rounded-full bg-emerald-400/10 blur-3xl"></div>
            </div>

            <div class="relative">
                <div class="flex justify-center">
                    <x-launch.countdown :target="$settings->launchAtCarbon()" size="lg" />
                </div>

                <div class="mt-10 w-full max-w-lg mx-auto">
                    <div class="relative isolate overflow-hidden rounded-2xl border border-white/10 bg-white/[0.06] p-6 text-left shadow-[0_18px_50px_-20px_rgb(0_0_0/0.6)] backdrop-blur-md sm:p-7">
                        <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/40 to-transparent" aria-hidden="true"></div>

                        <h2 class="font-display text-sm font-bold uppercase tracking-[0.2em] text-white/60">ASCPI Challenge</h2>

                        <dl class="mt-5 space-y-4">
                            <div class="flex items-center justify-between gap-4 border-b border-white/5 pb-4">
                                <dt class="text-sm text-slate-400">Status</dt>
                                <dd>
                                    @if($settings->challenge_open)
                                        <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-success-300">
                                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Registered
                                        </span>
                                    @else
                                        <span class="text-sm font-medium text-slate-400">Challenge opens soon.</span>
                                    @endif
                                </dd>
                            </div>
                            <div class="flex items-center justify-between gap-4">
                                <dt class="text-sm text-slate-400">Result</dt>
                                <dd>
                                    <span class="inline-flex items-center gap-1.5 text-sm font-semibold text-white/60">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                        Locked
                                    </span>
                                </dd>
                            </div>
                        </dl>

                        <p class="mt-6 border-t border-white/5 pt-4 text-sm leading-relaxed text-slate-400">
                            Your result is revealed when Labgoto officially launches.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
