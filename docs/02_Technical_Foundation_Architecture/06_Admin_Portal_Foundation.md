# 06 — Admin Portal Foundation

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically User Roles and RBAC, Target Users and Journeys).
- The approved Laravel Application Structure document.
- The approved Environment and Configuration document.
- The approved Authentication and Session Security document.
- The approved RBAC Implementation document.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The administrative layout architecture.
- The administrative navigation structure.
- The dashboard shell requirements.
- The foundational CRUD UI patterns.
- The mandatory UI states (Loading, Empty, Error, Success, Validation).
- The administrative component library structure.
- The accessibility and responsiveness requirements for the admin area.

## Affects

This document affects:

- The Logging, Audit, and Error Handling document.
- The Testing Foundation document.
- All future vertical-slice administrative interfaces.
- All future Livewire admin components.
- All future admin Blade views.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the visual and functional foundation of the Admin Portal.
- Ensures a consistent, professional, and accessible experience for administrators.
- Prevents disconnected or dead administrative interfaces.
- Provides reusable UI patterns for all future resource management modules.

---

# 1. Purpose

This document establishes the Admin Portal Foundation for LABGOTO.

The Admin Portal is a serious internal application. It must be efficient, secure, accessible, and reliable.

This foundation ensures that when future laboratory resources (Calculators, Tools, Reference Ranges, SOPs) are implemented, they plug into a consistent and well-structured administrative interface.

---

# 2. Admin Layout Architecture

The Admin Portal must use a dedicated layout that is distinct from the public website.

## 2.1 Layout File

The primary admin layout must be located at:

resources/views/layouts/admin.blade.php

## 2.2 Layout Structure

The layout must include:

- A fixed or responsive sidebar for primary navigation.
- A top header containing the administrator's name, profile menu, and logout action.
- A main content area with appropriate padding and max-width constraints.
- A footer with versioning or environment indicators (e.g., "LABGOTO Admin - Production").

## 2.3 Responsive Behavior

The Admin Portal must be fully responsive.

- Desktop: Sidebar visible by default.
- Tablet: Sidebar collapsible or overlay.
- Mobile: Off-canvas sidebar with a hamburger toggle.

Administrators may need to check the dashboard or review content on mobile devices.

## 2.4 Color and Typography

The admin interface should use a clean, professional design system.

- Avoid excessive gradients, glassmorphism, or decorative clutter.
- Use high-contrast text for readability.
- Use clear visual hierarchy for headings, labels, and data tables.
- Use Tailwind CSS for styling.

---

# 3. Admin Navigation

The admin navigation must be generated dynamically based on the authenticated user's permissions.

## 3.1 Navigation Items

The following navigation groups must be supported:

- Dashboard
- Content (Tools, Calculators, Reference Ranges, SOPs, Interpretation, Knowledge)
- Taxonomy (Categories, Tags)
- Users & Roles
- System (Settings, Audit Logs, Analytics)

## 3.2 Permission-Based Visibility

Navigation items must only be visible if the authenticated user has the corresponding view permission.

Examples:

- Users & Roles requires users.view or roles.view.
- System Settings requires settings.view.
- Audit Logs requires audit-logs.view.

If a user has no view permissions for a group, the group must be hidden.

## 3.3 Active State

The current page must be clearly highlighted in the navigation.

## 3.4 No Dead Links

Navigation items must not link to unimplemented pages unless those pages display a clear "Coming Soon" or "Not Found" state.

---

# 4. Dashboard Shell

The dashboard is the landing page for authenticated administrators.

## 4.1 Dashboard Route

Route: /admin/dashboard
Permission: dashboard.view (or implicit for all authenticated admins)

## 4.2 Dashboard Content

The dashboard must display real, honest data.

Initial dashboard widgets may include:

- Recent Content Updates
- Pending Reviews (if workflow is implemented)
- System Status (e.g., Queue status, Cache status)
- Quick Links to common actions

## 4.3 Empty States

If no data exists (e.g., no content has been created yet), the dashboard must display a clear empty state.

Fake statistics are prohibited.

## 4.4 Performance

Dashboard queries must be optimized.

- Avoid N+1 queries.
- Use pagination or limits for recent activity lists.
- Cache expensive aggregates where appropriate.

---

# 5. CRUD Foundation Patterns

All future administrative resource modules must follow consistent CRUD patterns.

## 5.1 Index Page

Purpose: List resources with filtering and pagination.

Required elements:

- Page title
- Search or filter input
- Data table or resource grid
- Pagination controls
- "Create" button (if user has create permission)
- Empty state if no resources exist

## 5.2 Create Page

Purpose: Display a form to create a new resource.

Required elements:

- Page title
- Form fields with clear labels
- Validation error display
- "Cancel" button (returns to index)
- "Save" button

## 5.3 Edit Page

Purpose: Display a form to update an existing resource.

Required elements:

- Page title
- Form fields populated with existing data
- Validation error display
- "Cancel" button (returns to index)
- "Save Changes" button
- "Delete" button (if user has delete permission and deletion is allowed)

## 5.4 Form Validation

All forms must use server-side validation via Form Requests.

Validation errors must be displayed near the relevant field.

## 5.5 Confirmation Dialogs

Destructive actions (e.g., Delete) must require confirmation.

Confirmation may be implemented via:

- Native browser confirm() (simple, accessible)
- Accessible modal dialog (preferred for better UX)

