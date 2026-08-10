# 05 — RBAC Implementation

## Depends On

This document depends on:

- The approved User Roles and RBAC document from Phase 01.
- The approved Laravel Application Structure document.
- The approved Environment and Configuration document.
- The approved Database Foundation document.
- The approved Authentication and Session Security document.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The implementation architecture for Role-Based Access Control.
- The model relationships for users, roles, and permissions.
- The permission naming convention.
- The middleware strategy for route protection.
- The policy and gate strategy for resource-level authorization.
- The role assignment and privilege escalation prevention rules.
- The permission caching strategy.
- The audit requirements for role and permission changes.
- The testing requirements for RBAC.

## Affects

This document affects:

- The Admin Portal Foundation document.
- The Logging, Audit, and Error Handling document.
- The Testing Foundation document.
- All future admin controllers.
- All future Livewire admin components.
- All future API endpoints requiring authorization.
- All future resource management modules.
- All future seeders for roles and permissions.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes how authorization will be enforced inside Laravel.
- Prevents unauthorized access to admin routes and actions.
- Prevents privilege escalation during role assignment.
- Ensures permissions are reusable across future laboratory resource modules.
- Ensures authorization remains server-side and testable.

---

# 1. Purpose

This document defines how Role-Based Access Control will be implemented in LabGato.

RBAC is the security boundary for the Admin Portal. It ensures that administrators can only access the modules, actions, and records permitted by their assigned roles.

RBAC must be enforced on the server. Hiding buttons or links in the UI is not sufficient.

---

# 2. RBAC Implementation Principles

## 2.1 Server-Side Enforcement

All authorization must be enforced server-side.

Frontend restrictions may improve usability, but they must never be treated as security controls.

Every protected action must be checked in:

- Middleware
- Controllers
- Livewire components
- Form requests where appropriate
- Policies
- Services where business rules require additional checks

## 2.2 Principle of Least Privilege

Users must receive only the permissions required for their role.

Permissions must be granted explicitly.

Default access must be denied.

## 2.3 Explicit Permissions

Authorization decisions must be based on explicit permission slugs.

Examples:

- calculators.view
- calculators.create
- calculators.edit
- calculators.publish
- calculators.delete
- users.manage
- audit-logs.view

Wildcard permissions may exist for Super Administrator, but they must not be used as a shortcut for normal roles.

## 2.4 No Privilege Escalation

A user must not be able to grant permissions they do not themselves possess.

A user must not be able to modify a user with a higher privilege level unless explicitly authorized.

A user must not be able to assign the Super Administrator role unless they are a Super Administrator.

## 2.5 Auditability

All role and permission changes must be logged in the audit log.

Audit logs must record:

- The administrator who made the change.
- The affected user or role.
- The old and new role assignments where practical.
- The timestamp.
- The IP address where available.

---

# 3. RBAC Model Architecture

The RBAC implementation must use the foundation tables defined in the Database Foundation document.

## 3.1 User Model

The User model must support:

- Authentication
- Active status checking
- Role relationships
- Permission checking
- Session invalidation when deactivated

Key relationships:

- User belongs to many Roles.

Key methods:

- hasRole(string $roleSlug): bool
- hasPermission(string $permissionSlug): bool
- hasAnyPermission(array $permissionSlugs): bool
- isActive(): bool

Rules:

- The User model must not contain complex authorization workflows.
- The User model must use cached permission resolution where practical.
- The User model must protect role assignment fields from mass assignment.

## 3.2 Role Model

The Role model must support:

- Permission relationships
- User relationships
- System role protection

Key relationships:

- Role belongs to many Permissions.
- Role belongs to many Users.

Key methods:

- hasPermission(string $permissionSlug): bool

Rules:

- System roles must not be deleted.
- Role slugs must be unique.
- Role names must be unique.

## 3.3 Permission Model

The Permission model must support:

- Grouping by module
- Unique permission slugs
- Role relationships

Key relationships:

- Permission belongs to many Roles.

Rules:

- Permission slugs must be stable and machine-readable.
- Permissions should not be renamed casually.
- Permission deletion must be prevented if assigned to active roles unless explicitly handled.

---

# 4. Permission Naming Convention

Permissions must use a consistent naming convention.

## 4.1 Format

module.action

Examples:

- tools.view
- tools.create
- tools.edit
- tools.publish
- tools.delete
- calculators.view
- calculators.create
- calculators.edit
- calculators.publish
- calculators.delete
- reference-ranges.view
- reference-ranges.create
- reference-ranges.edit
- reference-ranges.publish
- reference-ranges.delete
- sops.view
- sops.create
- sops.edit
- sops.publish
- sops.delete
- interpretation.view
- interpretation.create
- interpretation.edit
- interpretation.publish
- interpretation.delete
- knowledge.view
- knowledge.create
- knowledge.edit
- knowledge.publish
- knowledge.delete
- taxonomy.view
- taxonomy.manage
- users.view
- users.create
- users.edit
- users.delete
- users.assign-roles
- roles.view
- roles.manage
- audit-logs.view
- analytics.view
- settings.view
- settings.manage

