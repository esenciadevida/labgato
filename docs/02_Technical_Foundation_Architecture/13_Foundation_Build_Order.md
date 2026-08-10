# 13 — Foundation Build Order

## Depends On

This document depends on:

- All preceding Phase 02 documents (01 through 12).
- The approved Phase 01 documentation.
- The approved vertical-slice development strategy.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The exact step-by-step implementation order for Slice 1 (Application Foundation).
- The dependencies between foundational build steps.
- The Definition of Done for the application foundation.
- The criteria for transitioning from foundation building to resource vertical slices.
- The Phase 02 implementation map and build order.

## Affects

This document affects:

- The immediate development workflow for the engineering team.
- The sequence of pull requests and commits.
- The initial deployment to the Hostinger environment.
- The readiness assessment for Phase 03 (Resource Architecture).

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Prevents out-of-order development that causes merge conflicts or architectural debt.
- Ensures that security, database, and layout foundations are stable before feature development begins.
- Provides a clear checklist for the first deployment.

---

# 1. Purpose

This document establishes the Foundation Build Order for LabGato.

The Application Foundation (Slice 1) is the bedrock of the platform. Building features like Calculators or Reference Ranges before the authentication, RBAC, and layout foundations are stable will result in rework and technical debt.

This build order ensures that the framework, database, security, and UI shells are implemented in a strict, dependency-driven sequence.

---

# 2. Build Order Philosophy

The foundation must be built horizontally first, then vertically.

1.  **Environment & Framework**: Get Laravel running with the correct configuration.
2.  **Data & Security**: Establish the database schema, authentication, and RBAC.
3.  **UI Shells**: Build the Admin and Public layouts.
4.  **Support Systems**: Implement logging, error handling, and base APIs.
5.  **Verification**: Test, deploy, and verify.

Do not build resource-specific CRUD (e.g., Calculators) until Steps 1 through 5 are complete.

---

# 3. Step-by-Step Build Order for Slice 1

## Build 1: Project Initialization & Environment

**Objective**: Initialize the Laravel project and configure the environment for local development and Hostinger compatibility.

**Tasks**:
1. Create a new Laravel project using the latest stable version compatible with PHP 8.4.
2. Configure `.env` and `.env.example` according to the Environment and Configuration document.
3. Set up `config/labgato.php` for custom application settings.
4. Configure `config/database.php`, `config/session.php`, `config/cache.php`, and `config/queue.php` to use database drivers.
5. Initialize Git repository and commit the initial Laravel skeleton.
6. Configure Vite, Tailwind CSS, and Alpine.js. Verify local asset compilation.

**Dependencies**: None.

## Build 2: Database Foundation & Migrations

**Objective**: Create the core database schema for access, system, taxonomy, and framework tables.

**Tasks**:
1. Create migrations for `users`, `roles`, `permissions`, `role_user`, and `permission_role`.
2. Create migrations for `settings` and `audit_logs`.
3. Create migrations for `categories` and `tags`.
4. Create migrations for framework tables (`sessions`, `cache`, `jobs`, `failed_jobs`, `password_reset_tokens`).
5. Create Eloquent models for all foundation entities.
6. Create database seeders for default roles and permissions.
7. Run migrations and seeders locally to verify schema integrity.

**Dependencies**: Build 1.

## Build 3: Authentication & Session Security

**Objective**: Implement secure administrator login, logout, and password reset functionality.

**Tasks**:
1. Set up Laravel authentication scaffolding (or custom controllers) for admin login.
2. Implement login throttling middleware.
3. Implement session regeneration on login.
4. Create authentication views (Login, Forgot Password, Reset Password) using the auth layout.
5. Implement the "Active User" check middleware to block deactivated accounts.
6. Write feature tests for login, logout, and throttling.

**Dependencies**: Build 2.

## Build 4: RBAC Implementation

**Objective**: Implement the Role-Based Access Control system.

**Tasks**:
1. Implement `hasRole`, `hasPermission`, and `hasAnyPermission` methods on the User model.
2. Create permission middleware for route protection.
3. Define Gates and Policies for foundation modules (Users, Roles, Settings).
4. Implement the `AuditLogService` and integrate it with authentication events.
5. Implement permission caching logic with invalidation on role changes.
6. Write unit and feature tests for RBAC, including privilege escalation prevention.

**Dependencies**: Build 3.

## Build 5: Admin Portal Foundation

**Objective**: Build the administrative layout, navigation, and dashboard shell.

**Tasks**:
1. Create the `admin.blade.php` layout with sidebar, header, and footer.
2. Create reusable Blade components (`x-admin.sidebar`, `x-ui.button`, `x-ui.alert`, etc.).
3. Implement permission-based navigation rendering.
4. Create the Admin Dashboard controller and view with honest empty states.
5. Implement the base CRUD layout patterns (Index, Create, Edit shells) for future resources.
6. Write feature tests for admin layout rendering and navigation visibility.

