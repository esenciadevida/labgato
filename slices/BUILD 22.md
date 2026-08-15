labgato/
├── app/
│   └── Http/
│       └── Controllers/
│           └── Admin/
│               └── AnalyticsController.php
├── database/
│   └── migrations/
│       └── xxxx_add_si_unit_to_reference_ranges_table.php
├── resources/
│   ├── css/
│   │   └── app.css
│   └── views/
│       ├── components/
│       │   ├── admin/
│       │   │   ├── sidebar.blade.php
│       │   │   └── navigation.blade.php
│       │   └── public/
│       │       ├── copy-button.blade.php
│       │       ├── print-button.blade.php
│       │       └── unit-toggle.blade.php
│       └── public/
│           ├── calculators/
│           │   └── show.blade.php
│           ├── reference-ranges/
│           │   └── show.blade.php
│           └── sops/
│               └── show.blade.php
├── routes/
│   └── admin.php
└── tests/
    └── Feature/
        ├── Admin/
        │   └── AnalyticsRouteTest.php
        └── PublicSite/
            └── ReferenceRangeConversionTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD22-ANALYTICS-FIX-AND-LAB-FEATURES

CONTEXT:
LabGato V1.1 is complete. The analytics dashboard exists but its sidebar nav link is a dead "coming soon" placeholder. We are fixing that and adding three high-value features for laboratory professionals: print stylesheet, copy-to-clipboard, and unit conversion toggle.

OBJECTIVE:
1. Fix the Analytics navigation by creating a dedicated /admin/analytics route.
2. Add a print stylesheet for SOPs and Reference Ranges.
3. Add copy-to-clipboard for formulas and reference values.
4. Add a unit conversion toggle for Reference Ranges.

EXISTING ARCHITECTURE:
- Tailwind CSS, Alpine.js, Livewire.
- Advanced analytics view exists at admin.dashboard-advanced.
- reference_ranges table has conversion_factor column (from Phase 04).
- Hostinger shared hosting. WCAG 2.2 AA required.

FILES TO CREATE:
- app/Http/Controllers/Admin/AnalyticsController.php
- database/migrations/xxxx_add_si_unit_to_reference_ranges_table.php
- resources/views/components/public/copy-button.blade.php
- resources/views/components/public/print-button.blade.php
- resources/views/components/public/unit-toggle.blade.php
- tests/Feature/Admin/AnalyticsRouteTest.php
- tests/Feature/PublicSite/ReferenceRangeConversionTest.php

FILES TO MODIFY:
- routes/admin.php
- resources/views/components/admin/sidebar.blade.php
- resources/views/components/admin/navigation.blade.php
- resources/views/public/calculators/show.blade.php
- resources/views/public/reference-ranges/show.blade.php
- resources/views/public/sops/show.blade.php
- resources/css/app.css

TASK 1: FIX ANALYTICS NAVIGATION

Create app/Http/Controllers/Admin/AnalyticsController.php.

Method: index()
- Inject AnalyticsDashboardService.
- Fetch kpis, resourceGrowth, searchInsights, aiUtilization.
- Return view('admin.dashboard-advanced', $data).

Add route in routes/admin.php:

GET /admin/analytics
- Name: admin.analytics.index
- Controller: Admin\AnalyticsController@index
- Middleware: auth, active, permission:analytics.view

Update the sidebar/navigation components:
- Find the "System → Analytics" placeholder item.
- Change it from a placeholder/coming-soon to a real link pointing to route('admin.analytics.index').
- Ensure it only renders for users with analytics.view permission.

Keep /admin/dashboard as-is (it still resolves to advanced or basic based on permission). The dedicated /admin/analytics gives a clean home for the System nav link.

TASK 2: PRINT STYLESHEET FOR SOPS & REFERENCE RANGES

Add print styles using Tailwind's print: variant and a small @media print block in app.css.

Add to app.css:

@media print {
    .no-print { display: none !important; }
    .print-area { display: block !important; }
    body { background: white; }
    a[href]:after { content: none; }
}

Modify sops/show.blade.php and reference-ranges/show.blade.php:
- Wrap the header, footer, sidebar, and navigation with print:hidden (or class="no-print").
- Keep the main content visible for print.
- Add a "Print" button (using the new print-button component) with class="print:hidden".

Create resources/views/components/public/print-button.blade.php.

Props: none (or optional label).

