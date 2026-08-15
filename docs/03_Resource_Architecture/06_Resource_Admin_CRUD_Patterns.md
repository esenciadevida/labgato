# 06 — Resource Admin CRUD Patterns

## Depends On

This document depends on:

- The approved Phase 02 documentation (specifically Admin Portal Foundation, RBAC Implementation).
- The approved Phase 03 Resource Foundation document.
- The approved Phase 03 Taxonomy Implementation document.
- The approved Phase 03 Calculators Architecture document.
- The approved Phase 03 Laboratory Tools Architecture document.

## Defines

This document defines:

- The standard administrative CRUD route patterns for resources.
- The Index (listing) page pattern.
- The Create page pattern.
- The Edit page pattern.
- The Delete and Archive patterns.
- The form validation and error handling patterns.
- The component reuse strategy for admin resource management.
- The testing requirements for admin CRUD.

## Affects

This document affects:

- All future resource admin controllers.
- All future resource admin Livewire components.
- All future resource admin Blade views.
- All future resource admin form requests.
- The Resource Testing Strategy document.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes consistent, reusable CRUD patterns for all resource types.
- Prevents disconnected or inconsistent admin interfaces.
- Ensures all resource management actions are secure, validated, and audited.
- Provides the blueprint for building resource admin modules efficiently.

---

# 1. Purpose

This document establishes the Resource Admin CRUD Patterns for LABGOTO.

Every resource type (Calculators, Tools, Reference Ranges, etc.) requires administrative management. To ensure consistency, security, and maintainability, all resource admin modules must follow the same CRUD patterns.

This document defines those patterns so that each resource vertical slice can be implemented rapidly and consistently.

---

# 2. CRUD Route Patterns

All resource admin routes must follow standard RESTful conventions.

## 2.1 Route Structure

Base path: `/admin/{resource_module}`

Example for Calculators:

- `GET /admin/calculators` → Index
- `GET /admin/calculators/create` → Create Form
- `POST /admin/calculators` → Store
- `GET /admin/calculators/{id}/edit` → Edit Form
- `PUT/PATCH /admin/calculators/{id}` → Update
- `DELETE /admin/calculators/{id}` → Destroy

## 2.2 Route Naming

Route names must follow the pattern:

`admin.{resource_module}.{action}`

Examples:

- `admin.calculators.index`
- `admin.calculators.create`
- `admin.calculators.store`
- `admin.calculators.edit`
- `admin.calculators.update`
- `admin.calculators.destroy`

## 2.3 Route Middleware

All resource admin routes must be protected by:

- `auth`
- `active`
- `permission:{resource_module}.{action}`

Example:

Route::middleware(['auth', 'active', 'permission:calculators.view'])->group(function () {
    Route::get('/calculators', [CalculatorController::class, 'index'])->name('calculators.index');
});

---

# 3. Index Page Pattern (Listing)

The Index page displays a list of resources with filtering, search, and pagination.

## 3.1 Page Layout

- Page title (e.g., "Calculators").
- Search input field.
- Filter controls (status, category, tag).
- "Create" button (if user has create permission).
- Data table or resource grid.
- Pagination controls.

## 3.2 Data Table Columns

Standard columns for resource listings:

- Name (linked to edit page).
- Status badge (Draft, Published, Archived, etc.).
- Category/Tags (optional).
- Created date.
- Updated date.
- Actions (Edit, Delete, Archive).

## 3.3 Empty State

If no resources exist:
- Display a clear empty state message.
- Provide a "Create your first resource" call-to-action.

## 3.4 Search and Filtering

- Search by name or slug.
- Filter by status (All, Draft, Published, Archived).
- Filter by category.
- Filter by tag.

## 3.5 Pagination

- Default pagination: 15, 25, or 50 items per page.
- Use Laravel's built-in pagination.

---

# 4. Create Page Pattern

The Create page displays a form for creating a new resource.

## 4.1 Page Layout

- Page title (e.g., "Create Calculator").
- Resource form.
- "Cancel" button (returns to index).
- "Save" button.

## 4.2 Form Sections

Standard form sections for resource creation:

1. **Basic Information**
   - Name
   - Slug (auto-generated from name if left blank)
   - Short Description
   - Status (default: Draft)

2. **Resource-Specific Fields**
   - Calculators: Formula display, Engine, Input Schema, Output Schema, Interpretation Notes, References.
   - Tools: Tool Type, Engine, Input Schema, Output Schema, Instructions, References.

3. **Taxonomy**
   - Categories (multi-select or checkbox tree).
   - Tags (tag input component).

4. **SEO**
   - SEO Title
   - SEO Description

## 4.3 Form Validation

- Use Laravel Form Requests for validation.
- Display inline validation errors.
- Do not clear the form on validation failure.

## 4.4 Save Behavior

- On successful save:
  - Redirect to the edit page or index page.
  - Display a success flash message.
  - Log the creation in the audit log.
- On validation failure:
  - Redirect back with errors.
  - Preserve input data.

---

# 5. Edit Page Pattern

The Edit page displays a form for updating an existing resource.

## 5.1 Page Layout

- Page title (e.g., "Edit Calculator: Anion Gap").
- Resource form (pre-populated with existing data).
- "Cancel" button (returns to index).
- "Save Changes" button.
- "Delete" button (if user has delete permission).
- "Archive" button (if user has publish permission and resource is published).

## 5.2 Form Sections

Same as Create page, but pre-populated with existing data.

## 5.3 Status Transitions

- Allow status changes via a dropdown or radio buttons.
- Enforce status transition rules defined in the Resource Foundation.
- Log status changes in the audit log.