## 4.2 Reserved Super Administrator Permission

The Super Administrator role may be granted a reserved internal permission:

- admin.full-access

This permission must not be assignable through the normal admin UI.

It must be protected in seeders and code.

## 4.3 Module Grouping

Permissions must be grouped by module.

Examples:

- Tools
- Calculators
- Reference Ranges
- SOPs
- Interpretation
- Knowledge
- Taxonomy
- Users
- Roles
- Audit Logs
- Analytics
- Settings

Grouping is used for admin UI organization and permission auditing.

---

# 5. Middleware Strategy

RBAC must be enforced through middleware for route-level protection.

## 5.1 Admin Authentication Middleware

All admin routes must use the authentication middleware.

Purpose:

- Ensure the user is logged in.
- Ensure the user session is valid.
- Redirect unauthenticated users to the admin login page.

## 5.2 Active User Middleware

All admin routes must verify that the authenticated user is active.

If the user is inactive:

- The session must be invalidated.
- The user must be logged out.
- The user must be redirected to the login page with a safe message.
- The event should be logged.

## 5.3 Permission Middleware

Protected admin routes should use permission middleware where appropriate.

Example route protection:

- admin.calculators.index requires calculators.view
- admin.calculators.create requires calculators.create
- admin.calculators.store requires calculators.create
- admin.calculators.edit requires calculators.edit
- admin.calculators.update requires calculators.edit
- admin.calculators.destroy requires calculators.delete

Rules:

- Middleware must use permission slugs.
- Middleware must return a 403 response for unauthorized access.
- Middleware must not expose sensitive reasons for denial.

## 5.4 Password Confirmation Middleware

Sensitive routes must use password confirmation middleware.

Examples:

- Role assignment
- User deactivation
- System settings management
- Audit log access where approved
- Administrator creation

---

# 6. Policy and Gate Strategy

Middleware protects routes. Policies protect resources and actions.

## 6.1 Gates

Gates must be defined for general permission checks.

Examples:

- Gate::define('calculators.view', function ($user) { ... })
- Gate::define('users.manage', function ($user) { ... })
- Gate::before(function ($user, $ability) { ... })

The Super Administrator may bypass specific permission checks using a Gate::before callback, but this bypass must be explicit and logged where appropriate.

## 6.2 Policies

Resource policies must be created when resource modules are implemented.

Examples:

- CalculatorPolicy
- ToolPolicy
- ReferenceRangePolicy
- SopPolicy
- KnowledgeResourcePolicy
- CategoryPolicy
- TagPolicy
- UserPolicy
- RolePolicy
- SettingPolicy

Policy methods may include:

- viewAny
- view
- create
- update
- publish
- delete

Rules:

- Policies must return boolean decisions.
- Policies must not modify data.
- Policies must not contain complex workflows unless required by a business rule.
- Policies must be testable.

## 6.3 Controller Authorization

Controllers must authorize actions before executing them.

Examples:

- Authorize index access before listing resources.
- Authorize create access before showing creation form.
- Authorize update access before saving changes.
- Authorize delete access before deletion.

Controllers must not assume middleware alone is sufficient.

## 6.4 Livewire Authorization

Livewire components must authorize actions during:

- Mounting
- Form submission
- State changes
- Delete or publish actions
- Modal actions

Rules:

- Livewire components must not render unauthorized admin controls.
- Livewire components must reject unauthorized server-side actions.
- Livewire components must display safe permission-denied states.

---

# 7. Role Assignment Rules

## 7.1 Authorized Assigners

Only users with the users.assign-roles permission may assign roles.

In the initial model, this will usually be the Super Administrator.

## 7.2 Self-Assignment Prohibition

Users must not assign roles to themselves.

If a user attempts to modify their own roles, the request must be rejected.

## 7.3 Higher-Privilege Protection

Users must not modify Super Administrator accounts unless they are Super Administrators.

Users must not assign roles that grant permissions they do not themselves possess.

## 7.4 Role Assignment Validation

When assigning roles, the system must validate:

- The target user exists.
- The target user is active unless reactivation is explicitly authorized.
- The selected roles exist.
- The selected roles are assignable.
- The current user has permission to assign each selected role.
- The current user is not escalating their own privileges.

## 7.5 Role Deletion Protection

System roles must not be deleted.

Roles assigned to users must not be deleted without a migration or reassignment plan.

Permission removal from roles must be logged.

---

# 8. Seeder Strategy