Render:
<button type="button" onclick="window.print()" class="..." aria-label="Print this page">
    [printer icon] Print
</button>

Rules:
- Hide the print button itself when printing.
- Ensure the SOP metadata table and reference range variations table print cleanly.

TASK 3: COPY-TO-CLIPBOARD FOR FORMULAS & REFERENCE VALUES

Create resources/views/components/public/copy-button.blade.php.

Props:
- value: string (the text to copy)
- label: optional string (accessible label)

Behavior:
- On click, use navigator.clipboard.writeText(value).
- Provide fallback for browsers without Clipboard API (create a temporary textarea, select, execCommand('copy')).
- Show "Copied!" feedback for 2 seconds.
- Announce via aria-live="polite".

Render (Alpine.js):
<div x-data="{ copied: false, copy() { ... } }">
    <button @click="copy()" :aria-label="copied ? 'Copied' : 'Copy to clipboard'">
        <span x-show="!copied">Copy</span>
        <span x-show="copied">Copied!</span>
    </button>
    <span x-show="copied" aria-live="polite" class="sr-only">Copied to clipboard</span>
</div>

Modify calculators/show.blade.php:
- Add a copy button next to the formula_display value (e.g., "Na - (Cl + HCO3)").

Modify reference-ranges/show.blade.php:
- Add a copy button next to the reference interval values.
- Copy text format: "Analyte: {value} {unit}".

TASK 4: UNIT CONVERSION TOGGLE FOR REFERENCE RANGES

First, add an si_unit column to reference_ranges (needed to label the converted unit).

Create migration: add_si_unit_to_reference_ranges_table
- Add column: si_unit (string, nullable, after unit).

Update the ReferenceRange model to include si_unit in $fillable.

Create resources/views/components/public/unit-toggle.blade.php.

Props:
- range: the ReferenceRange model.

Behavior (Alpine.js):
- Toggle between "conventional" and "si" display.
- When "si" is selected, multiply lower_limit, upper_limit, critical_low, critical_high by conversion_factor.
- Display the si_unit label instead of the conventional unit.
- If conversion_factor or si_unit is null, hide the toggle.

Render:
<div x-data="{ showSI: false, factor: {{ $range->conversion_factor ?? 1 }} }">
    <button @click="showSI = !showSI" aria-pressed="showSI">
        <span x-show="!showSI">Show SI Units</span>
        <span x-show="showSI">Show Conventional Units</span>
    </button>
    [Value display switches based on showSI]
</div>

Modify reference-ranges/show.blade.php:
- Add the unit-toggle component above the reference range table.
- Make the value cells reactive to the toggle.
- Round converted values to appropriate precision (use 2 decimal places by default).

TASK 5: FEATURE TESTS

Create tests/Feature/Admin/AnalyticsRouteTest.php.

Required tests:
1. test_analytics_route_accessible_with_permission
   - Authenticate user with analytics.view.
   - GET /admin/analytics.
   - Assert 200 and advanced dashboard renders.
2. test_analytics_route_forbidden_without_permission
   - Authenticate user without analytics.view.
   - GET /admin/analytics.
   - Assert 403.
3. test_analytics_nav_link_visible_for_authorized_user
   - Authenticate user with analytics.view.
   - Visit admin dashboard.
   - Assert "Analytics" nav link is present and not "coming soon".

Create tests/Feature/PublicSite/ReferenceRangeConversionTest.php.

Required tests:
1. test_reference_range_detail_shows_unit_toggle_when_conversion_factor_exists
   - Seed a reference range with conversion_factor and si_unit.
   - Visit the detail page.
   - Assert the unit toggle is visible.
2. test_reference_range_detail_hides_unit_toggle_when_no_conversion_factor
   - Seed a reference range without conversion_factor.
   - Visit the detail page.
   - Assert the unit toggle is not visible.
3. test_reference_range_shows_copy_button
   - Visit a reference range detail page.
   - Assert the copy button is present.

DEFINITION OF DONE:
- /admin/analytics route works and the sidebar nav link is no longer "coming soon".
- SOP and Reference Range pages print cleanly with navigation hidden.
- Print button is present and hidden when printing.
- Copy-to-clipboard works for formulas and reference values with accessible feedback.
- Unit conversion toggle works for reference ranges with conversion_factor and si_unit.
- si_unit migration runs successfully.
- All feature tests pass.
- Reduced motion and keyboard accessibility are respected.