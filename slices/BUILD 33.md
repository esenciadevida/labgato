labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       └── BulkActionController.php
│   │   └── Requests/
│   │       └── Admin/
│   │           └── BulkActionRequest.php
│   └── Services/
│       └── Admin/
│           └── BulkActionService.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── admin/
│       │       ├── bulk-actions-bar.blade.php
│       │       └── listing-select.blade.php
│       └── admin/
│           ├── calculators/
│           │   └── index.blade.php
│           ├── tools/
│           │   └── index.blade.php
│           ├── reference-ranges/
│           │   └── index.blade.php
│           ├── sops/
│           │   └── index.blade.php
│           ├── interpretation/
│           │   └── index.blade.php
│           └── knowledge/
│               └── index.blade.php
├── routes/
│   └── admin.php
└── tests/
    └── Feature/
        └── Admin/
            ├── BulkActionTest.php
            └── AdminRedirectTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD33-BULK-ACTIONS

CONTEXT:
LabGato admins need to perform bulk actions (publish, archive, delete) on resources. Additionally, single actions currently redirect admins away from the page they're working on, which is disruptive. This build adds bulk actions and fixes the redirect behavior so admins stay on their page.

OBJECTIVE:
Implement a reusable bulk action system across all 6 resource types, fix admin redirect behavior to keep admins on their current page, and audit admin pages for live/real data.

EXISTING ARCHITECTURE:
- 6 resource types with separate admin controllers and listing pages.
- ResourceStatus enum with lifecycle states.
- RBAC permissions per resource type (e.g., calculators.publish, calculators.delete).
- Admin listing pages are Blade views with filters and pagination.

FILES TO INSPECT:
- app/Enums/ResourceStatus.php
- app/Http/Controllers/Admin/CalculatorController.php (and all other resource controllers)
- resources/views/admin/calculators/index.blade.php (and all other listing views)
- routes/admin.php
- app/Models/ (all 6 resource models)

FILES TO CREATE:
- app/Services/Admin/BulkActionService.php
- app/Http/Controllers/Admin/BulkActionController.php
- app/Http/Requests/Admin/BulkActionRequest.php
- resources/views/components/admin/bulk-actions-bar.blade.php
- resources/views/components/admin/listing-select.blade.php
- tests/Feature/Admin/BulkActionTest.php
- tests/Feature/Admin/AdminRedirectTest.php

FILES TO MODIFY:
- resources/views/admin/calculators/index.blade.php
- resources/views/admin/tools/index.blade.php
- resources/views/admin/reference-ranges/index.blade.php
- resources/views/admin/sops/index.blade.php
- resources/views/admin/interpretation/index.blade.php
- resources/views/admin/knowledge/index.blade.php
- All 6 resource admin controllers (fix redirects)
- routes/admin.php

TASK 1: BULK ACTION SERVICE

Create app/Services/Admin/BulkActionService.php.

A reusable service that processes bulk actions across resource types.

Methods:
- publish(string $type, array $ids): int (returns count of affected)
- archive(string $type, array $ids): int
- delete(string $type, array $ids): int

Logic:
- Resolve the model class from the type string (calculator, tool, reference-range, sop, interpretation, knowledge).
- Validate the action is allowed for the type.
- Query the resources by ID.
- Apply the action (update status or delete).
- Enforce the current user's permission for each action.
- Audit log each bulk action.
- Return the count of affected resources.

Type-to-model mapping:
- 'calculator' => Calculator
- 'tool' => LaboratoryTool
- 'reference-range' => ReferenceRange
- 'sop' => SopResource
- 'interpretation' => InterpretationResource
- 'knowledge' => KnowledgeResource

Permission mapping:
- publish → {type}.publish (e.g., calculators.publish)
- archive → {type}.edit (archiving is an edit action)
- delete → {type}.delete

TASK 2: BULK ACTION CONTROLLER

Create app/Http/Controllers/Admin/BulkActionController.php.

Method: process(BulkActionRequest $request, string $type)

Route: POST /admin/bulk/{type}

Logic:
1. Validate the request (action + ids).
2. Check permission for the action on the type.
3. Call BulkActionService.
4. Redirect back to the originating listing page with filters preserved and a success flash message.

Create BulkActionRequest validation:
- action: required, in [publish, archive, delete]
- ids: required, string (comma-separated IDs)
- Parse ids into an array of integers.