**Dependencies**: Build 4.

## Build 6: Public Site Foundation

**Objective**: Build the public website layout, navigation, and static pages.

**Tasks**:
1. Create the `public.blade.php` layout with header, footer, and skip links.
2. Create the Home Page controller and view with hero and category grid shells.
3. Create Legal Pages (Privacy, Terms, Disclaimer, Accessibility).
4. Create custom Error Pages (403, 404, 500, 503).
5. Implement the `<x-public.meta>` component for SEO and Open Graph tags.
6. Write feature tests for public page rendering and HTTP status codes.

**Dependencies**: Build 1.

## Build 7: Base API, Logging & Error Handling

**Objective**: Implement the API foundation, global exception handler, and audit logging service.

**Tasks**:
1. Configure `routes/api.php` with versioning and rate limiting.
2. Create a standardized JSON response trait or helper.
3. Customize the Laravel Exception Handler for production safety and API JSON responses.
4. Implement the `AuditLogService` with sensitive data masking.
5. Configure daily log rotation and retention limits.
6. Write tests for API responses and exception handling.

**Dependencies**: Build 2, Build 4.

## Build 8: Testing, SEO & Accessibility Verification

**Objective**: Verify the foundation against testing, SEO, and accessibility standards.

**Tasks**:
1. Run the full test suite and fix any failures.
2. Run Lighthouse or manual accessibility checks on the public layout and admin login.
3. Verify keyboard navigation and focus states.
4. Generate the initial `sitemap.xml` and `robots.txt`.
5. Verify that no fake data or dead links exist in the foundation layouts.

**Dependencies**: Build 5, Build 6, Build 7.

## Build 9: Deployment Preparation & First Release

**Objective**: Deploy the foundation to the Hostinger staging/production environment.

**Tasks**:
1. Build frontend assets locally (`npm run build`).
2. Install production Composer dependencies.
3. Package the application and upload to Hostinger.
4. Configure the production `.env` file.
5. Run database migrations on the server.
6. Create the storage symlink.
7. Configure Cron jobs for the scheduler and queue.
8. Execute the Release Verification Checklist.

**Dependencies**: Build 8.

---

# 4. Definition of Done for Foundation

The Application Foundation is considered **COMPLETE** only when:

- [ ] Laravel boots successfully locally and on Hostinger.
- [ ] Database migrations run without errors.
- [ ] Administrator can log in, log out, and reset password securely.
- [ ] RBAC prevents unauthorized access to admin routes.
- [ ] Admin layout renders correctly with permission-based navigation.
- [ ] Public layout renders correctly with SEO meta tags.
- [ ] Error pages display safely in production mode.
- [ ] Audit logs record login and administrative actions.
- [ ] All foundation tests pass.
- [ ] Deployment checklist is completed and verified.

---

# 5. Transition to Phase 03

Once the Foundation is marked **COMPLETE**, the project may transition to **Phase 03: Resource Architecture**.

The first resource vertical slice (e.g., Calculators or Tools) must not begin until the Foundation Definition of Done is satisfied.

---

# Document Completion Check

## Requirements Covered

- Build order philosophy defined.
- Step-by-step build order for Slice 1 defined.
- Definition of Done for Foundation defined.
- Transition criteria to Phase 03 defined.

## Decisions Established

- Foundation must be built horizontally before resource vertical slices.
- Database and RBAC must be implemented before Admin CRUD.
- Public and Admin layouts are built in parallel after security.
- Deployment is the final step of the foundation slice.

## Dependencies

- All Phase 02 documents.
- Approved Phase 01 roadmap.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/13_Foundation_Build_Order.md

It serves as the execution plan for the first development sprint.

## Open Questions

1. Which specific resource type will be selected for the first vertical slice in Phase 03?
2. Will the initial deployment target a staging subdomain or the production domain directly?

## TBD Decisions

- TBD: Selection of the first resource vertical slice.
- TBD: Final staging environment URL.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE02-DOC13

## Context

Phase 02 documentation is concluding. This document establishes the exact build order for the Application Foundation (Slice 1) and defines the criteria for completion.

This prompt implements the documentation outcome of the Foundation Build Order document.

## Objective

Create the official LabGato Foundation Build Order document inside the approved Phase 02 documentation structure.

After this prompt is completed, Phase 02 documentation is fully drafted and ready for final review and repository commit.

## Existing Architecture

Relevant approved decisions:

- Foundation must be built in dependency order.
- Security and RBAC precede feature CRUD.
- Deployment is the final verification step.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 through 12.

## Files to Create

Create:

02_Technical_Foundation_Architecture/13_Foundation_Build_Order.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

No public SEO impact.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the 9 build steps.
- The file defines the Definition of Done for the foundation.

## Integration Requirements

This document bridges the gap between Phase 02 (Technical Architecture) and actual code implementation. It must be referenced when starting Build 1.

## Definition of Done

This implementation is complete when:

- The Foundation Build Order file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.