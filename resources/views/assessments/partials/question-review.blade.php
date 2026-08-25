<div class="rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-4 dark:border-slate-800">
        <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
            <span class="rounded-full bg-slate-100 px-3 py-1 font-semibold text-slate-700 dark:bg-slate-800 dark:text-slate-300">Question {{ $index }}</span>

            @if($item['subject'])
                <span class="rounded-full bg-blue-50 px-3 py-1 uppercase tracking-wide text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">{{ $item['subject'] }}</span>
            @endif

            <span class="rounded-full bg-slate-50 px-3 py-1 capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-400">{{ $item['difficulty'] }}</span>
        </div>

        @if($item['is_correct'])
            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                Correct
            </span>
        @else
            <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-3 py-1 text-xs font-semibold text-red-700 dark:bg-red-900/40 dark:text-red-300">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                Incorrect
            </span>
        @endif
    </div>

    <div class="px-5 py-4">
        <p class="whitespace-pre-line text-sm leading-relaxed text-slate-900 dark:text-slate-100">{{ $item['stem'] }}</p>

        @if($item['image_url'])
            <figure class="mt-4 overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                <img src="{{ $item['image_url'] }}" alt="{{ $item['image_alt'] ?? 'Question image' }}"
                     class="max-h-80 w-full object-contain" loading="lazy">
            </figure>
            @if($item['attribution'] ?? null)
                <p class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    {{ $item['attribution']['source'] }} &middot; {{ $item['attribution']['license'] }}
                    @if($item['attribution']['attribution'])
                        &middot; {{ $item['attribution']['attribution'] }}
                    @endif
                    @if($item['attribution']['source_url'])
                        &middot; <a href="{{ $item['attribution']['source_url'] }}" target="_blank" rel="noopener noreferrer" class="underline">Source</a>
                    @endif
                </p>
            @endif
        @endif

        <ul class="mt-4 space-y-2">
            @foreach($item['options'] as $option)
                @php
                    $classes = match (true) {
                        $option['is_correct'] => 'border-emerald-500 bg-emerald-50 dark:border-emerald-600 dark:bg-emerald-900/20',
                        $option['is_user_selection'] => 'border-red-400 bg-red-50 dark:border-red-600 dark:bg-red-900/20',
                        default => 'border-slate-200 bg-white dark:border-slate-700 dark:bg-slate-900',
                    };
                @endphp
                <li class="flex items-start gap-3 rounded-lg border p-3 text-sm {{ $classes }}">
                    <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full border border-slate-300 text-xs font-bold text-slate-600 dark:border-slate-600 dark:text-slate-300">{{ $option['letter'] }}</span>

                    <span class="flex-1 whitespace-pre-line leading-relaxed text-slate-700 dark:text-slate-300">{{ $option['text'] }}</span>

                    @if($option['is_correct'])
                        <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">
                            <svg class="inline h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            Correct
                        </span>
                    @elseif($option['is_user_selection'])
                        <span class="text-xs font-semibold text-red-700 dark:text-red-400">Your answer</span>
                    @endif
                </li>
            @endforeach
        </ul>

        <div class="mt-3 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs">
            <span class="{{ $item['is_correct'] ? 'font-semibold text-emerald-700 dark:text-emerald-400' : 'text-red-700 dark:text-red-400' }}">
                Your answer:
                @if($item['your_option'])
                    {{ $item['your_option']['letter'] }}. {{ $item['your_option']['text'] }}
                @else
                    Unanswered
                @endif
            </span>

            @if(! $item['is_correct'] && $item['correct_option'])
                <span class="font-semibold text-emerald-700 dark:text-emerald-400">
                    Correct answer: {{ $item['correct_option']['letter'] }}. {{ $item['correct_option']['text'] }}
                </span>
            @endif

            @if($item['flagged'])
                <span class="inline-flex items-center gap-1 font-medium text-amber-700 dark:text-amber-400">
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="currentColor"><path d="M4 3a1 1 0 00-1 1v17a1 1 0 102 0v-6h13.2c.9 0 1.4-1 .8-1.7L15.5 9l3.5-4.3c.6-.7.1-1.7-.8-1.7H4z"/></svg>
                    Flagged for review
                </span>
            @endif
        </div>

        @if($item['explanation'])
            <details class="group mt-4">
                <summary class="cursor-pointer list-none text-sm font-semibold text-blue-700 transition hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">
                    Explanation
                    <span class="ml-1 inline-block transition-transform group-open:rotate-90">&rsaquo;</span>
                </summary>

                <div class="mt-3 whitespace-pre-line rounded-lg bg-slate-50 p-4 text-sm leading-relaxed text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                    {{ $item['explanation'] }}
                </div>
            </details>
        @endif
    </div>
</div>
