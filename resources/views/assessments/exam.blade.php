<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $attempt->assessment->title }} - Examination</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('assets/LabGoTo_favicon.svg') }}">
    <x-brand.fonts />
    <x-theme-script />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .no-select {
            user-select: none;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body class="no-select bg-slate-100 text-slate-900 antialiased dark:bg-slate-950 dark:text-slate-100"
      x-data="examComponent"
      @contextmenu.prevent
      @copy.prevent
      @cut.prevent
      @paste.prevent
      @dragstart.prevent
      @drop.prevent>

@php
    $examConfig = [
        'attemptId' => $attempt->id,
        'total' => $questions->count(),
        'remainingSeconds' => $remainingSeconds,
        'endsAt' => $attempt->ends_at?->getTimestamp() ?? 0,
        'serverNow' => (int) now()->timestamp,
        'answers' => (object) $answersMap,
        'flags' => (object) array_map(fn () => true, $flagsMap),
        'urls' => [
            'save' => route('exams.answers.save', $attempt),
            'flag' => route('exams.flag', $attempt),
            'submit' => route('exams.submit', $attempt),
            'submitted' => route('exams.submitted', $attempt),
            'integrity' => route('exams.integrity.store', $attempt),
        'cancel' => route('exams.cancel', $attempt),
        'intro' => route('assessments.show', $attempt->assessment),
    ],
    'autoCancelThreshold' => (int) config('labgoto.integrity.auto_cancel_threshold', 3),
];
@endphp

<script type="application/json" data-exam-config>
@json($examConfig)
</script>

<script type="application/json" data-questions-config>
@json($questions->values()->all())
</script>

<header class="sticky top-0 z-30 border-b border-slate-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3">
        <div class="flex min-w-0 items-center gap-3">
            <span class="hidden h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-blue-800 text-sm font-bold text-white sm:flex dark:bg-blue-700">LG</span>
            <h1 class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $attempt->assessment->title }}</h1>
        </div>

        <p class="text-sm font-medium text-slate-600 dark:text-slate-300">
            Question <span x-text="current + 1" class="font-bold text-blue-800 dark:text-blue-400">1</span>
            of <span x-text="total">1</span>
        </p>

        <div id="exam-timer"
             data-initial-left="{{ $remainingSeconds }}"
             class="rounded-lg border px-4 py-2 font-mono text-lg font-bold tabular-nums transition-colors"
             :class="timerClass"
             x-text="clock">
        </div>

        <button type="button" @click="quitOpen = true"
                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-slate-900 disabled:cursor-not-allowed disabled:opacity-50 dark:border-slate-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white"
                :disabled="submitting">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            <span class="hidden sm:inline">Quit</span>
        </button>
    </div>
</header>

@if($attempt->mode === 'practice')
    <div class="border-b border-amber-300 bg-amber-50 px-4 py-2 text-center text-xs font-semibold uppercase tracking-wide text-amber-800 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-300">
        Practice Run &mdash; not ranked. This attempt does not affect your official score, the leaderboard, or certificates.
    </div>
@endif

