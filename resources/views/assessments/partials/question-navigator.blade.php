<aside class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Question Navigator</h2>

    <div x-ref="tablist" class="mt-4 grid grid-cols-6 gap-2 lg:grid-cols-4" role="tablist" aria-label="Question navigator"
         @keydown.arrow-left.prevent="focusRelative(-1)"
         @keydown.arrow-right.prevent="focusRelative(1)"
         @keydown.home.prevent="focusRelative('home')"
         @keydown.end.prevent="focusRelative('end')">
        @foreach($questions as $index => $question)
            @php
                $questionId = $question['id'];
                $answered = isset($answersMap[$questionId]);
                $flagged = isset($flagsMap[$questionId]);
            @endphp
            <button type="button"
                    role="tab"
                    @click="goto({{ $index }})"
                    :tabindex="current === {{ $index }} ? 0 : -1"
                    :aria-selected="current === {{ $index }} ? 'true' : 'false'"
                    :aria-label="'Question ' + ({{ $index + 1 }}) + (!!flags[{{ $questionId }}] ? ', flagged for review' : '') + (!!answers[{{ $questionId }}] ? ', answered' : '')"
                    class="relative flex h-10 w-full items-center justify-center rounded-lg border text-sm font-semibold transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                    :class="current === {{ $index }}
                        ? 'ring-2 ring-blue-700 dark:ring-blue-400 ' + tileClass({{ $questionId }})
                        : tileClass({{ $questionId }})">
                {{ $index + 1 }}

                @if($flagged)
                    <span class="absolute right-1 top-1 h-2 w-2 rounded-full bg-amber-500" aria-hidden="true"></span>
                @endif
            </button>
        @endforeach
    </div>

    <div class="mt-4 space-y-1.5 text-xs text-slate-600 dark:text-slate-300">
        <p class="flex items-center gap-2">
            <span class="h-3 w-3 rounded border border-emerald-600 bg-emerald-100 dark:bg-emerald-800" aria-hidden="true"></span>
            Answered
        </p>
        <p class="flex items-center gap-2">
            <span class="h-3 w-3 rounded border border-amber-500 bg-amber-100 dark:bg-amber-800" aria-hidden="true"></span>
            Flagged for review
        </p>
        <p class="flex items-center gap-2">
            <span class="h-3 w-3 rounded border border-slate-300 bg-slate-100 dark:border-slate-600 dark:bg-slate-800" aria-hidden="true"></span>
            Unanswered
        </p>
    </div>

    <dl class="mt-4 border-t border-slate-200 pt-4 text-sm text-slate-600 dark:border-slate-700 dark:text-slate-300">
        <dt class="sr-only">Summary</dt>
        <dd>
            Answered <span class="font-semibold" x-text="answeredCount()">{{ count($answersMap) }}</span>
            &middot; Flagged <span class="font-semibold" x-text="flaggedCount()">{{ count($flagsMap) }}</span>
            &middot; Unanswered <span class="font-semibold" x-text="unansweredCount()">{{ $questions->count() - count($answersMap) }}</span>
        </dd>
    </dl>

    <button type="button"
            @click="openConfirm()"
            :disabled="submitting"
            class="mt-5 w-full rounded-lg bg-emerald-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-emerald-800 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:bg-emerald-600 dark:hover:bg-emerald-500">
        Submit Examination
    </button>
</aside>
