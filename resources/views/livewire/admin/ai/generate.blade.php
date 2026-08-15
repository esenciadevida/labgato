@php($componentKey = 'ai-generate-'.($target ?: 'untitled'))
@php($confirmTitle = 'ai-confirm-'.$componentKey)

<div
    wire:key="{{ $componentKey }}"
    class="ai-generate"
    x-data="{
        loading: @entangle('loading'),
        error: @entangle('error'),
        generated: @entangle('generated'),
        confirmOpen: false,
        banner: false,
        bannerTimer: null,
        formTouched: 0,
        confirmOverwrite: @js($confirmOverwrite),
        minSourceChars: @js($minSourceChars),

        get sourceLength() {
            this.formTouched;
            return this.readField(@js($sourceField)).trim().length;
        },

        get blocked() {
            return this.minSourceChars > 0 && this.sourceLength < this.minSourceChars;
        },

        readField(id) {
            const el = document.getElementById(id);
            return el ? el.value : '';
        },

        trigger() {
            this.error = '';
            if (this.confirmOverwrite && this.readField(@js($target)).trim().length > 0) {
                this.confirmOpen = true;
                return;
            }
            this.start();
        },

        start() {
            this.confirmOpen = false;
            this.loading = true;
            $wire.generate(
                this.readField(@js($titleField)),
                this.readField(@js($outlineField)),
                this.readField(@js($sourceField)),
            );
        },

        init() {
            const form = this.$el.closest('form');
            form?.addEventListener('input', () => { this.formTouched = Date.now(); });

            this.$watch('generated', (value) => {
                const el = document.getElementById(@js($target));
                if (el) {
                    el.disabled = false;
                    if (value) {
                        el.value = value;
                        el.focus({ preventScroll: true });
                        this.banner = true;
                        clearTimeout(this.bannerTimer);
                        this.bannerTimer = setTimeout(() => { this.banner = false; }, 10000);
                    }
                }
            });

            this.$watch('loading', (value) => {
                const el = document.getElementById(@js($target));
                if (el) {
                    el.disabled = value;
                }
                if (value) {
                    this.banner = false;
                }
            });

            this.$watch('confirmOpen', (open) => {
                this.$nextTick(() => {
                    if (open) {
                        this.$refs.confirmCancel?.focus();
                    } else {
                        this.$refs.generateButton?.focus();
                    }
                });
            });
        },
    }"
>
    <div class="flex flex-wrap items-center gap-3">
        <button
            x-ref="generateButton"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg border border-primary-600/30 bg-primary-50 px-3 py-1.5 text-sm font-medium text-primary-700 transition-colors hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:border-primary-600/40 dark:bg-primary-900/30 dark:text-primary-300 dark:hover:bg-primary-900/50 dark:focus:ring-offset-slate-900"
            :disabled="loading || blocked"
            @click="trigger()"
            :aria-busy="loading ? 'true' : 'false'"
        >
            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l1.912 5.813a2 2 0 001.275 1.275L21 12l-5.813 1.912a2 2 0 00-1.275 1.275L12 21l-1.912-5.813a2 2 0 00-1.275-1.275L3 12l5.813-1.912a2 2 0 001.275-1.275L12 3z" />
            </svg>
            <span x-show="!loading" x-text="@js($label)">{{ $label }}</span>
            <span x-show="loading" x-cloak class="inline-flex items-center gap-2">
                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true" focusable="false">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
                Generating...
            </span>
        </button>

        <span
            x-show="blocked"
            x-cloak
            class="text-xs text-slate-500"
            role="note"
        >Enter at least {{ $minSourceChars }} characters in the source field to enable this action.</span>
    </div>

    <div x-show="loading" x-cloak class="mt-3" role="status">
        <x-ui.alert type="info">Generating AI content, please wait. This can take up to 15 seconds.</x-ui.alert>
    </div>

    <div x-show="error" x-cloak class="mt-3">
        <x-ui.alert type="error">
            <span x-text="error">{{ $error }}</span>
        </x-ui.alert>
    </div>

    <div x-show="banner" x-cloak class="mt-3 rounded-lg border border-warning-600/20 bg-warning-50 dark:bg-warning-900/30 dark:text-warning-400 p-4 text-sm text-warning-600 dark:border-warning-600/30" role="status">
        AI-generated content inserted. Please review for scientific accuracy and edit as needed before saving.
    </div>

    <div x-show="confirmOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50" @click="confirmOpen = false"></div>
        <div
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $confirmTitle }}"
            class="relative w-full max-w-md rounded-lg bg-white p-6 shadow-xl dark:bg-slate-800"
        >
            <h3 id="{{ $confirmTitle }}" class="text-lg font-semibold text-slate-900 dark:text-slate-100">Replace existing text?</h3>
            <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">This will replace your existing text with AI-generated content. Are you sure you want to continue?</p>
            <div class="mt-5 flex justify-end gap-3">
                <button
                    x-ref="confirmCancel"
                    type="button"
                    class="inline-flex items-center rounded-lg px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 focus:outline-none focus:ring-2 focus:ring-slate-400 dark:text-slate-200 dark:hover:bg-slate-700"
                    @click="confirmOpen = false"
                >Cancel</button>
                <button
                    type="button"
                    class="inline-flex items-center rounded-lg bg-primary-600 px-4 py-2 text-sm font-medium text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-primary-500"
                    @click="start()"
                >Replace Text</button>
            </div>
        </div>
    </div>
</div>