<main class="mx-auto grid max-w-7xl gap-6 px-4 py-6 lg:grid-cols-[3fr_1fr]">
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-900">
        <template x-for="(question, index) in questions" :key="question.id">
            <div x-show="index === current" class="min-h-[16rem]">
                <div class="flex flex-wrap items-center gap-2 text-xs font-medium">
                    <span class="rounded-full bg-blue-100 px-3 py-1 uppercase tracking-wide text-blue-800 dark:bg-blue-900/40 dark:text-blue-300" x-text="question.subject"></span>
                    <span class="rounded-full bg-slate-100 px-3 py-1 capitalize text-slate-600 dark:bg-slate-800 dark:text-slate-300" x-text="question.difficulty"></span>
                    <span class="rounded-full bg-emerald-100 px-3 py-1 capitalize text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300" x-text="typeLabel(question.type)"></span>
                </div>

                <div class="mt-4 whitespace-pre-line text-base leading-relaxed text-slate-900 dark:text-slate-100">
                    <p x-show="question.type === 'case'" class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">Case Scenario</p>
                    <p x-text="question.stem"></p>
                    <p x-show="question.case_text" class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm dark:border-slate-700 dark:bg-slate-800" x-text="question.case_text"></p>
                </div>

                <figure x-show="question.image_url" class="mt-4 overflow-hidden rounded-lg border border-slate-200 dark:border-slate-700">
                    <img :src="question.image_url || ''" :alt="question.image_alt || 'Question image'"
                         class="max-h-[28rem] w-full object-contain" draggable="false">
                </figure>

                <div x-show="question.attribution" class="mt-2 text-xs leading-relaxed text-slate-500 dark:text-slate-400">
                    <template x-if="question.attribution">
                        <span>
                            <span x-text="question.attribution.source"></span>
                            &middot;&nbsp;<span x-text="question.attribution.license"></span>
                            <template x-if="question.attribution.attribution">
                                <span>&middot;&nbsp;<span x-text="question.attribution.attribution"></span></span>
                            </template>
                            <template x-if="question.attribution.source_url">
                                &middot;&nbsp;<a :href="question.attribution.source_url" target="_blank" rel="noopener noreferrer" class="underline">Source</a>
                            </template>
                        </span>
                    </template>
                </div>

                <div class="mt-6 space-y-3" role="radiogroup" :aria-label="'Question ' + (index + 1) + ' options'">
                    <template x-for="option in question.options" :key="option.id">
                        <button type="button" role="radio"
                                @click="select(question.id, option.id)"
                                :aria-checked="answers[question.id] === option.id ? 'true' : 'false'"
                                class="flex w-full items-start gap-3 rounded-lg border p-4 text-left text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                :class="answers[question.id] === option.id
                                    ? 'border-blue-700 bg-blue-50 ring-2 ring-blue-700 dark:border-blue-500 dark:bg-blue-900/30 dark:ring-blue-500'
                                    : 'border-slate-200 bg-white hover:border-blue-400 hover:bg-blue-50/50 dark:border-slate-700 dark:bg-slate-900 dark:hover:border-blue-600 dark:hover:bg-blue-900/20'">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border font-bold"
                                  :class="answers[question.id] === option.id
                                      ? 'border-blue-700 bg-blue-800 text-white dark:border-blue-500 dark:bg-blue-600'
                                      : 'border-slate-300 text-slate-600 dark:border-slate-600 dark:text-slate-300'"
                                  x-text="option.letter"></span>
                            <span class="whitespace-pre-line leading-relaxed" x-text="option.text"></span>
                        </button>
                    </template>
                </div>

                <div class="mt-8 flex items-center justify-between border-t border-slate-200 pt-5 dark:border-slate-700">
                    <button type="button" @click="prev()" :disabled="current === 0"
                            class="inline-flex items-center gap-2 rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 transition enabled:hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-600 dark:text-slate-200 dark:enabled:hover:bg-slate-800">
                        &larr; Previous
                    </button>

                    <button type="button" @click="toggleFlag(questions[current].id)"
                            class="inline-flex items-center gap-2 rounded-lg border px-4 py-2.5 text-sm font-medium transition"
                            :class="flags[questions[current].id]
                                ? 'border-amber-400 bg-amber-100 text-amber-800 dark:border-amber-500 dark:bg-amber-900/40 dark:text-amber-300'
                                : 'border-slate-300 text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800'">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor"><path d="M4 3a1 1 0 00-1 1v17a1 1 0 102 0v-6h13.2c.9 0 1.4-1 .8-1.7L15.5 9l3.5-4.3c.6-.7.1-1.7-.8-1.7H4z"/></svg>
                        <span x-text="flags[questions[current]?.id] ? 'Flagged' : 'Flag for review'"></span>
                    </button>

                    <button type="button" @click="next()" :disabled="current >= total - 1"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-800 px-4 py-2.5 text-sm font-medium text-white transition enabled:hover:bg-blue-900 disabled:cursor-not-allowed disabled:opacity-40 dark:bg-blue-700 dark:enabled:hover:bg-blue-600">
                        Next &rarr;
                    </button>
                </div>
            </div>
        </template>
    </section>

    @include('assessments.partials.question-navigator')
</main>

<div x-show="confirmOpen" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true">
    <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900" @click.outside="confirmOpen = false">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Submit your examination?</h2>

        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
            You have answered <strong x-text="answeredCount()"></strong> of <strong x-text="total"></strong>.
            <span x-text="unansweredCount()"></span> unanswered.
            <span x-text="flaggedCount()"></span> flagged for review.
        </p>

        <p x-show="unansweredCount() > 0" class="mt-3 rounded-lg border border-amber-300 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-600 dark:bg-amber-900/30 dark:text-amber-300">
            Warning: unanswered questions are scored as incorrect and cannot be changed after submission.
        </p>

        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">Once submitted, this attempt is final.</p>

        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="confirmOpen = false"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                Cancel
            </button>
            <button type="button" @click="doSubmit(false)" :disabled="submitting"
                    class="rounded-lg bg-blue-800 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-900 disabled:opacity-50 dark:bg-blue-700 dark:hover:bg-blue-600">
                Yes, Submit
            </button>
        </div>
    </div>
