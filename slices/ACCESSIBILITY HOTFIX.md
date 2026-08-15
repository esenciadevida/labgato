LABGOTO/
├── config/
│   └── LABGOTO.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   ├── auth.blade.php
│       │   ├── error.blade.php
│       │   ├── public.blade.php
│       │   └── admin.blade.php
│       ├── components/
│       │   ├── ui/
│       │   │   ├── button.blade.php
│       │   │   ├── badge.blade.php
│       │   │   ├── table.blade.php
│       │   │   ├── alert.blade.php
│       │   │   ├── empty-state.blade.php
│       │   │   └── (any component with decorative SVGs)
│       │   ├── admin/
│       │   │   ├── sidebar.blade.php
│       │   │   ├── header.blade.php
│       │   │   ├── navigation.blade.php
│       │   │   ├── navigation-item.blade.php
│       │   │   └── resources/
│       │   │       ├── category-selector.blade.php
│       │   │       └── tag-input.blade.php
│       │   └── public/
│       │       ├── header.blade.php
│       │       ├── footer.blade.php
│       │       ├── navigation.blade.php
│       │       ├── breadcrumbs.blade.php
│       │       └── search/
│       │           ├── form.blade.php
│       │           └── result-item.blade.php
│       ├── admin/
│       │   ├── categories/
│       │   ├── tags/
│       │   ├── calculators/
│       │   ├── tools/
│       │   ├── reference-ranges/
│       │   ├── sops/
│       │   ├── interpretation/
│       │   └── knowledge/
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── forgot-password.blade.php
│       │   └── reset-password.blade.php
│       ├── errors/
│       │   ├── 403.blade.php
│       │   ├── 404.blade.php
│       │   ├── 419.blade.php
│       │   ├── 429.blade.php
│       │   ├── 500.blade.php
│       │   └── 503.blade.php
│       └── livewire/
│           └── public/
│               ├── calculators/
│               │   └── calculator-runner.blade.php
│               └── tools/
│                   └── tool-runner.blade.php
├── database/
│   └── factories/
│       └── ReferenceRangeFactory.php
└── tests/
    └── Feature/
        └── PublicSite/
            └── ReferenceRanges/
                └── ReferenceRangeDetailTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-HOTFIX-A11Y-AUDIT

CONTEXT:
LABGOTO MVP is complete and hardened. An accessibility audit identified 6 key findings that must be fixed before Phase 05. Additionally, a flaky ReferenceRange test needs fixing, and a tools feature flag should be added for consistency.

This hotfix addresses all findings systematically.

OBJECTIVE:
Fix all 6 accessibility audit findings, resolve the flaky ReferenceRange test, and add the tools feature flag.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Target accessibility standard is WCAG 2.2 AA.
- All decorative SVGs must use aria-hidden="true".
- All tables must use proper th scope attributes.
- All touch targets must be at least 44x44 CSS pixels.
- All pages must have exactly one h1.
- All layouts must use semantic HTML landmarks.
- Livewire loading indicators and field errors must be in live regions.

FILES TO INSPECT:
- All files listed in the target file paths.
- config/LABGOTO.php
- database/factories/ReferenceRangeFactory.php
- tests/Feature/PublicSite/ReferenceRanges/ReferenceRangeDetailTest.php

FILES TO MODIFY:
- All files with decorative SVGs.
- All admin table views.
- All components with pagination or small touch targets.
- resources/views/layouts/auth.blade.php
- resources/views/layouts/error.blade.php
- resources/views/livewire/public/calculators/calculator-runner.blade.php
- resources/views/livewire/public/tools/tool-runner.blade.php
- config/LABGOTO.php
- database/factories/ReferenceRangeFactory.php
- tests/Feature/PublicSite/ReferenceRanges/ReferenceRangeDetailTest.php

TASK 1: FIX DECORATIVE SVG ARIA-HIDDEN (Finding 1)

Audit all 27 decorative SVGs identified in the audit report.

Requirements:
1. For each decorative SVG, add aria-hidden="true" and focusable="false".
2. Do NOT add aria-hidden to informative SVGs that convey meaning.
3. If an SVG is informative, add a <title> element inside it and reference it with aria-labelledby.

Pattern for decorative SVGs:
<svg aria-hidden="true" focusable="false" ...>

Search locations:
- resources/views/components/ui/ (all components)
- resources/views/components/admin/ (sidebar, header, navigation)
- resources/views/components/public/ (header, footer, navigation, breadcrumbs, search)
- resources/views/admin/ (all admin views)
- resources/views/public/ (all public views)
- resources/views/livewire/ (all Livewire views)

TASK 2: FIX TABLE ACCESSIBILITY (Finding 2)

Fix the 5 admin tables missing scope="row" and add captions.

Requirements:
1. For all admin index/listing tables, ensure the Name column header uses:
   <th scope="col">Name</th>
2. For all admin index/listing tables, ensure the first cell in each row uses:
   <th scope="row">[Resource Name]</th>
3. Add a <caption> element to all tables. For admin tables, use a visually hidden caption or aria-label:
   <caption class="sr-only">List of [resource type]</caption>
   OR
   <table aria-label="List of [resource type]">

Affected tables (5 admin tables):
- Categories index
- Tags index
- Calculators index
- Tools index
- Reference Ranges index
- SOPs index
- Interpretation index
- Knowledge index

Also check any tables in public views (e.g., Reference Range detail page).

TASK 3: FIX TOUCH TARGETS (Finding 3)

Increase touch targets to meet 44x44 CSS pixels.

Requirements:
1. Pagination controls:
   - Add minimum padding: px-3 py-2 or min-w-[44px] min-h-[44px]
   - Ensure clickable area is at least 44x44.

