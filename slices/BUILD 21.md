labgato/
├── app/
│   └── Http/
│       └── Controllers/
│           └── PublicSite/
│               └── LiveSearchController.php
├── resources/
│   ├── css/
│   │   └── app.css
│   └── views/
│       ├── components/
│       │   ├── ui/
│       │   │   └── skeleton.blade.php
│       │   └── public/
│       │       ├── header.blade.php
│       │       └── search/
│       │           └── live-search.blade.php
│       └── livewire/
│           └── public/
│               ├── calculators/
│               │   └── calculator-runner.blade.php
│               └── tools/
│                   └── tool-runner.blade.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── PublicSite/
            └── LiveSearchTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD21-UX-POLISH

CONTEXT:
LabGato V1.1 is complete. We are adding two UX improvements: skeleton loaders for perceived performance, and a responsive live (as-you-type) search in the public header.

OBJECTIVE:
Add a reusable skeleton loader component and a live search dropdown with debouncing, keyboard navigation, and accessibility support.

EXISTING ARCHITECTURE:
- Tailwind CSS, Alpine.js, Livewire.
- SearchService already queries published resources.
- Hostinger shared hosting (no Redis). Use database cache.
- WCAG 2.2 AA required.

FILES TO CREATE:
- resources/views/components/ui/skeleton.blade.php
- resources/views/components/public/search/live-search.blade.php
- app/Http/Controllers/PublicSite/LiveSearchController.php
- tests/Feature/PublicSite/LiveSearchTest.php

FILES TO MODIFY:
- resources/views/components/public/header.blade.php
- resources/css/app.css
- routes/web.php
- resources/views/livewire/public/calculators/calculator-runner.blade.php
- resources/views/livewire/public/tools/tool-runner.blade.php

TASK 1: SKELETON COMPONENT

Create resources/views/components/ui/skeleton.blade.php.

A reusable skeleton placeholder.

Props:
- class: string (Tailwind sizing classes, e.g., "h-4 w-3/4")

Render:
<div class="skeleton {{ $class }}" aria-hidden="true"></div>

Add to resources/css/app.css:

.skeleton {
    @apply animate-pulse rounded bg-neutral-200;
}

@media (prefers-reduced-motion: reduce) {
    .skeleton { animation: none; }
}

TASK 2: LIVE SEARCH ENDPOINT

Create app/Http/Controllers/PublicSite/LiveSearchController.php.

Route: GET /search/live  (name: public.search.live)
Middleware: throttle:search-live (30 requests/minute)

Behavior:
- Accept ?q= parameter.
- If q length < 2, return { "results": [] }.
- Query published resources across all 6 types via SearchService.
- Limit to top 6 results.
- Return JSON:

{
  "results": [
    { "title": "Anion Gap", "type": "Calculator", "url": "/calculators/anion-gap" },
    ...
  ]
}

Rules:
- Only published resources.
- Sanitize q.
- Do not log every keystroke (only full searches on /search).

TASK 3: LIVE SEARCH DROPDOWN (ALPINE.JS)

Create resources/views/components/public/search/live-search.blade.php.

An Alpine.js component embedded in the public header.

Behavior:
- Text input bound to x-model="query".
- Debounced fetch (300ms) to /search/live?q=... when query length >= 2.
- Show dropdown while loading or when results exist.
- While loading: render 3 skeleton rows using <x-ui.skeleton>.
- When results: render list of links (title + type badge).
- "View all results" link at bottom → /search?q={query}.
- Close on Escape, on outside click, or on blur.

Accessibility (WCAG 2.2 AA):
- Input: role="combobox", aria-expanded, aria-controls, aria-autocomplete="list".
- Dropdown: role="listbox".
- Items: role="option", aria-selected.
- Keyboard: ArrowDown/ArrowUp move active option, Enter selects, Escape closes.
- Use aria-activedescendant for the active option.
- Announce result count via aria-live="polite".

TASK 4: INTEGRATE INTO HEADER

Modify resources/views/components/public/header.blade.php.

- Add the live-search component to the public header (desktop).
- On mobile, keep the existing search link to /search, OR add the live search inside the mobile menu.

TASK 5: USE SKELETONS IN LIVEWIRE LOADING STATES

Modify calculator-runner.blade.php and tool-runner.blade.php.

- Replace plain "Loading..." text with skeleton placeholders during wire:loading.
- Example: show 2 skeleton rows in the result area while calculating.

TASK 6: FEATURE TESTS

Create tests/Feature/PublicSite/LiveSearchTest.php.

Required tests:
1. test_live_search_returns_json_results
   - Seed a published calculator.
   - GET /search/live?q=anion
   - Assert JSON contains the calculator.
2. test_live_search_returns_empty_for_short_query
   - GET /search/live?q=a
   - Assert results is empty.
3. test_live_search_excludes_unpublished
   - Seed a draft calculator.
   - GET /search/live?q=...
   - Assert draft is not returned.
4. test_live_search_is_rate_limited
   - Assert throttle middleware applied.

DEFINITION OF DONE:
- Skeleton component exists and is used in live search and Livewire loading states.
- Live search endpoint returns JSON of published resources only.
- Live search dropdown works with debouncing and keyboard navigation.
- Live search is accessible (combobox/listbox pattern).
- Header includes live search on desktop.
- Feature tests pass.
- Reduced motion is respected for skeletons.