</div>

<div x-show="timeUpOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 p-4">
    <div class="w-full max-w-sm rounded-xl border border-slate-200 bg-white p-6 text-center shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Time is up</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">Your examination was submitted automatically.</p>
    </div>
</div>

<div x-show="quitOpen" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 p-4" role="dialog" aria-modal="true">
    <div class="w-full max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-xl dark:border-slate-700 dark:bg-slate-900" @click.outside="quitOpen = false">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Quit this exam?</h2>
        <p class="mt-3 text-sm text-slate-600 dark:text-slate-300">
            Your current attempt will be cancelled and marked as abandoned. You can start a fresh, randomized exam afterward.
        </p>
        <div class="mt-6 flex justify-end gap-3">
            <button type="button" @click="quitOpen = false"
                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 dark:border-slate-600 dark:text-slate-200 dark:hover:bg-slate-800">
                Keep Going
            </button>
            <button type="button" @click="doQuit()" :disabled="submitting"
                    class="rounded-lg bg-danger-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-danger-700 disabled:opacity-50 dark:bg-danger-500 dark:hover:bg-danger-600">
                Quit Exam
            </button>
        </div>
    </div>
</div>

<div x-show="cancelledOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 p-4">
    <div class="w-full max-w-sm rounded-xl border border-slate-200 bg-white p-6 text-center shadow-xl dark:border-slate-700 dark:bg-slate-900">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Attempt cancelled</h2>
        <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
            This attempt was cancelled because the exam window was left. A fresh, randomized exam will start automatically.
        </p>
    </div>
</div>

<div x-show="warnOpen" x-cloak class="fixed inset-0 z-40 flex items-center justify-center bg-slate-900/60 p-4" role="alertdialog" aria-modal="true">
    <div class="w-full max-w-md rounded-xl border border-amber-300 bg-white p-6 shadow-xl dark:border-amber-600 dark:bg-slate-900" @click.outside="warnOpen = false">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Stay in the exam window</h2>
        <p class="mt-3 text-sm leading-relaxed text-slate-600 dark:text-slate-300" x-text="warnMessage"></p>
        <div class="mt-6 flex justify-end">
            <button type="button" @click="warnOpen = false"
                    class="rounded-lg bg-amber-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-amber-700">
                I'll stay focused
            </button>
        </div>
    </div>
</div>

