@props([
    'contentSelector' => '#article-content',
])

<div x-data="tableOfContents()" x-init="init()" class="hidden lg:block">
    <nav
        x-cloak
        x-show="headings.length > 1"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 transform translate-x-2"
        x-transition:enter-end="opacity-100 transform translate-x-0"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 transform translate-x-0"
        x-transition:leave-end="opacity-0 transform translate-x-2"
        class="sticky top-24 max-h-[calc(100vh-8rem)] overflow-y-auto pr-4"
        aria-label="Table of contents"
        role="navigation"
    >
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">On this page</h2>
            <button
                type="button"
                @click="collapsed = !collapsed"
                :aria-expanded="!collapsed"
                :aria-controls="tocId"
                class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded p-1"
            >
                <span x-show="collapsed" aria-hidden="true">Expand</span>
                <span x-show="!collapsed" aria-hidden="true">Collapse</span>
            </button>
        </div>

        <ul
            :id="tocId"
            x-show="!collapsed"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 transform translate-y-1"
            x-transition:enter-end="opacity-100 transform translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 transform translate-y-0"
            x-transition:leave-end="opacity-0 transform translate-y-1"
            class="space-y-1 text-sm"
            role="list"
        >
            <template x-for="heading in headings" :key="heading.id">
                <li :class="heading.level === 3 ? 'pl-4' : ''">
                    <a
                        :href="'#' + heading.id"
                        :class="[
                            'block px-2 py-1 rounded transition-colors',
                            heading.active ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-medium' : 'text-slate-600 dark:text-slate-300 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400',
                        ]"
                        x-text="heading.text"
                        @click.prevent="scrollToHeading(heading.id)"
                    ></a>
                </li>
            </template>
        </ul>
    </nav>
</div>

<!-- Mobile collapsible TOC -->
<div x-cloak x-show="headings.length > 1" class="lg:hidden no-print">
    <details class="group">
        <summary class="flex items-center justify-between p-3 rounded-lg bg-slate-50 dark:bg-slate-800 cursor-pointer list-none focus:outline-none focus:ring-2 focus:ring-primary-500">
            <span class="text-sm font-medium text-slate-900 dark:text-slate-100">On this page</span>
            <svg class="w-5 h-5 text-slate-500 dark:text-slate-400 group-open:rotate-180 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </summary>
        <div class="mt-2 space-y-1 text-sm max-h-60 overflow-y-auto">
            <ul class="space-y-1" role="list">
                <template x-for="heading in headings" :key="heading.id">
                    <li :class="heading.level === 3 ? 'pl-4' : ''">
                        <a
                            :href="'#' + heading.id"
                            :class="[
                                'block px-2 py-1 rounded transition-colors',
                                heading.active ? 'bg-primary-50 dark:bg-primary-900/20 text-primary-700 dark:text-primary-300 font-medium' : 'text-slate-600 dark:text-slate-300 dark:text-slate-400 hover:text-primary-600 dark:hover:text-primary-400',
                            ]"
                            x-text="heading.text"
                            @click.prevent="scrollToHeading(heading.id)"
                        ></a>
                    </li>
                </template>
            </ul>
        </div>
    </details>
</div>

<script>
    function tableOfContents() {
        return {
            headings: [],
            collapsed: false,
            tocId: 'toc-' + Math.random().toString(36).substr(2, 9),
            observer: null,
            contentContainer: null,

            init() {
                this.contentContainer = document.querySelector(this.$el.getAttribute('data-content-selector') || '#article-content');
                if (!this.contentContainer) return;

                this.buildTOC();
                this.observeScroll();
                this.handleReducedMotion();
            },

            buildTOC() {
                const headingElements = this.contentContainer.querySelectorAll('h2, h3');
                if (headingElements.length < 2) return;

                headingElements.forEach((el, index) => {
                    const level = parseInt(el.tagName.charAt(1));
                    let id = el.id;

                    if (!id) {
                        id = this.slugify(el.textContent);
                        let uniqueId = id;
                        let counter = 1;
                        while (document.getElementById(uniqueId)) {
                            uniqueId = id + '-' + counter;
                            counter++;
                        }
                        id = uniqueId;
                        el.id = id;
                    }

                    this.headings.push({
                        id: id,
                        text: el.textContent,
                        level: level,
                        active: false,
                        element: el,
                    });
                });
            },

            slugify(text) {
                return text
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            },

            observeScroll() {
                if ('IntersectionObserver' in window) {
                    const options = {
                        root: null,
                        rootMargin: '-20% 0px -70% 0px',
                        threshold: 0,
                    };

                    this.observer = new IntersectionObserver((entries) => {
                        entries.forEach((entry) => {
                            const heading = this.headings.find((h) => h.id === entry.target.id);
                            if (heading) {
                                heading.active = entry.isIntersecting;
                            }
                        });

                        this.updateActiveHeading();
                    }, options);

                    this.headings.forEach((heading) => {
                        if (heading.element) {
                            this.observer.observe(heading.element);
                        }
                    });
                } else {
                    this.fallbackScrollSpy();
                }
            },

            fallbackScrollSpy() {
                this._fallbackScrollHandler = () => this.updateActiveHeading();
                window.addEventListener('scroll', this._fallbackScrollHandler, { passive: true });
            },

            updateActiveHeading() {
                const scrollY = window.scrollY || window.pageYOffset;
                let activeHeading = null;

                this.headings.forEach((heading) => {
                    if (heading.element) {
                        const rect = heading.element.getBoundingClientRect();
                        if (rect.top <= 100 && rect.bottom >= 100) {
                            activeHeading = heading;
                        }
                    }
                });

                if (activeHeading) {
                    this.headings.forEach((h) => {
                        h.active = h.id === activeHeading.id;
                    });
                }
            },

            scrollToHeading(id) {
                const target = document.getElementById(id);
                if (!target) return;

                const headerOffset = 80;
                const elementPosition = target.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - headerOffset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: this.prefersReducedMotion ? 'auto' : 'smooth',
                });

                target.focus({ preventScroll: true });
                history.pushState(null, '', '#' + id);
            },

            handleReducedMotion() {
                this.prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
            },

            destroy() {
                if (this.observer) {
                    this.observer.disconnect();
                }
                if (this._fallbackScrollHandler) {
                    window.removeEventListener('scroll', this._fallbackScrollHandler);
                }
            },
        };
    }
</script>