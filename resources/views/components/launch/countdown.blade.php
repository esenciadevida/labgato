@props([
    'target' => null,
    'size' => 'md',
    'reloadOnZero' => false,
])

@php
    $epochTarget = $target?->getTimestamp() ?? 0;
    $initialLeft = $epochTarget > 0 ? max(0, $epochTarget - now()->getTimestamp()) : 0;

    $boxClass = match ($size) {
        'lg' => 'flex-1 min-w-0 px-3 py-4 sm:px-5 sm:py-6 sm:min-w-[4.5rem] sm:max-w-[6.5rem] rounded-2xl sm:rounded-3xl',
        default => 'flex-1 min-w-0 px-2.5 py-3 sm:px-4 sm:py-4 sm:min-w-[3.5rem] sm:max-w-[5rem] rounded-xl sm:rounded-2xl',
    };
    $digitClass = match ($size) {
        'lg' => 'text-2xl sm:text-4xl md:text-5xl',
        default => 'text-xl sm:text-2xl',
    };
    $labelClass = match ($size) {
        'lg' => 'text-[9px] sm:text-[11px]',
        default => 'text-[8px] sm:text-[9px]',
    };

    // Each expression derives purely from the locally-ticking `left` value,
    // which starts at a server-computed number of seconds — the browser
    // clock is never consulted.
    $units = [
        ['label' => 'Days', 'expr' => "Math.floor(left / 86400)"],
        ['label' => 'Hours', 'expr' => "Math.floor((left % 86400) / 3600)"],
        ['label' => 'Minutes', 'expr' => "Math.floor((left % 3600) / 60)"],
        ['label' => 'Seconds', 'expr' => "left % 60"],
    ];
@endphp

<div data-initial-left="{{ $initialLeft }}"
     x-data="{ left: {{ $initialLeft }} }"
     x-init="if (left > 0) { const tick = setInterval(() => { left--; if (left <= 0) { clearInterval(tick); @if($reloadOnZero) setTimeout(() => window.location.reload(), 1500); @endif } }, 1000); }"
     role="timer"
     aria-label="{{ $epochTarget > 0 ? 'Countdown to launch' : 'Launch date to be announced' }}"
     {{ $attributes->merge(['class' => 'inline-flex flex-col items-center']) }}
>
    @if($epochTarget > 0)
        <div class="flex w-full max-w-md items-stretch justify-center gap-1.5 sm:max-w-none sm:gap-2.5" aria-live="off">
            @foreach($units as $unit)
                <div class="relative isolate overflow-hidden border border-white/10 bg-gradient-to-b from-white/[0.14] via-white/[0.06] to-white/[0.02] shadow-[0_18px_40px_-12px_rgb(0_0_0/0.55),inset_0_1px_0_0_rgb(255_255_255/0.14)] backdrop-blur-md {{ $boxClass }}">
                    <div class="pointer-events-none absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-white/50 to-transparent" aria-hidden="true"></div>
                    <div class="pointer-events-none absolute -top-1/2 left-1/2 h-full w-[140%] -translate-x-1/2 rounded-[100%] bg-primary-400/10 blur-2xl" aria-hidden="true"></div>

                    <span class="relative block bg-gradient-to-b from-white via-white to-white/60 bg-clip-text font-display font-bold tabular-nums text-transparent [text-shadow:none] {{ $digitClass }}"
                          x-text="String({{ $unit['expr'] }}).padStart(2, '0')">
                    </span>
                    <span class="relative mt-1 block font-semibold uppercase tracking-[0.22em] text-white/45 {{ $labelClass }}">{{ $unit['label'] }}</span>
                </div>

                @if(! $loop->last)
                    <div class="flex flex-col items-center justify-center gap-1.5" aria-hidden="true">
                        <span class="h-1 w-1 animate-pulse rounded-full bg-white/30 [animation-delay:0ms]"></span>
                        <span class="h-1 w-1 animate-pulse rounded-full bg-white/30 [animation-delay:600ms]"></span>
                    </div>
                @endif
            @endforeach
        </div>
        <p x-show="left <= 0" x-cloak
           class="mt-5 inline-flex items-center gap-2 rounded-full border border-success-400/30 bg-success-500/15 px-6 py-2.5 font-display text-sm font-semibold uppercase tracking-[0.2em] text-success-300 shadow-[0_0_30px_-6px_rgb(52_211_153/0.5)] backdrop-blur">
            <span class="h-1.5 w-1.5 rounded-full bg-success-300"></span>
            We are live!
        </p>
    @else
        <p class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-6 py-3 font-display text-sm font-semibold uppercase tracking-[0.18em] text-white/70 backdrop-blur">
            Launch date to be announced
        </p>
    @endif
</div>