RBAC seeders must create the foundation roles and permissions.

## 8.1 Required Permissions

The permission seeder must create permissions for all approved foundation modules.

At minimum:

- users.view
- users.create
- users.edit
- users.delete
- users.assign-roles
- roles.view
- roles.manage
- taxonomy.view
- taxonomy.manage
- audit-logs.view
- analytics.view
- settings.view
- settings.manage

Future resource permissions must be added when the corresponding vertical slice is documented.

## 8.2 Required Roles

The role seeder must create:

- Super Administrator
- Content Manager
- Viewer / Analyst

## 8.3 Role-Permission Assignment

The seeder must assign permissions according to the approved Phase 01 permission matrix.

Rules:

- Content Manager must not receive delete permissions.
- Viewer / Analyst must receive read-only permissions.
- Super Administrator must receive full access.

## 8.4 Idempotency

Seeders must be safe to run multiple times.

Seeders must use firstOrCreate or updateOrCreate where appropriate.

Seeders must not duplicate permissions or roles.

## 8.5 Default Administrator Account

A default Super Administrator account may be created for local development only.

Production administrator accounts must be created through a secure, documented process.

Default credentials must never be committed as real secrets.

---

# 9. Permission Caching Strategy

Permission checks may occur on every admin request. To maintain performance on shared hosting, permissions should be cached.

## 9.1 Cache Source

Cache driver:

Database

This avoids Redis and remains compatible with Hostinger.

## 9.2 Cache Key Strategy

A recommended cache key structure:

user:{user_id}:permissions
user:{user_id}:roles
role:{role_id}:permissions

## 9.3 Cache Invalidation

The permission cache must be invalidated when:

- A role is assigned to a user.
- A role is removed from a user.
- A permission is added to a role.
- A permission is removed from a role.
- A role is deleted.
- A user is deactivated.
- A user is reactivated.

## 9.4 Cache Safety

Cached permissions must not expose secrets.

Cached permissions must be cleared after RBAC changes.

The application must not rely on stale permissions after an administrator modifies roles.

---

# 10. Authorization Error Handling

## 10.1 Unauthorized Response

When authorization fails:

- Return a 403 response.
- Show a safe branded error page.
- Do not reveal which permission was missing unless in local development.
- Do not expose internal role logic publicly.

## 10.2 Audit Logging

Authorization failures for sensitive actions should be logged.

Examples:

- Attempted access to user management.
- Attempted role assignment.
- Attempted settings modification.
- Attempted deletion of protected resources.

## 10.3 User Experience

If an administrator lacks permission:

- The UI should not display the action where practical.
- If the user reaches the action directly, show a clear permission-denied message.
- Do not create dead buttons.
- Do not leave forms partially usable without server-side permission.

---

# 11. Admin UI RBAC Rules

## 11.1 Navigation Visibility

Admin navigation should hide modules the user cannot access.

However, direct URL access must still be blocked server-side.

## 11.2 Action Buttons

Create, edit, publish, and delete buttons must only appear when authorized.

If unauthorized:

- Hide the button, or
- Replace it with a disabled state and explanatory tooltip where appropriate.

Do not render fake controls.

## 11.3 Forms

Forms must not render editable fields for unauthorized actions.

If a user submits an unauthorized form, the server must reject the request.

## 11.4 Tables

Table rows may be visible if the user has view permission.

Action columns must respect individual permissions.

---

# 12. Shared Hosting Constraints

## 12.1 No Redis

RBAC must not rely on Redis.

Permission caching must use the database cache driver or another approved Hostinger-compatible cache store.

## 12.2 Query Efficiency

Role and permission checks must avoid excessive database queries.

Mitigations:

- Cache user permissions.
- Eager load roles where appropriate.
- Avoid checking permissions inside large loops without caching.

## 12.3 Session Compatibility

RBAC must work with database sessions.

If a user is deactivated, the system must be able to invalidate access immediately.

---

# 13. Testing Requirements

RBAC must be thoroughly tested.

## 13.1 Unit Tests

Test:

- user->hasPermission()
- user->hasRole()
- user->hasAnyPermission()
- role->hasPermission()
- Permission cache invalidation behavior where practical

## 13.2 Feature Tests

Test:

- Authorized user can access permitted admin pages.
- Unauthorized user receives 403.
- Guest is redirected to login.
- Deactivated user is logged out and denied access.
- Content Manager cannot access user management.
- Viewer / Analyst cannot create or edit resources.
- Super Administrator can access all approved modules.

## 13.3 Privilege Escalation Tests

Test:

- User cannot assign roles to themselves.
- User cannot assign a role containing permissions they do not possess.
- Non-Super Administrator cannot modify a Super Administrator.
- Role assignment request fails when permission is missing.
- Direct API or form manipulation is rejected.

