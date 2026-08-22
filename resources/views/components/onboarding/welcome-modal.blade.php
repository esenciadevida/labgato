@props([
    'tourSteps',
])

<div
    x-data="{
        open: true,
        slide: 0,
        total: 4,
        done: false,
        init() {
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => this.$refs.primary && this.$refs.primary.focus());
        },
        next() { if (this.slide < this.total - 1) this.slide++ },
        prev() { if (this.slide > 0) this.slide-- },
        finish(withTour) {
            if (this.done) return;
            this.done = true;
            this.open = false;
            document.body.style.overflow = '';
            const root = this.$root;
            fetch(root.dataset.welcomeUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': root.dataset.csrf, 'Accept': 'application/json' },
            }).catch(() => {});
            if (withTour) setTimeout(() => window.LabgotoTour && window.LabgotoTour.start('member-dashboard'), 250);
        },
        trap(e) {
            const focusables = [...this.$refs.panel.querySelectorAll('button, a[href]')].filter((el) => !el.disabled && el.offsetParent !== null);
            if (!focusables.length) return;
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
            else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
        },
    }"
    data-welcome-url="{{ route('account.onboarding.welcome') }}"
    data-csrf="{{ csrf_token() }}"
    x-init="init()"
    x-on:keydown.escape.window="finish(false)"
    x-on:keydown.tab="trap($event)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[95]"
    role="dialog"
    aria-modal="true"
    aria-labelledby="onboarding-welcome-title"
>
    <div class="absolute inset-0 bg-slate-950/70 backdrop-blur-sm" x-on:click="finish(false)" aria-hidden="true"></div>

    <div class="relative flex min-h-full items-center justify-center p-4">
        <div
            x-ref="panel"
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            class="w-full max-w-md rounded-2xl border border-slate-200 bg-white shadow-xl dark:border-slate-700 dark:bg-slate-800"
        >
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-4 dark:border-slate-700">
                <div class="flex items-center gap-1.5" aria-hidden="true">
                    @foreach(range(0, 3) as $dot)
                        <span class="h-1.5 rounded-full transition-all duration-300 {{ $dot === 0 ? 'w-6 bg-primary-600 dark:bg-primary-500' : 'w-1.5 bg-slate-300 dark:bg-slate-600' }}"
                              :class="{ 'w-6 bg-primary-600 dark:bg-primary-500': slide === {{ $dot }}, 'w-1.5 bg-slate-300 dark:bg-slate-600': slide !== {{ $dot }} }"></span>
                    @endforeach
                </div>
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:hover:bg-slate-700 dark:hover:text-slate-300"
                    x-on:click="finish(false)"
                    aria-label="Skip introduction"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-5 w-5" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-8">
                <div x-show="slide === 0" x-cloak class="text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-7 w-7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/>
                        </svg>
                    </span>
                    <h2 id="onboarding-welcome-title" class="mt-4 font-display text-xl font-bold text-slate-900 dark:text-slate-100">
                        Welcome to {{ config('app.name') }}
                    </h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Your trusted hub for laboratory knowledge &mdash; procedures, result interpretations, and clinical tools built by professionals.
                    </p>
                </div>

                <div x-show="slide === 1" x-cloak class="text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-7 w-7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                        </svg>
                    </span>
                    <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100">Learn the standards</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Browse SOPs and result interpretation guides written and reviewed by experienced laboratory scientists.
                    </p>
                </div>

                <div x-show="slide === 2" x-cloak class="text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-7 w-7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z"/>
                        </svg>
                    </span>
                    <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100">Work faster</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Run clinical calculators and check reference ranges without leaving your browser.
                    </p>
                </div>

                <div x-show="slide === 3" x-cloak class="text-center">
                    <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-100 text-primary-700 dark:bg-primary-900/40 dark:text-primary-300">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="h-7 w-7" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z"/>
                        </svg>
                    </span>
                    <h2 class="font-display text-xl font-bold text-slate-900 dark:text-slate-100">Share your expertise</h2>
                    <p class="mt-2 text-sm leading-relaxed text-slate-500 dark:text-slate-400">
                        Contribute your own SOPs and knowledge resources. Our review team will help you publish with confidence.
                    </p>
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 border-t border-slate-200 px-6 py-4 dark:border-slate-700">
                <button
                    type="button"
                    class="rounded-lg px-3 py-2 text-sm font-medium text-slate-500 transition-colors hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:pointer-events-none disabled:opacity-40 dark:text-slate-400 dark:hover:text-slate-200"
                    x-on:click="prev()"
                    :disabled="slide === 0"
                >Back</button>

                <template x-if="slide < total - 1">
                    <button
                        type="button"
                        ref="primary"
                        class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                        x-on:click="next()"
                    >
                        Next
                    </button>
                </template>

                <template x-if="slide === total - 1">
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="rounded-lg px-3 py-2 text-sm font-medium text-slate-500 transition-colors hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:text-slate-400 dark:hover:text-slate-200"
                            x-on:click="finish(false)"
                        >
                            Explore on my own
                        </button>
                        <button
                            type="button"
                            ref="primary"
                            class="inline-flex items-center justify-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
                            x-on:click="finish(true)"
                        >
                            Take the quick tour
                        </button>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <x-onboarding.tour tour-key="member-dashboard" :steps="$tourSteps" />
</div>
