labgato/
├── database/
│   └── migrations/
│       └── xxxx_add_last_verified_at_to_resources.php
├── app/
│   └── Models/
│       ├── ReferenceRange.php
│       └── SopResource.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── public/
│       │       ├── recent-calculations.blade.php
│       │       ├── recently-viewed.blade.php
│       │       ├── table-of-contents.blade.php
│       │       └── verified-badge.blade.php
│       ├── admin/
│       │   ├── reference-ranges/
│       │   │   └── partials/
│       │   │       └── form.blade.php
│       │   └── sops/
│       │       └── partials/
│       │           └── form.blade.php
│       └── public/
│           ├── home.blade.php
│           ├── calculators/
│           │   └── show.blade.php
│           ├── reference-ranges/
│           │   └── show.blade.php
│           ├── sops/
│           │   └── show.blade.php
│           ├── knowledge/
│           │   └── show.blade.php
│           └── interpretation/
│               └── show.blade.php
└── tests/
    └── Feature/
        └── PublicSite/
            ├── CalculatorHistoryTest.php
            └── RecentlyViewedTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD23-SELECTED-ENHANCEMENTS

CONTEXT:
LabGato V1.1 is live-ready with Builds 1-22 complete. We are adding five selected enhancements that serve laboratory professionals: calculator result history, critical value highlighting, "last verified" trust badges, a sticky table of contents, and recently viewed resources.

All localStorage features are client-side only and require no user account.

OBJECTIVE:
Implement A1 (calculator result history), A2 (critical value highlighting), A3 (last verified date + source badge), B1 (sticky table of contents), and C1 (recently viewed resources).

EXISTING ARCHITECTURE:
- Tailwind CSS, Alpine.js, Livewire.
- CalculatorRunner and ToolRunner Livewire components exist.
- reference_ranges and sop_resources tables exist.
- Hostinger shared hosting. WCAG 2.2 AA required.

FILES TO CREATE:
- database/migrations/xxxx_add_last_verified_at_to_resources.php
- resources/views/components/public/recent-calculations.blade.php
- resources/views/components/public/recently-viewed.blade.php
- resources/views/components/public/table-of-contents.blade.php
- resources/views/components/public/verified-badge.blade.php
- tests/Feature/PublicSite/CalculatorHistoryTest.php
- tests/Feature/PublicSite/RecentlyViewedTest.php

FILES TO MODIFY:
- app/Models/ReferenceRange.php
- app/Models/SopResource.php
- resources/views/admin/reference-ranges/partials/form.blade.php
- resources/views/admin/sops/partials/form.blade.php
- resources/views/public/calculators/show.blade.php
- resources/views/public/reference-ranges/show.blade.php
- resources/views/public/sops/show.blade.php
- resources/views/public/knowledge/show.blade.php
- resources/views/public/interpretation/show.blade.php
- resources/views/public/home.blade.php

TASK 1: SCHEMA CHANGE (A3)

Create migration: add_last_verified_at_to_resources.

Add column to reference_ranges:
- last_verified_at: date, nullable, after source_citation.

Add column to sop_resources:
- last_verified_at: date, nullable, after references.

Update ReferenceRange and SopResource models:
- Add last_verified_at to $fillable.
- Cast last_verified_at to date.

TASK 2: CALCULATOR RESULT HISTORY (A1)

Modify the CalculatorRunner Livewire component to dispatch a browser event after successful calculation.

After a successful calculation, dispatch:
$this->dispatchBrowserEvent('labgato:calc-result', [
    'slug' => $this->calculator->slug,
    'name' => $this->calculator->name,
    'inputs' => $this->inputs,
    'outputs' => $this->results,
    'timestamp' => now()->toISOString(),
]);

Create resources/views/components/public/recent-calculations.blade.php.

An Alpine.js component that:
- Listens for the labgato:calc-result event.
- Saves the result to localStorage under key labgato-calc-history-{slug}.
- Keeps only the last 5 entries per calculator.
- Renders a "Recent Calculations" list below the calculator.
- Each entry shows: timestamp, inputs summary, result.
- Provides a "Clear history" button.

Modify calculators/show.blade.php:
- Include <x-public.recent-calculations> below the calculator.
- Pass the calculator slug.

Privacy rules:
- All history is client-side only (localStorage).
- Never send history to the server.
- Provide clear "Clear history" control.