2. Admin Edit/Delete buttons:
   - Increase padding to meet 44px minimum.
   - Use px-3 py-2 or add min-w/min-h classes.

3. Header/sidebar controls (4 controls):
   - Mobile menu toggle
   - User dropdown trigger
   - Sidebar collapse toggle (if exists)
   - Any other header/sidebar interactive controls
   - Add padding to meet 44px minimum.

4. Chip-links (category badges, tag badges):
   - Add padding to meet 44px minimum height.
   - Use px-3 py-1.5 or similar to increase clickable area.
   - Ensure badges are not too small to tap on mobile.

General approach:
- Use min-w-[44px] min-h-[44px] utility classes where needed.
- Or increase padding (px/py) to achieve 44px dimensions.
- Do not change visual appearance drastically, only increase clickable area.

TASK 4: FIX AUTH PAGE STRUCTURE (Finding 4)

Fix duplicate h1 and missing main landmark.

Requirements:
1. Remove the <h1> from layouts/auth.blade.php.
   - The individual auth pages (login, forgot-password, reset-password) already have their own <h1>.
   - The layout should NOT have an <h1>.
2. Add <main> landmark to layouts/auth.blade.php.
   - Wrap the content yield/slot in <main id="main-content">...</main>
3. Add <main> landmark to layouts/error.blade.php.
   - Wrap the content yield/slot in <main id="main-content">...</main>
4. Verify that layouts/public.blade.php and layouts/admin.blade.php already have <main> landmarks. If not, add them.

TASK 5: FIX LIVEWIRE LIVE REGIONS (Finding 5)

Add aria-live regions to Livewire loading indicators and field-level errors.

Requirements:
1. Calculator Runner (calculator-runner.blade.php):
   - Wrap the loading indicator in <div aria-live="polite" role="status">...</div>
   - Wrap field-level validation errors in <div aria-live="assertive" role="alert">...</div>
   - The result region already uses aria-live correctly, do not change it.

2. Tool Runner (tool-runner.blade.php):
   - Wrap the loading indicator in <div aria-live="polite" role="status">...</div>
   - Wrap field-level validation errors in <div aria-live="assertive" role="alert">...</div>
   - The result region already uses aria-live correctly, do not change it.

Pattern for loading indicator:
<div wire:loading wire:target="calculate" aria-live="polite" role="status" class="...">
    Loading...
</div>

Pattern for field errors:
@error('field_name')
    <div aria-live="assertive" role="alert" class="text-sm text-danger-600">
        {{ $message }}
    </div>
@enderror

TASK 6: VERIFY PASSING ITEMS (Finding 6)

The following items passed the audit and do not need changes:
- Images (alt text is correct)
- Button naming (accessible names are correct)
- Interactive SVG names (accessible names are correct)
- Status/alert roles (correct ARIA roles)
- Public h1s (exactly one h1 per page)

No action required for these items. Verify they remain correct after other fixes.

TASK 7: FIX FLAKY REFERENCERANGE TEST

Fix the flaky ReferenceRangeDetailTest.

Requirements:
1. In tests/Feature/PublicSite/ReferenceRanges/ReferenceRangeDetailTest.php:
   - Find the test that creates two ReferenceRange records.
   - Give them explicit, distinct names instead of relying on factory-generated names.
   - Example:
     $range1 = ReferenceRange::factory()->create(['name' => 'Hemoglobin - Adult Male', 'slug' => 'hemoglobin-adult-male', 'analyte_name' => 'Hemoglobin']);
     $range2 = ReferenceRange::factory()->create(['name' => 'Hemoglobin - Adult Female', 'slug' => 'hemoglobin-adult-female', 'analyte_name' => 'Hemoglobin']);

2. In database/factories/ReferenceRangeFactory.php:
   - Ensure the factory generates unique slugs by default.
   - Use a sequence or unique() modifier to prevent collisions.

TASK 8: ADD TOOLS FEATURE FLAG

Add a tools feature flag to config/LABGOTO.php for consistency.

Requirements:
1. Add the following to config/LABGOTO.php:

'features' => [
    'calculators' => env('FEATURE_CALCULATORS', true),
    'tools' => env('FEATURE_TOOLS', true),
    'reference_ranges' => env('FEATURE_REFERENCE_RANGES', true),
    'sops' => env('FEATURE_SOPS', true),
    'interpretation' => env('FEATURE_INTERPRETATION', true),
    'knowledge' => env('FEATURE_KNOWLEDGE', true),
    'search' => env('FEATURE_SEARCH', true),
    'taxonomy' => env('FEATURE_TAXONOMY', true),
],

2. Update .env.example to include:

FEATURE_CALCULATORS=true
FEATURE_TOOLS=true
FEATURE_REFERENCE_RANGES=true
FEATURE_SOPS=true
FEATURE_INTERPRETATION=true
FEATURE_KNOWLEDGE=true
FEATURE_SEARCH=true
FEATURE_TAXONOMY=true

3. Note: Do NOT gate any existing functionality behind these flags in this hotfix. The flags are added for future use. Gating will be implemented in a future sprint if needed.

DEFINITION OF DONE:
- All 27 decorative SVGs have aria-hidden="true" and focusable="false".
- All admin tables have scope="row" on the Name column.
- All tables have a caption or aria-label.
- All touch targets are at least 44x44 CSS pixels.
- Auth pages have exactly one h1.
- Auth and error layouts have <main> landmarks.
- Livewire loading indicators are in aria-live="polite" regions.
- Livewire field-level errors are in aria-live="assertive" regions.
- Flaky ReferenceRange test is fixed with explicit distinct names.
- Tools feature flag is added to config/LABGOTO.php.
- All existing tests pass.
- No new accessibility regressions are introduced.