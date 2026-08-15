labgato/
├── app/
│   └── Services/
│       └── RelatedResourcesService.php
├── resources/
│   ├── css/
│   │   └── app.css
│   └── views/
│       ├── components/
│       │   └── public/
│       │       ├── resource-hero.blade.php
│       │       ├── related-resources.blade.php
│       │       ├── share-button.blade.php
│       │       └── resource-meta-bar.blade.php
│       └── public/
│           ├── calculators/
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
            └── ResourcePageImprovementTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD38-RESOURCE-PAGES

CONTEXT:
LabGato's six public resource detail pages need a comprehensive improvement pass. They must be visually consistent with the new Abyss Blue theme (light + dark), have a unified structure, and include high-value features like related resources and share buttons.

OBJECTIVE:
Improve all six resource detail pages (calculators, tools, reference-ranges, sops, interpretation, knowledge) with a consistent hero header, related resources section, share button, better typography, and full dark mode + mobile support.

EXISTING ARCHITECTURE:
- Abyss Blue theme with dark mode (Build 35).
- Print/copy/unit-conversion features (Build 22).
- Table of contents for Knowledge/Interpretation (Build 23).
- Contributor credit blocks (Build 29).
- Categories/tags system.

FILES TO INSPECT:
- resources/views/public/calculators/show.blade.php
- resources/views/public/tools/show.blade.php
- resources/views/public/reference-ranges/show.blade.php
- resources/views/public/sops/show.blade.php
- resources/views/public/interpretation/show.blade.php
- resources/views/public/knowledge/show.blade.php
- resources/css/app.css
- app/Models/Category.php

FILES TO CREATE:
- app/Services/RelatedResourcesService.php
- resources/views/components/public/resource-hero.blade.php
- resources/views/components/public/resource-meta-bar.blade.php
- resources/views/components/public/related-resources.blade.php
- resources/views/components/public/share-button.blade.php
- tests/Feature/PublicSite/ResourcePageImprovementTest.php

FILES TO MODIFY:
- resources/css/app.css
- All 6 resource show.blade.php pages

TASK 1: RELATED RESOURCES SERVICE

Create app/Services/RelatedResourcesService.php.

Method:
- getRelated($resource, string $type, int $limit = 4): Collection

Logic:
1. Get the categories and tags of the current resource.
2. Find other PUBLISHED resources that share at least one category or tag.
3. Prioritize resources of the same type, then other types.
4. Exclude the current resource.
5. Limit to $limit results.
6. Return a collection with title, type, url, and short_description.

Implementation note:
- Query across the relevant resource tables based on shared category/tag IDs.
- Keep queries efficient (use whereIn on category/tag IDs).

TASK 2: RESOURCE HERO COMPONENT

Create resources/views/components/public/resource-hero.blade.php.

A reusable top section for all resource pages.

Props:
- title: string
- type: string (for the badge)
- typeLabel: string
- shortDescription: string
- (slots for type-specific metadata and actions)

Layout:
- Breadcrumbs at top.
- Type badge (color-coded per type).
- Title (h1, large).
- Short description (lead text).
- Action buttons row (print, share, copy — passed via slot).

Styling:
- Use Abyss Blue theme.
- Dark mode support.
- Generous vertical spacing.

TASK 3: RESOURCE META BAR COMPONENT

Create resources/views/components/public/resource-meta-bar.blade.php.

A consistent metadata strip shown below the hero.

Props:
- categories: collection
- tags: collection
- contributor: User or null
- lastVerifiedAt: date or null
- publishedAt: date

Layout:
- Category chips (linked).
- Tag chips (linked).
- Contributor credit (using existing contributor-credit component).
- "Last verified" badge (if applicable).
- Published date.

Styling:
- Horizontal flex layout, wraps on mobile.
- Subtle separators.
- Dark mode support.

TASK 4: SHARE BUTTON COMPONENT

Create resources/views/components/public/share-button.blade.php.

A button to copy the current page URL.

Behavior:
- On click, copy window.location.href to clipboard.
- Show "Link copied!" feedback for 2 seconds.
- Use the Clipboard API with a fallback.
- Accessible (aria-label, aria-live for feedback).

Optional enhancement:
- If navigator.share is available (mobile), offer native share.

Styling:
- Icon (link/share SVG) + "Share" text.
- Matches theme button styles.

TASK 5: CONTENT TYPOGRAPHY

Modify resources/css/app.css.

Add a `.resource-content` class with typography styles for rich text content:

.resource-content {
  - Headings (h2, h3, h4): proper sizes, margins, font-weight.
  - Paragraphs: line-height 1.7, margin-bottom.
  - Lists (ul, ol): proper indentation and spacing.
  - Links: primary color, underline on hover.
  - Tables: borders, padding, striped rows.
  - Blockquotes: styled with border-left.
  - Code: monospace, background.
}

Add dark mode variants for .resource-content.

Apply this class to the content container on SOP, Knowledge, and Interpretation pages.

TASK 6: RELATED RESOURCES COMPONENT

Create resources/views/components/public/related-resources.blade.php.

Props: resources (collection), currentType.

Layout:
- Section title: "Related Resources".
- Grid of resource cards (2-4 columns).
- Each card: type badge, title, short description, link.
- Empty state: hide the section if no related resources.

Styling:
- Consistent with theme.
- Dark mode support.

TASK 7: UPDATE ALL 6 RESOURCE PAGES

Modify each of the 6 show.blade.php pages to use the new components:

1. Replace the existing header with <x-public.resource-hero>.
2. Add <x-public.resource-meta-bar> below the hero.
3. Add <x-public.share-button> to the hero actions (alongside existing print/copy).
4. Apply .resource-content class to the main content area.
5. Add <x-public.related-resources> at the bottom (before footer).
6. Ensure dark mode classes are applied throughout.
7. Verify mobile responsiveness.

Type-specific notes:
- Calculators: keep the calculator runner, add related calculators.
- Tools: keep the tool runner, add related tools.
- Reference Ranges: keep the variations table + unit toggle, add related.
- SOPs: keep the structured sections + print, add related.
- Interpretation: keep the disclaimer + TOC, add related.
- Knowledge: keep the TOC + learning objectives, add related.

TASK 8: DARK MODE + MOBILE VERIFICATION

For all 6 pages, verify:
- All text is readable in dark mode (contrast).
- All components (hero, meta bar, related, content) render correctly in dark mode.
- Tables scroll horizontally on mobile.
- Meta bar wraps correctly on mobile.
- Related resources grid collapses to 1 column on mobile.
- Buttons and chips are touch-friendly (≥44px).

TASK 9: FEATURE TESTS

Create tests/Feature/PublicSite/ResourcePageImprovementTest.php:
- Test each resource detail page renders without errors.
- Test the resource hero displays title and type badge.
- Test the meta bar shows categories and tags.
- Test the share button is present.
- Test related resources section appears when related resources exist.
- Test related resources section is hidden when none exist.
- Test contributor credit appears on contributed resources.
- Test the .resource-content class is applied to content pages.

DEFINITION OF DONE:
- All 6 resource pages use the new resource-hero component.
- Meta bar consistently shows categories, tags, contributor, and dates.
- Share button works on all resource pages.
- Related resources section appears with relevant results.
- Content typography is improved and consistent.
- All pages render correctly in light and dark modes.
- All pages are mobile responsive.
- All feature tests pass.