TASK 3: CRITICAL VALUE HIGHLIGHTING (A2)

Modify reference-ranges/show.blade.php.

In the reference range variations table:
- Display critical_low and critical_high values with the danger semantic color (text-danger-600).
- Add a "Critical" badge or warning icon next to critical values.
- Ensure the highlighting is not color-only (pair with icon or text label).

Rules:
- Only highlight when critical_low or critical_high is not null.
- Use the theme danger color tokens.

TASK 4: LAST VERIFIED + SOURCE BADGE (A3)

Create resources/views/components/public/verified-badge.blade.php.

Props:
- date: the last_verified_at date.
- source: optional source_citation string.

Render:
- A badge showing "Last verified: {date}".
- If source is provided, show it below or in a tooltip.
- Use the info or success semantic color.
- If date is null, do not render.

Modify reference-ranges/show.blade.php and sops/show.blade.php:
- Include <x-public.verified-badge> near the top of the detail page.
- Pass last_verified_at and source_citation.

Modify admin forms (reference-ranges and sops):
- Add a date input for last_verified_at.
- Label: "Last Verified Date".
- Add helper text: "The date this data was last validated against a trusted source."

TASK 5: STICKY TABLE OF CONTENTS (B1)

Create resources/views/components/public/table-of-contents.blade.php.

An Alpine.js component that:
- On mount, scans the article content container for h2 and h3 headings.
- Generates a nested TOC list.
- Assigns IDs to headings that lack them (slugify the text).
- Renders the TOC in a sticky sidebar (desktop) or collapsible section (mobile).
- Highlights the active section based on scroll position (scroll spy).

Accessibility:
- Use nav with aria-label="Table of contents".
- Use a list structure.
- Ensure keyboard navigation works.

Modify knowledge/show.blade.php and interpretation/show.blade.php:
- Wrap the content in a container with a known ID (e.g., id="article-content").
- Include <x-public.table-of-contents> alongside the content.
- Use a two-column layout on desktop (content + sticky TOC).

Rules:
- Only render the TOC if the article has at least 2 headings.
- Hide the TOC on short articles.

TASK 6: RECENTLY VIEWED RESOURCES (C1)

Create resources/views/components/public/recently-viewed.blade.php.

An Alpine.js component that:
- On mount, reads the current page's resource info (type, title, url) from data attributes.
- Saves it to localStorage under key labgato-recently-viewed.
- Keeps only the last 8 entries, deduplicated by url.
- Renders a "Recently Viewed" horizontal list or grid.

Modify resource detail pages (calculators, tools, reference-ranges, sops, knowledge, interpretation):
- Add data attributes for the resource info (data-resource-type, data-resource-title, data-resource-url).
- Include <x-public.recently-viewed> at the bottom of the page.

Modify public/home.blade.php:
- Add a "Recently Viewed" section that reads from localStorage.
- Show only if there is history.
- Provide a "Clear" button.

Privacy rules:
- All data is client-side only.
- Never send to server.

TASK 7: FEATURE TESTS

Create tests/Feature/PublicSite/CalculatorHistoryTest.php.

Required tests:
1. test_calculator_page_includes_recent_calculations_component
   - Visit a published calculator detail page.
   - Assert the recent-calculations component markup is present.
2. test_calculator_runner_dispatches_result_event
   - Use Livewire to perform a calculation.
   - Assert the labgato:calc-result event is dispatched.

Create tests/Feature/PublicSite/RecentlyViewedTest.php.

Required tests:
1. test_resource_detail_page_includes_recently_viewed_component
   - Visit a published resource detail page.
   - Assert the recently-viewed component markup is present.
2. test_home_page_includes_recently_viewed_section
   - Visit the home page.
   - Assert the recently-viewed section markup is present.

DEFINITION OF DONE:
- last_verified_at column exists on reference_ranges and sop_resources.
- Calculator result history saves to localStorage and renders below the calculator.
- Critical values are highlighted with danger color and an icon/label.
- Verified badge displays last_verified_at and source on reference ranges and SOPs.
- Sticky TOC renders on Knowledge and Interpretation articles with 2+ headings.
- Recently viewed resources save to localStorage and render on home + detail pages.
- All localStorage features are client-side only.
- All features are keyboard accessible and respect reduced motion.
- Feature tests pass.