<script>
function examComponent() {
    var config = JSON.parse(document.querySelector('script[data-exam-config]').textContent);
    var questions = JSON.parse(document.querySelector('script[data-questions-config]').textContent);
    var storageKey = 'exam-' + config.attemptId + '-idx';
    var timerInterval = null;

    return {
        questions: questions,
        total: config.total,
        answers: config.answers,
        flags: config.flags,
        current: parseInt(localStorage.getItem(storageKey) || '0', 10),
        remaining: config.remainingSeconds,
        submitting: false,
        confirmOpen: false,
        timeUpOpen: false,
        quitOpen: false,
        cancelledOpen: false,
        cancelledRedirect: config.urls.intro || '/',
        eventQueue: [],
        lastEventAt: {},
        clockSkew: (config.serverNow * 1000) - Date.now(),
        endsAtMs: (config.endsAt || 0) * 1000,
        autoCancelThreshold: config.autoCancelThreshold || 3,
        leaves: 0,
        warnOpen: false,
        warnMessage: '',

        init: function () {
            var self = this;

            if (!(this.current >= 0 && this.current < this.total)) {
                this.current = 0;
            }

            this.requestFullscreen();
            this.installDeterrents();
            this.installIntegrityCapture();

            timerInterval = setInterval(function () { self.tick(); }, 1000);
            setInterval(function () { self.flushEvents(); }, 5000);

            window.addEventListener('pagehide', function () {
                self.flushEvents(true);
            });

            window.addEventListener('beforeunload', function (event) {
                if (!self.submitting) {
                    event.preventDefault();
                    event.returnValue = '';
                }
            });
        },

        get clock() {
            var s = Math.max(0, this.remaining);
            var h = String(Math.floor(s / 3600)).padStart(2, '0');
            var m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
            var sec = String(s % 60).padStart(2, '0');

            return h + ':' + m + ':' + sec;
        },

        get timerClass() {
            if (this.submitting || this.timeUpOpen) {
                return 'border-red-300 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300';
            }

            if (this.remaining < 60) {
                return 'animate-pulse border-red-400 bg-red-600 text-white';
            }

            if (this.remaining < 300) {
                return 'border-red-300 bg-red-50 text-red-700 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300';
            }

            if (this.remaining < 600) {
                return 'border-amber-300 bg-amber-50 text-amber-700 dark:border-amber-700 dark:bg-amber-900/30 dark:text-amber-300';
            }

            return 'border-slate-200 bg-white text-slate-900 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100';
        },

        answeredCount: function () {
            var answers = this.answers;
            var questions = this.questions;

            return questions.filter(function (question) {
                return !!answers[question.id];
            }).length;
        },

        unansweredCount: function () {
            return this.total - this.answeredCount();
        },

        tileClass: function (questionId) {
            if (this.flags[questionId]) {
                return 'border-amber-500 bg-amber-100 text-amber-900 dark:border-amber-500 dark:bg-amber-800/60 dark:text-amber-200';
            }

            if (this.answers[questionId]) {
                return 'border-emerald-600 bg-emerald-100 text-emerald-900 dark:border-emerald-600 dark:bg-emerald-800/60 dark:text-emerald-200';
            }

            return 'border-slate-300 bg-slate-100 text-slate-600 dark:border-slate-600 dark:bg-slate-800 dark:text-slate-300';
        },

        typeLabel: function (type) {
            var labels = {
                text: 'Multiple Choice',
                image: 'Image Question',
                text_image: 'Text & Image',
                calculation: 'Calculation',
                case: 'Case-Based',
            };

            return labels[type] || 'Question';
        },

        flaggedCount: function () {
            var flags = this.flags;

            return Object.keys(flags).filter(function (k) { return flags[k]; }).length;
        },

        select: function (questionId, optionId) {
            var self = this;
            var previous = this.answers[questionId] || null;

            this.answers[questionId] = optionId;

            fetch(config.urls.save, {
                method: 'POST',
                headers: this.headers(),
                body: JSON.stringify({ question_id: questionId, option_id: optionId }),
            }).then(function (response) {
                if (response.status === 422) {
                    response.json().then(function (data) {
                        if (data.expired) {
                            self.finishLocally();

                            return;
                        }

                        delete self.answers[questionId];

                        if (previous) {
                            self.answers[questionId] = previous;
                        }
                    });

                    return;
                }

                // Authoritative timer sync straight from the server.
                response.json().then(function (data) {
                    if (typeof data.remaining_seconds === 'number') {
                        self.remaining = data.remaining_seconds;
                    }
                }).catch(function () {});
            }).catch(function () {});
        },

        toggleFlag: function (questionId) {
            var flags = this.flags;

            flags[questionId] = !flags[questionId];

            fetch(config.urls.flag, {
                method: 'POST',
                headers: this.headers(),
                body: JSON.stringify({ question_id: questionId }),
            }).catch(function () {
                flags[questionId] = !flags[questionId];
            });
        },

        goto: function (index) {
            if (index >= 0 && index < this.total) {
                this.current = index;
                localStorage.setItem(storageKey, String(this.current));
            }
        },

        focusRelative: function (offset) {
            var tabs = this.$refs.tablist.querySelectorAll('[role="tab"]');
            var target;

            if (offset === 'home') {
                target = 0;
            } else if (offset === 'end') {
                target = this.total - 1;
            } else {
                target = this.current + offset;
                if (target < 0) {
                    target = this.total - 1;
                }
                if (target > this.total - 1) {
                    target = 0;
                }
            }

            this.goto(target);

            if (tabs[target]) {
                tabs[target].focus();
            }
        },

        next: function () {
            this.goto(this.current + 1);
        },

        prev: function () {
            this.goto(this.current - 1);
        },

        tick: function () {
            if (this.submitting || this.timeUpOpen) {
                return;
            }

            // Server-authoritative countdown: derive the remaining time from
            // the real end deadline (with the clock-skew offset), so it stays
            // accurate even when the tab is throttled or backgrounded.
            if (this.endsAtMs > 0) {
                this.remaining = Math.max(0, Math.ceil((this.endsAtMs - (Date.now() + this.clockSkew)) / 1000));
            } else {
                this.remaining = Math.max(0, this.remaining - 1);
            }

            if (this.remaining <= 0) {
                this.remaining = 0;
                clearInterval(timerInterval);
                this.doSubmit(true);
            }
        },

        openConfirm: function () {
            this.confirmOpen = true;
        },

        doSubmit: function (auto) {
            var self = this;

            if (this.submitting) {
                return;
            }

            this.submitting = true;
            this.confirmOpen = false;

            if (auto) {
                this.timeUpOpen = true;
            }

            fetch(config.urls.submit, {
                method: 'POST',
                headers: this.headers(),
                body: '{}',
            }).then(function () {
                window.location.href = config.urls.submitted;
            }).catch(function () {
                self.submitting = false;
                window.location.href = config.urls.submitted;
            });
        },

        finishLocally: function () {
            clearInterval(timerInterval);
            this.remaining = 0;
            this.timeUpOpen = true;

            window.location.href = config.urls.submitted;
        },

        doQuit: function () {
            var self = this;

            if (this.submitting) {
                return;
            }

            this.submitting = true;
            this.quitOpen = false;

            fetch(config.urls.cancel, {
                method: 'POST',
                headers: this.headers(),
                body: '{}',
            }).then(function () {
                window.location.href = config.urls.intro;
            }).catch(function () {
                self.submitting = false;
                window.location.href = config.urls.intro;
            });
        },

        handleCancelled: function (redirect) {
            clearInterval(timerInterval);
            this.cancelledOpen = true;
            this.cancelledRedirect = redirect || config.urls.intro || '/';

            var self = this;
            setTimeout(function () {
                window.location.href = self.cancelledRedirect;
            }, 2500);
        },

        registerLeave: function (type) {
            if (this.submitting || this.timeUpOpen) {
                return;
            }

            this.leaves = (this.leaves || 0) + 1;
            var remaining = this.autoCancelThreshold - this.leaves;

            if (remaining <= 0) {
                return;
            }

            this.warnMessage = 'You left the exam window (' + type + '). ' + remaining
                + ' more leave' + (remaining === 1 ? '' : 's')
                + ' will cancel this attempt.';
            this.warnOpen = true;
            this.pushEvent(type === 'fullscreen_exit' ? 'fullscreen_exit' : 'tab_switch');
        },

        headers: function () {
            return {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            };
        },

        requestFullscreen: function () {
            var element = document.documentElement;

            if (element.requestFullscreen) {
                element.requestFullscreen().catch(function () {});
            }
        },

        installDeterrents: function () {
            ['copy', 'cut', 'paste', 'contextmenu', 'dragstart', 'drop'].forEach(function (eventName) {
                document.addEventListener(eventName, function (event) { event.preventDefault(); });
            });

            document.addEventListener('keydown', function (event) {
                var key = (event.key || '').toLowerCase();
                var blockedKeys = ['p', 's', 'u', 'c', 'v', 'x', 'a'];

                if ((event.ctrlKey || event.metaKey) && blockedKeys.indexOf(key) !== -1) {
                    event.preventDefault();

                    return;
                }

                if (event.key === 'F12') {
                    event.preventDefault();
                }
            });
        },

        installIntegrityCapture: function () {
            var self = this;

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    self.registerLeave('tab switch');
                }
            });

            window.addEventListener('blur', function () {
                self.pushEvent('window_blur');
            });

            document.addEventListener('fullscreenchange', function () {
                if (!document.fullscreenElement) {
                    self.registerLeave('fullscreen exit');
                }
            });

            ['copy', 'paste', 'cut'].forEach(function (eventName) {
                document.addEventListener(eventName, function () {
                    self.pushEvent(eventName.replace('_attempt', '') + '_attempt');
                });
            });

            window.addEventListener('beforeprint', function () {
                self.pushEvent('print_attempt');
            });

            document.addEventListener('contextmenu', function () {
                self.pushEvent('right_click_attempt');
            });
        },

        pushEvent: function (type) {
            if (this.submitting || this.timeUpOpen) {
                return;
            }

            var now = Date.now();

            if (this.lastEventAt[type] && (now - this.lastEventAt[type]) < 2000) {
                return;
            }

            this.lastEventAt[type] = now;
            this.eventQueue.push({
                type: type,
                q: this.current + 1,
            });

            if (this.eventQueue.length >= 50) {
                this.flushEvents();
            }
        },

        flushEvents: function (useBeacon) {
            if (!this.eventQueue.length) {
                return;
            }

            var self = this;
            var batch = this.eventQueue.splice(0, 50);
            var payload = JSON.stringify({ events: batch });
            var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            if (useBeacon && navigator.sendBeacon) {
                var blob = new Blob([payload], { type: 'application/json' });

                navigator.sendBeacon(config.urls.integrity, blob);

                return;
            }

            fetch(config.urls.integrity, {
                method: 'POST',
                keepalive: true,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                },
                body: payload,
            }).then(function (response) {
                if (!response.ok) {
                    return;
                }

                response.json().then(function (data) {
                    if (data && data.cancelled) {
                        self.handleCancelled(data.redirect);
                    }
                });
            }).catch(function () {});
        },
    };
}
</script>
</body>
</html>
