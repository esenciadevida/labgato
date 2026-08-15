labgato/
├── resources/
│   ├── css/
│   │   └── app.css
│   └── views/
│       ├── components/
│       │   └── public/
│       │       ├── resource-hero.blade.php
│       │       ├── related-resources.blade.php
│       │       └── resource-meta-bar.blade.php
│       └── public/
│           ├── calculators/
│           │   ├── index.blade.php   (reference template)
│           │   └── show.blade.php
│           ├── tools/
│           │   └── show.blade.php
│           ├── reference-ranges/
│           │   └── show.blade.php
│           ├── sops/
│           │   └── show.blade.php
│           ├── interpretation/
│           │   └── show.blade.php
│           └── knowledge/
│               └── show.blade.php
└── tests/
    └── Feature/
        └── PublicSite/
            └── ResourceDetailTemplateTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD43-DETAIL-TEMPLATE-FIX

CONTEXT:
The six resource detail pages are visually broken and inconsistent with the established
template. The "Related Calculators" cards are truncated and squeezed, and the detail
hero lacks the listing page's hero background and has a large empty gap. This build
fixes all six detail pages to match the established template.

OBJECTIVE:
Fix the related-resources component (truncation + layout), fix the resource-hero to
match the listing page template (background + spacing), remove the empty gap, and
apply consistently across all six resource detail pages.

REFERENCE TEMPLATE:
The LISTING page hero (e.g., resources/views/public/calculators/index.blade.php) is
the source of truth. It has:
- A navy/indigo hero background (e.g., bg-primary-950 or a subtle gradient).
- Breadcrumbs at top.
- Category label (uppercase, primary-400).
- Large white title.
- Muted description.
- Consistent vertical padding (py-16 / py-20).

FILES TO INSPECT:
- resources/views/public/calculators/index.blade.php (reference hero)
- resources/views/components/public/resource-hero.blade.php
- resources/views/components/public/related-resources.blade.php
- resources/views/components/public/resource-meta-bar.blade.php
- All 6 show.blade.php pages
- resources/css/app.css

FILES TO MODIFY:
- resources/views/components/public/resource-hero.blade.php
- resources/views/components/public/related-resources.blade.php
- resources/views/components/public/resource-meta-bar.blade.php
- All 6 show.blade.php pages
- resources/css/app.css (if needed)

FILES TO CREATE:
- tests/Feature/PublicSite/ResourceDetailTemplateTest.php

TASK 1: FIX RELATED-RESOURCES COMPONENT

Modify resources/views/components/public/related-resources.blade.php.

Problems to fix:
- The badge text ("CALCULATOR") is truncated. Fix by allowing the badge to fit:
  use smaller text (text-[10px] or text-xs), add max-w-full and truncate, OR
  remove whitespace-nowrap so it can wrap.
- The cards are squeezed into 3 columns in a narrow sidebar. Fix the layout:
  - When rendered in a sidebar/narrow context: use a VERTICAL stack (space-y-3),
    one card per row.
  - When rendered full-width: use a responsive grid (grid-cols-1 sm:grid-cols-2 lg:grid-cols-3).

Recommended card structure (vertical-friendly):
<div class="block rounded-lg border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 p-4 hover:border-primary-500 transition overflow-hidden">
    <span class="inline-block max-w-full truncate rounded-full bg-primary-50 dark:bg-primary-500/10 px-2.5 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-primary-700 dark:text-primary-400">
        {{ $type }}
    </span>
    <h3 class="mt-2 font-semibold text-slate-900 dark:text-slate-100 leading-snug line-clamp-2">{{ $title }}</h3>
    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400 line-clamp-2">{{ $description }}</p>
</div>

Rules:
- Use line-clamp-2 for title and description (prevents overflow).
- Use truncate + max-w-full on the badge.
- Ensure cards have overflow-hidden.
- Provide a prop or detect container to switch between sidebar (stack) and full-width (grid).
  Simplest: default to a responsive grid that collapses to 1 column, and when used in
  the sidebar pass a "stacked" prop to force 1 column.

TASK 2: FIX RESOURCE-HERO COMPONENT

Modify resources/views/components/public/resource-hero.blade.php.

Make it match the listing page hero:
- Wrap the hero in the SAME background as the listing hero:
  class="bg-primary-950 dark:bg-slate-900" (or match the exact listing hero classes).
- Add consistent vertical padding: py-16 (match listing).
- Structure inside a max-w container:
  - Breadcrumbs.
  - Type badge / category label (uppercase, primary-400).
  - Title (h1, text-3xl/4xl, white in dark hero).
  - Short description (muted).
  - Actions row (print, share, copy) with mt-6.
- Remove any extra empty container/margin that creates the big gap.

Ensure the hero text colors work on the navy background in BOTH light and dark mode:
- Title: text-white.
- Description: text-slate-300 / text-primary-100.
- Breadcrumbs: text-slate-300.

TASK 3: REMOVE THE EMPTY GAP

Inspect all 6 show.blade.php pages.

Find and remove the element/container causing the large empty space between the hero
(Share button) and the main content (Calculate card). Likely causes:
- An empty div or section.
- Excessive margin/padding (e.g., mt-24, py-24).
- A placeholder container left from Build 38.

Tighten spacing so the content follows the hero with normal padding (py-8 / py-12).

TASK 4: APPLY CONSISTENTLY TO ALL 6 DETAIL PAGES

Modify each show.blade.php:
- calculators, tools, reference-ranges, sops, interpretation, knowledge.

For each:
- Use the fixed <x-public.resource-hero>.
- Place <x-public.resource-meta-bar> directly under the hero (inside or right after).
- Ensure the main content area uses the standard container + padding.
- Place <x-public.related-resources> at the bottom with the corrected layout.
  - If related resources are in the sidebar, pass the "stacked" prop.
  - If full-width, use the responsive grid.
- Verify no leftover empty containers.

TASK 5: VERIFY META BAR

Modify resources/views/components/public/resource-meta-bar.blade.php if needed:
- Ensure it renders on the navy hero or immediately below with correct contrast.
- Category/tag chips must be readable in dark mode.
- Wraps correctly on mobile.

TASK 6: DARK MODE + MOBILE VERIFICATION

For all 6 detail pages,