TASK 3: BULK ACTION BAR COMPONENT

Create resources/views/components/admin/bulk-actions-bar.blade.php.

Props:
- type: string (the resource type)
- route: string (the bulk action route)

An Alpine.js component that:
- Is hidden when no items are selected.
- Shows the count of selected items.
- Displays action buttons: Publish, Archive, Delete.
- Publish and Archive: submit directly.
- Delete: show a confirmation dialog before submitting.
- Uses a form POST with hidden inputs for ids and action.

Alpine.js state (managed by the parent listing):
- selected: array of selected IDs.
- The bulk bar reads selected and populates the form on submit.

TASK 4: LISTING SELECT COMPONENT

Create resources/views/components/admin/listing-select.blade.php.

A reusable Alpine.js wrapper for listing pages that provides:
- Select-all checkbox (header).
- Per-row checkbox.
- Selection state management.
- Integration with the bulk action bar.

Alpine component structure:
x-data="{
    selected: [],
    allIds: [],
    selectAll: false,
    toggle(id) { ... },
    toggleAll() { ... },
    isSelected(id) { return this.selected.includes(id); }
}"

TASK 5: ADD CHECKBOXES TO LISTING PAGES

Modify all 6 admin listing pages (calculators, tools, reference-ranges, sops, interpretation, knowledge):

1. Wrap the table in the listing-select Alpine component.
2. Add a checkbox column as the first column.
3. Add a select-all checkbox in the header.
4. Add the bulk-actions-bar component above the table.
5. Pass the list of resource IDs to the Alpine component (via data attribute or inline).
6. Ensure filters and pagination still work.

TASK 6: FIX ADMIN REDIRECTS (STAY ON PAGE)

Modify all 6 resource admin controllers.

Current problem: After store/update/destroy/publish, admins are redirected away from the page they were on.

Fix:
- For update actions (from edit page): redirect back to the edit page with a success flash.
- For destroy actions (from listing): redirect back to the listing with filters preserved.
- For publish/archive actions: redirect back to the originating page (use redirect()->back() or preserve the intended URL).
- Preserve query string filters and pagination where applicable.

Implementation approach:
- Use redirect()->back() with a flash message for most actions.
- For actions triggered from the listing with filters, preserve the query string.
- Ensure flash messages are displayed on the redirected page.

Add a helper or use session to preserve the listing URL with filters:
- On listing page load, store the current URL (with query string) in session as 'intended_listing_url'.
- On actions, redirect to this stored URL.

TASK 7: ADMIN LIVE DATA AUDIT

Audit all admin pages to ensure they display live, real data.

Check:
- Admin dashboard shows real counts (not placeholders).
- All listing pages query and display actual resources.
- No "coming soon" or fake data remains.
- No stale cached data (verify cache invalidation on updates).
- Analytics dashboard shows real aggregated data.
- Review queue shows real pending contributions.

Fix any issues found:
- Replace placeholder text with real queries.
- Clear/invalidate caches appropriately.
- Ensure counts are accurate.

TASK 8: FEATURE TESTS

Create tests/Feature/Admin/BulkActionTest.php:
- Test bulk publish updates status for selected resources.
- Test bulk archive updates status.
- Test bulk delete removes resources.
- Test bulk action respects permissions (user without permission is blocked).
- Test bulk action requires valid action.
- Test bulk action requires at least one ID.
- Test bulk action is audit logged.
- Test bulk delete requires confirmation (handled client-side, but verify endpoint processes correctly).

Create tests/Feature/Admin/AdminRedirectTest.php:
- Test updating a resource redirects back to the edit page.
- Test deleting a resource from listing redirects back to the listing.
- Test filters are preserved after an action.
- Test flash message is displayed after redirect.

SECURITY REQUIREMENTS:
- Bulk actions enforce RBAC permissions per resource type.
- Bulk delete requires explicit confirmation.
- IDs are validated as integers.
- Users cannot bulk-act on resources they lack permission for.
- All bulk actions are audit logged.

DEFINITION OF DONE:
- BulkActionService handles publish/archive/delete for all 6 resource types.
- Bulk action bar appears on all 6 listing pages when items are selected.
- Select-all and per-row checkboxes work.
- Delete requires confirmation.
- Admin actions redirect back to the originating page (no more "off the page").
- Filters are preserved after actions.
- All admin pages show live, real data.
- All feature tests pass.