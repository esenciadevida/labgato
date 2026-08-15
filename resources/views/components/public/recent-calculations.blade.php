<div x-data="recentCalculations()" x-init="init()" data-calculator-slug="{{ $calculatorSlug }}">
    <template x-if="history.length > 0">
        <div class="border-t border-slate-200 dark:border-slate-700 pt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Recent Calculations</h3>
                <button
                    @click="clearHistory()"
                    class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 rounded"
                    aria-label="Clear calculation history"
                >
                    Clear history
                </button>
            </div>

            <ul class="space-y-3" role="list" aria-label="Recent calculations">
                <template x-for="(entry, index) in history" :key="index">
                    <li class="p-4 bg-slate-50 dark:bg-slate-800 rounded-lg border border-slate-200 dark:border-slate-700">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 mb-2">
                                    <time :datetime="entry.timestamp" x-text="formatDate(entry.timestamp)"></time>
                                    <span aria-hidden="true">•</span>
                                    <span x-text="entry.name"></span>
                                </div>
                                <div class="text-sm text-slate-700 dark:text-slate-300 mb-1">
                                    <span class="font-medium">Inputs:</span>
                                    <span x-text="formatInputs(entry.inputs)"></span>
                                </div>
                                <div class="text-sm text-slate-700 dark:text-slate-300">
                                    <span class="font-medium">Result:</span>
                                    <span x-text="formatOutputs(entry.outputs)"></span>
                                </div>
                            </div>
                        </div>
                    </li>
                </template>
            </ul>
        </div>
    </template>
</div>

<script>
    function recentCalculations() {
        return {
            slug: '',
            storageKey: '',
            history: [],

            init() {
                this.slug = this.$el.getAttribute('data-calculator-slug') || '';
                this.storageKey = `labgato-calc-history-${this.slug}`;

                const stored = localStorage.getItem(this.storageKey);
                if (stored) {
                    try {
                        this.history = JSON.parse(stored);
                    } catch (e) {
                        this.history = [];
                    }
                }

                this.$watch('history', (val) => {
                    localStorage.setItem(this.storageKey, JSON.stringify(val));
                }, { deep: true });

                this._calcResultHandler = (event) => {
                    if (event.detail?.slug === this.slug) {
                        this.addToHistory(event.detail);
                    }
                };
                window.addEventListener('labgato:calc-result', this._calcResultHandler);
            },

            destroy() {
                if (this._calcResultHandler) {
                    window.removeEventListener('labgato:calc-result', this._calcResultHandler);
                }
            },

            addToHistory(detail) {
                const entry = {
                    slug: detail.slug,
                    name: detail.name,
                    inputs: detail.inputs,
                    outputs: detail.outputs,
                    timestamp: detail.timestamp,
                };

                this.history.unshift(entry);
                if (this.history.length > 5) {
                    this.history = this.history.slice(0, 5);
                }
            },

            clearHistory() {
                this.history = [];
                localStorage.removeItem(this.storageKey);
            },

            formatDate(isoString) {
                const date = new Date(isoString);
                return date.toLocaleString(undefined, {
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                });
            },

            formatInputs(inputs) {
                if (!inputs || typeof inputs !== 'object') return '';
                return Object.entries(inputs)
                    .filter(([_, v]) => v !== '' && v !== null && v !== undefined)
                    .map(([k, v]) => `${k}: ${v}`)
                    .join(', ');
            },

            formatOutputs(outputs) {
                if (!outputs || typeof outputs !== 'object') return '';
                return Object.entries(outputs)
                    .map(([k, v]) => `${k}: ${v}`)
                    .join(', ');
            },
        };
    }
</script>