## 13.4 Route Tests

Test direct access to:

- Admin dashboard
- User management
- Role management
- Settings
- Audit logs
- Future resource admin routes

Direct access without permission must return 403.

---

# 14. Prohibited RBAC Patterns

The following patterns are prohibited.

## 14.1 Frontend-Only Authorization

Hiding a button without enforcing authorization server-side is prohibited.

## 14.2 Hardcoded User IDs

Authorization must not depend on hardcoded user IDs.

Use roles, permissions, and policies.

## 14.3 Implicit Admin Access

Access must not be granted based on email domain, name, or unverified profile fields.

## 14.4 Silent Permission Changes

Permission or role changes must not occur without audit logging.

## 14.5 Unrestricted Role Creation

Role creation and permission assignment must be restricted to authorized administrators.

---

# Document Completion Check

## Requirements Covered

- RBAC implementation principles defined.
- Model architecture defined.
- Permission naming convention defined.
- Middleware strategy defined.
- Policy and gate strategy defined.
- Role assignment rules defined.
- Seeder strategy defined.
- Permission caching strategy defined.
- Authorization error handling defined.
- Admin UI RBAC rules defined.
- Shared hosting constraints addressed.
- Testing requirements defined.
- Prohibited patterns defined.

## Decisions Established

- RBAC will be enforced server-side.
- Permissions will use module.action naming.
- Middleware will protect routes.
- Policies and gates will protect actions and resources.
- Super Administrator may bypass permission checks through an explicit Gate::before rule.
- Users cannot assign roles to themselves.
- Users cannot assign permissions they do not possess.
- Role and permission changes will be audited.
- Permission caching will use the database cache driver.
- Public user RBAC remains out of scope.

## Dependencies

- Approved User Roles and RBAC.
- Approved Database Foundation.
- Approved Authentication and Session Security.
- Approved Laravel Application Structure.
- Approved Environment and Configuration.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/05_RBAC_Implementation.md

It affects future auth middleware, policies, gates, models, seeders, admin controllers, Livewire components, and tests.

## Open Questions

1. Should roles be cached separately from permissions?
2. Should role changes require password confirmation every time?
3. Should Viewer / Analyst have access to audit logs in MVP?
4. Should the Super Administrator bypass be logged for every action?

## TBD Decisions

- TBD: Final caching key structure.
- TBD: Final password confirmation requirements for role changes.
- TBD: Final audit log access policy for Viewer / Analyst.
- TBD: Final Super Administrator bypass logging policy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE02-DOC05

## Context

LabGato requires a secure server-side RBAC implementation to protect the Admin Portal. This document defines how users, roles, permissions, middleware, policies, gates, seeders, caching, and auditing must work together.

This prompt implements the documentation outcome of the RBAC Implementation document.

## Objective

Create the official LabGato RBAC Implementation document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have the approved authorization implementation architecture.

## Existing Architecture

Relevant approved decisions:

- LabGato uses Laravel.
- Admin authentication is required.
- Sessions use the database driver.
- Roles and permissions are stored in the database.
- Public user accounts are out of scope.
- RBAC must be enforced server-side.
- Role and permission changes must be audited.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- 01_Laravel_Application_Structure.md
- 02_Environment_and_Configuration.md
- 03_Database_Foundation.md
- 04_Authentication_and_Session_Security.md
- Any existing auth or RBAC code

If no files exist, record that the technical documentation structure is being initialized.

## Files to Create

Create:

02_Technical_Foundation_Architecture/05_RBAC_Implementation.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

If a documentation index already exists, add a reference to:

02_Technical_Foundation_Architecture/05_RBAC_Implementation.md

Do not modify application code during this documentation step.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets.
- Do not include real administrator credentials.
- Do not expose internal permission logic beyond what is required for implementation.

## Accessibility Requirements

The Markdown document should use:

- Clear heading hierarchy.
- Readable list structure.
- Consistent permission naming examples.

## SEO Requirements

No public SEO impact.

This is an internal/documentation file.

## Analytics Requirements

No analytics events are required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the required dependency section.
- The file contains the required completion check.
- The file contains the implementation prompt.
- The file defines server-side RBAC enforcement.
- The file defines privilege escalation prevention.
- The file defines permission caching and invalidation.

If the repository supports Markdown linting, run it.

## Integration Requirements

This document must be referenced by future admin controllers, Livewire components, policies, gates, seeders, and tests.

No future authorization decision may conflict with this document without an approved change request.

## Definition of Done

This implementation is complete when:

- The RBAC Implementation file exists.
- The file matches the approved Phase 02 file tree.
- The file defines the RBAC implementation architecture.
- The file defines privilege escalation prevention.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
- No application code was changed.
- No secrets were committed.