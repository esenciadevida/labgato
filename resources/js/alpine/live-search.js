export function liveSearch(liveUrl, searchUrl) {
    return {
        query: '',
        results: [],
        isOpen: false,
        isLoading: false,
        activeIndex: -1,
        panelOpen: false,
        liveUrl: liveUrl,
        searchUrl: searchUrl,
        controller: null,

        open() {
            if (this.query.trim().length >= 2) {
                this.isOpen = true;
            }
        },

        close() {
            this.isOpen = false;
            this.activeIndex = -1;
        },

        openPanel() {
            this.panelOpen = true;
            this.$nextTick(() => {
                if (this.$refs.liveSearchInput) {
                    this.$refs.liveSearchInput.focus();
                }
            });
        },

        closePanel() {
            this.panelOpen = false;
            this.query = '';
            this.close();
        },

        togglePanel() {
            if (this.panelOpen) {
                this.closePanel();
            } else {
                this.openPanel();
            }
        },

        fetchResults() {
            if (this.query.trim().length < 2) {
                this.results = [];
                this.isOpen = false;
                return;
            }

            if (this.controller) {
                this.controller.abort();
            }

            this.controller = new AbortController();
            this.isLoading = true;
            this.isOpen = true;

            fetch(this.liveUrl + '?q=' + encodeURIComponent(this.query.trim()), {
                signal: this.controller.signal,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            })
                .then(response => response.ok ? response.json() : { results: [] })
                .then(data => {
                    this.results = data.results || [];
                    this.activeIndex = -1;
                })
                .catch(e => {
                    if (e.name !== 'AbortError') {
                        this.results = [];
                    }
                })
                .finally(() => {
                    this.isLoading = false;
                });
        },

        goToSearch() {
            const q = this.query.trim();
            if (q.length < 2) {
                return;
            }
            window.location.href = this.searchUrl + '?q=' + encodeURIComponent(q);
        },

        highlightNext() {
            if (this.activeIndex < this.results.length - 1) {
                this.activeIndex++;
            }
        },

        highlightPrev() {
            if (this.activeIndex > 0) {
                this.activeIndex--;
            }
        },

        selectHighlighted() {
            const result = this.activeIndex >= 0 ? this.results[this.activeIndex] : null;

            if (result) {
                this.close();
                window.location.href = result.url;
            } else {
                this.goToSearch();
            }
        },

        init() {
            this.$watch('query', () => {
                this.fetchResults();
            });
        },
    };
}
