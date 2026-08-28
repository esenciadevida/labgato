const typeMeta = {
    calculator: {
        label: 'Calculator',
        color: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-800',
        paths: [
            'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        ],
    },
    tool: {
        label: 'Tool',
        color: 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-300 dark:border-purple-800',
        paths: [
            'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            'M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        ],
    },
    'reference-range': {
        label: 'Reference Range',
        color: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800',
        paths: [
            'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        ],
    },
    interpretation: {
        label: 'Interpretation',
        color: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800',
        paths: [
            'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
        ],
    },
    knowledge: {
        label: 'Knowledge',
        color: 'bg-cyan-50 text-cyan-700 border-cyan-200 dark:bg-cyan-900/30 dark:text-cyan-300 dark:border-cyan-800',
        paths: [
            'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        ],
    },
    sop: {
        label: 'SOP',
        color: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-300 dark:border-rose-800',
        paths: [
            'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        ],
    },
};

export function liveResults(config) {
    return {
        q: config.initialQ || '',
        type: config.initialType || 'all',
        category: config.initialCategory || '',
        tag: config.initialTag || '',
        results: [],
        total: 0,
        isLoading: false,
        active: false,
        error: false,
        liveUrl: config.liveUrl,
        baseUrl: config.baseUrl,
        typeRestriction: config.type || 'all',
        showTypeSelect: Boolean(config.showTypeSelect),
        limit: config.limit || 10,
        controller: null,

        get effectiveType() {
            if (this.typeRestriction && this.typeRestriction !== 'all') {
                return this.typeRestriction;
            }

            return this.showTypeSelect ? this.type : 'all';
        },

        shouldFetch() {
            return this.q.trim().length >= 2;
        },

        onFiltersChange() {
            if (!this.shouldFetch()) {
                if (this.controller) {
                    this.controller.abort();
                }

                this.results = [];
                this.total = 0;
                this.active = false;
                this.error = false;

                return;
            }

            this.fetchResults();
        },

        fetchResults() {
            if (this.controller) {
                this.controller.abort();
            }

            const params = new URLSearchParams();
            params.set('q', this.q.trim());

            const type = this.effectiveType;
            if (type && type !== 'all') {
                params.set('type', type);
            }

            if (this.category) {
                params.set('category', this.category);
            }

            if (this.tag) {
                params.set('tag', this.tag);
            }

            params.set('limit', this.limit);

            this.controller = new AbortController();
            this.isLoading = true;
            this.active = true;
            this.error = false;

            fetch(this.liveUrl + '?' + params.toString(), {
                signal: this.controller.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then((response) => (response.ok ? response.json() : null))
                .then((data) => {
                    if (!data) {
                        this.error = true;
                        this.results = [];
                        this.total = 0;

                        return;
                    }

                    this.results = data.results || [];
                    this.total = data.total || this.results.length;
                })
                .catch((e) => {
                    if (e.name !== 'AbortError') {
                        this.error = true;
                        this.results = [];
                        this.total = 0;
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        selectChanged(event) {
            if (this.shouldFetch()) {
                this.fetchResults();
            } else if (event && event.target && event.target.form) {
                event.target.form.submit();
            }
        },

        clearFilters() {
            this.q = '';
            this.category = '';
            this.tag = '';

            if (this.showTypeSelect) {
                this.type = 'all';
            }

            if (this.controller) {
                this.controller.abort();
            }

            this.results = [];
            this.total = 0;
            this.active = false;
            this.error = false;
        },

        viewAllUrl() {
            const params = new URLSearchParams();

            if (this.q.trim()) {
                params.set('q', this.q.trim());
            }

            const type = this.effectiveType;
            if (type && type !== 'all') {
                params.set('type', type);
            }

            if (this.category) {
                params.set('category', this.category);
            }

            if (this.tag) {
                params.set('tag', this.tag);
            }

            const query = params.toString();

            return this.baseUrl + (query ? '?' + query : '');
        },

        meta(type) {
            return typeMeta[type] || {
                label: type || 'Resource',
                color: 'bg-slate-100 text-slate-700 border-slate-200 dark:bg-slate-700 dark:text-slate-200 dark:border-slate-600',
                paths: [],
            };
        },

        formatDate(value) {
            if (!value) {
                return '';
            }

            const date = new Date(value);

            if (isNaN(date.getTime())) {
                return '';
            }

            return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
        },

        init() {
            this.$watch('q', () => this.onFiltersChange());
        },
    };
}