---

# 6. Mandatory UI States

Every data-driven administrative interface must implement the following states.

## 6.1 Loading State

When data is being fetched or a form is being submitted:

- Display a loading spinner or skeleton screen.
- Disable submit buttons to prevent duplicate submissions.
- Use Livewire's loading states where applicable.

## 6.2 Empty State

When a list or table has no data:

- Display a clear message (e.g., "No calculators found.").
- Provide a call-to-action if appropriate (e.g., "Create your first calculator").

## 6.3 Error State

When an operation fails:

- Display a clear, safe error message.
- Do not expose stack traces or sensitive data.
- Log the error for administrators.

## 6.4 Success State

When an operation succeeds:

- Display a flash message or toast notification.
- Confirm the action (e.g., "Calculator updated successfully.").

## 6.5 Validation State

When input is invalid:

- Display inline validation errors.
- Highlight the invalid fields.
- Do not clear the entire form unless necessary.

---

# 7. Component Library (Admin)

Reusable Blade components must be created for common admin UI elements.

## 7.1 Layout Components

- x-admin.sidebar
- x-admin.header
- x-admin.footer

## 7.2 UI Components

- x-ui.button
- x-ui.input
- x-ui.select
- x-ui.textarea
- x-ui.checkbox
- x-ui.toggle
- x-ui.alert
- x-ui.badge
- x-ui.card
- x-ui.table
- x-ui.pagination

## 7.3 Form Components

- x-form.label
- x-form.error
- x-form.section

## 7.4 Component Rules

- Components must be accessible.
- Components must support ARIA attributes where necessary.
- Components must not contain business logic.
- Components must be styled consistently using Tailwind CSS.

---

# 8. Accessibility and Responsiveness

## 8.1 Keyboard Navigation

All admin forms, tables, and navigation must be usable via keyboard.

- Focus states must be visible.
- Tab order must be logical.
- Modals must trap focus and return focus to the trigger when closed.

## 8.2 Screen Reader Support

- Form inputs must have associated labels.
- Buttons must have accessible text.
- Status messages (success, error) should use aria-live regions.

## 8.3 Touch Targets

Interactive elements must be large enough for touch interaction.

## 8.4 Contrast

Text and background contrast must meet WCAG 2.2 AA standards.

---

# 9. Shared Hosting Constraints

## 9.1 Livewire Polling

If Livewire polling is used for real-time updates:

- Polling intervals must be conservative (e.g., 10s or longer).
- Avoid excessive polling that could increase server load.

## 9.2 Asset Loading

Admin assets must be compiled and optimized.

- Do not load unnecessary JavaScript on admin pages.
- Use Vite's code splitting where appropriate.

## 9.3 Database Load

Admin tables must use pagination.

- Default pagination: 15, 25, or 50 items per page.
- Avoid loading thousands of records into memory.

---

# Document Completion Check

## Requirements Covered

- Admin layout architecture defined.
- Admin navigation structure defined.
- Dashboard shell requirements defined.
- CRUD foundation patterns defined.
- Mandatory UI states defined.
- Component library structure defined.
- Accessibility and responsiveness requirements defined.
- Shared hosting constraints addressed.

## Decisions Established

- Admin Portal uses a dedicated layout.
- Navigation is permission-based.
- Fake statistics are prohibited.
- CRUD pages follow consistent patterns.
- Loading, Empty, Error, Success, and Validation states are mandatory.
- Reusable Blade components will be used.
- Admin UI must be accessible and responsive.

## Dependencies

- Approved Phase 01 User Roles and Journeys.
- Approved Laravel Application Structure.
- Approved RBAC Implementation.
- Approved Technology Stack.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/06_Admin_Portal_Foundation.md

It directly dictates the structure of all future admin Blade views and Livewire components.

## Open Questions

1. Should the admin dashboard use charts or graphs in the MVP?
2. Should confirmation dialogs use native browser confirm() or custom modals?
3. What is the default pagination size for admin tables?

## TBD Decisions

- TBD: Final decision on dashboard charting library (if any).
- TBD: Final decision on confirmation dialog implementation.
- TBD: Final default pagination size.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE02-DOC06

## Context

LABGOTO requires a professional, accessible, and secure Admin Portal. This document defines the layout, navigation, dashboard, CRUD patterns, and UI component rules that will serve as the foundation for all future administrative modules.

This prompt implements the documentation outcome of the Admin Portal Foundation document.

## Objective

Create the official LABGOTO Admin Portal Foundation document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have a binding UI/UX specification for the Admin Portal.

## Existing Architecture

Relevant approved decisions:

- LABGOTO uses Laravel, Blade, Tailwind, and Livewire.
- Admin Portal is restricted to authenticated administrators.
- Navigation must be permission-based.
- Fake statistics are prohibited.
- Accessibility is mandatory.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 through 05.
- Any existing admin views or components.

## Files to Create

Create:

02_Technical_Foundation_Architecture/06_Admin_Portal_Foundation.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI requirements.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that permission-based navigation rules are clearly documented.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

No public SEO impact. Admin pages must be noindex.

## Analytics Requirements

No analytics events required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the admin layout and CRUD patterns.
- The file defines the mandatory UI states.

## Integration Requirements

This document must be used as the primary reference when creating the admin layout, navigation, and base components.

## Definition of Done

This implementation is complete when:

- The Admin Portal Foundation file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.