## 5.4 Save Behavior

- On successful save:
  - Redirect to the edit page or index page.
  - Display a success flash message.
  - Log the update in the audit log.
- On validation failure:
  - Redirect back with errors.
  - Preserve input data.

---

# 6. Delete & Archive Patterns

## 6.1 Delete Pattern

- Delete actions must require confirmation.
- Use a confirmation modal or native browser confirm().
- Only users with `{resource_module}.delete` permission can delete.
- Deletion may be soft delete or hard delete depending on the resource type and retention policy.
- Log the deletion in the audit log.

## 6.2 Archive Pattern

- Archive actions transition the resource status to `archived`.
- Archived resources are hidden from public view but preserved in the database.
- Only users with `{resource_module}.publish` permission can archive.
- Log the archive action in the audit log.

## 6.3 Bulk Actions (Future)

Bulk actions (e.g., bulk delete, bulk archive) may be implemented in a future phase.

For MVP, individual actions are sufficient.

---

# 7. Form Validation & Error Handling

## 7.1 Validation Rules

All resource forms must validate:

- Required fields.
- String lengths.
- Unique slugs.
- Valid status values.
- Valid JSON schemas (for calculators/tools).
- Valid taxonomy assignments.

## 7.2 Error Display

- Display validation errors inline near the relevant field.
- Use accessible error messaging.
- Do not expose stack traces or sensitive data.

## 7.3 Flash Messages

- Use success flash messages for successful operations.
- Use error flash messages for failed operations.
- Use warning flash messages for destructive actions.

---

# 8. Component Reuse Strategy

To ensure consistency and reduce duplication, use reusable Blade components for admin resource management.

## 8.1 Shared Components

- `x-admin.resource-table`: For resource listing tables.
- `x-admin.resource-form`: For resource form layouts.
- `x-admin.form-section`: For grouping form fields.
- `x-admin.status-badge`: For displaying resource status.
- `x-admin.delete-button`: For delete actions with confirmation.
- `x-admin.archive-button`: For archive actions.

## 8.2 Component Location

Admin resource components should be located in:

resources/views/components/admin/resources/

---

# 9. Testing Requirements

## 9.1 Feature Tests

For each resource admin module, test:

- Index page renders for authorized users.
- Index page is blocked for unauthorized users.
- Create form renders for authorized users.
- Store action creates a resource with valid data.
- Store action rejects invalid data.
- Edit form renders for authorized users.
- Update action updates a resource with valid data.
- Update action rejects invalid data.
- Destroy action deletes a resource.
- Archive action archives a resource.

## 9.2 Policy Tests

- Test that users without permission cannot access CRUD actions.
- Test that users with permission can access CRUD actions.

## 9.3 Validation Tests

- Test that required fields are validated.
- Test that unique slugs are validated.
- Test that JSON schemas are validated.

---

# Document Completion Check

## Requirements Covered

- CRUD route patterns defined.
- Index page pattern defined.
- Create page pattern defined.
- Edit page pattern defined.
- Delete and archive patterns defined.
- Form validation and error handling patterns defined.
- Component reuse strategy defined.
- Testing requirements defined.

## Decisions Established

- Resource admin routes follow standard RESTful conventions.
- Index pages include search, filtering, and pagination.
- Create/Edit forms use consistent sections.
- Delete actions require confirmation.
- Archive actions transition status to archived.
- Reusable Blade components are used for admin resource management.
- Feature tests are mandatory for all CRUD actions.

## Dependencies

- Approved Phase 02 Admin Portal Foundation.
- Approved Phase 02 RBAC Implementation.
- Approved Phase 03 Resource Foundation.
- Approved Phase 03 Taxonomy Implementation.

## Files Affected

This document creates:

03_Resource_Architecture/06_Resource_Admin_CRUD_Patterns.md

It dictates the implementation of all future resource admin controllers, views, and components.

## Open Questions

1. Should bulk actions be implemented in the MVP or deferred?
2. Should soft deletes be used for all resource types?

## TBD Decisions

- TBD: Final decision on bulk actions for MVP.
- TBD: Final soft delete strategy per resource type.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE03-DOC06

## Context

LABGOTO requires consistent, reusable CRUD patterns for all resource admin modules. This document defines the standard patterns for listing, creation, editing, deletion, and archiving.

This prompt implements the documentation outcome of the Resource Admin CRUD Patterns document.

## Objective

Create the official LABGOTO Resource Admin CRUD Patterns document inside the approved Phase 03 documentation structure.

After this prompt is completed, the repository will have a binding specification for resource admin management.

## Existing Architecture

Relevant approved decisions:

- Resource admin routes follow RESTful conventions.
- Index pages include search, filtering, and pagination.
- Delete actions require confirmation.
- Reusable Blade components are used.

## Files to Inspect

Inspect, if available:

- docs/
- 03_Resource_Architecture/01_Resource_Foundation.md
- 03_Resource_Architecture/02_Taxonomy_Implementation.md
- resources/views/components/admin/ directory structure.

## Files to Create

Create:

03_Resource_Architecture/06_Resource_Admin_CRUD_Patterns.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI requirements for admin CRUD.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that RBAC enforcement is clearly documented.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

No public SEO impact. This is an internal architecture document.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the CRUD route patterns.
- The file defines the Index, Create, Edit, and Delete patterns.

## Integration Requirements

This document must be used as the primary reference when building resource admin controllers and views.

## Definition of Done

This implementation is complete when:

- The Resource Admin CRUD Patterns file exists.
- The file matches the approved Phase 03 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.