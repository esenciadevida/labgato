@props([
    'target' => null,
    'size' => 'md',
])

@php
    $epochTarget = $target?->getTimestamp() ?? 0;
    $initialLeft = $epochTarget > 0 ? max(0, $epochTarget - now()->getTimestamp()) : 0;

    $boxClass = match ($size) {
        'lg' => 'min-w-[4.5rem] sm:min-w-[6rem] px-3 py-4 sm:px-5 sm:py-6',
        default => 'min-w-[3.5rem] sm:min-w-[4.5rem] px-2.5 py-3 sm:px-4',
    };
    $digitClass = match ($size) {
        'lg' => 'text-3xl sm:text-5xl',
        default => 'text-xl sm:text-2xl',
    };
    $labelClass = match ($size) {
        'lg' => 'text-[10px] sm:text-xs',
        default => 'text-[9px] sm:text-[10px]',
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
     x-init="if (left > 0) { const tick = setInterval(() => { left--; if (left <= 0) clearInterval(tick); }, 1000); }"
     role="timer"
     aria-label="{{ $epochTarget > 0 ? 'Countdown to launch' : 'Launch date to be announced' }}"
     {{ $attributes->merge(['class' => 'inline-flex flex-col items-center']) }}
>
    @if($epochTarget > 0)
        <div class="flex items-stretch gap-2 sm:gap-3" aria-live="off">
            @foreach($units as $unit)
                <div class="rounded-xl border border-white/15 bg-white/5 backdrop-blur text-center shadow-card {{ $boxClass }}">
                    <span class="block font-display font-bold tabular-nums text-white {{ $digitClass }}"
                          x-text="String({{ $unit['expr'] }}).padStart(2, '0')">
                    </span>
                    <span class="mt-1 block uppercase tracking-widest text-white/50 {{ $labelClass }}">{{ $unit['label'] }}</span>
                </div>
                @if(! $loop->last)
                    <span class="self-center font-display font-bold text-white/40 {{ $digitClass }}" aria-hidden="true">:</span>
                @endif
            @endforeach
        </div>
        <p x-show="left <= 0" x-cloak
           class="mt-4 rounded-full border border-success-400/30 bg-success-500/20 px-5 py-2 font-display text-sm font-semibold uppercase tracking-widest text-success-300">
            We are live!
        </p>
    @else
        <p class="font-display text-lg font-semibold text-white/80">Launch date to be announced</p>
    @endif
</div>
