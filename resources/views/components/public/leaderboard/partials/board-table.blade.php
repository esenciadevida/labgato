@props(['rows', 'variant' => 'assessment'])

@php
    $authId = auth()->id();

    $formatTime = function (?int $seconds): string {
        if ($seconds === null) {
            return '-';
        }

        return sprintf('%d:%02d', intdiv($seconds, 60), $seconds % 60);
    };

    $podiumClasses = [
        1 => 'bg-amber-50 text-amber-800 dark:bg-amber-500/10 dark:text-amber-300',
        2 => 'bg-slate-100 text-slate-700 dark:bg-slate-500/10 dark:text-slate-300',
        3 => 'bg-orange-50 text-orange-800 dark:bg-orange-500/10 dark:text-orange-300',
    ];
@endphp

<div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
        <thead>
            <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                <th scope="col" class="px-4 py-3">Rank</th>
                <th scope="col" class="px-4 py-3">Participant</th>
                @if($variant === 'assessment')
                    <th scope="col" class="px-4 py-3">Score</th>
                    <th scope="col" class="px-4 py-3">Percent</th>
                    <th scope="col" class="px-4 py-3">Time</th>
                @else
                    <th scope="col" class="px-4 py-3">Avg Best %</th>
                    <th scope="col" class="px-4 py-3">Assessments</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse($rows as $row)
                @php $isMe = $authId !== null && (int) $row['user_id'] === (int) $authId; @endphp
                <tr
                    id="{{ $isMe ? 'me' : '' }}"
                    class="{{ $isMe
                        ? 'bg-primary-50 ring-1 ring-inset ring-primary-200 dark:bg-primary-900/20 dark:ring-primary-800'
                        : '' }}"
                >
                    <td class="whitespace-nowrap px-4 py-3">
                        @if(isset($podiumClasses[$row['rank']]))
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $podiumClasses[$row['rank']] }}">{{ $row['rank'] }}</span>
                        @else
                            <span class="text-slate-600 dark:text-slate-300">{{ $row['rank'] }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($row['anonymous'])
                                <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800 dark:text-slate-500">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </span>
                                <span class="italic text-slate-500 dark:text-slate-400">Anonymous</span>
                            @else
                                <x-public.avatar :name="$row['name']" :src="$row['photo_url']" size="sm" />
                                <span class="font-medium text-slate-800 dark:text-slate-100">{{ $row['name'] }}</span>
                            @endif

                            @if($isMe)
                                <span class="rounded-full bg-primary-100 px-2 py-0.5 text-[11px] font-semibold text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">You</span>
                            @endif
                        </div>
                    </td>

                    @if($variant === 'assessment')
                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ number_format($row['score']) }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-slate-300">{{ number_format($row['percent'], 1) }}%</td>
                        <td class="whitespace-nowrap px-4 py-3 tabular-nums text-slate-600 dark:text-slate-300">{{ $formatTime($row['time_spent']) }}</td>
                    @else
                        <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800 dark:text-slate-100">{{ $row['score'] }}%</td>
                        <td class="whitespace-nowrap px-4 py-3 text-slate-600 dark:text-slate-300">{{ $row['entries'] }}</td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-500 dark:text-slate-400">
                        No completed attempts yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
