# 12 — User Roles and RBAC

## Depends On

This document depends on:

- The approved LABGOTO Master Software Project directive.
- The approved Project Charter.
- The approved Product Scope and Non-Goals document.
- The approved Target Users and Journeys document.
- The approved Core Content Model document.
- The approved Information Architecture document.
- The approved Technology Stack document.

## Defines

This document defines:

- The Role-Based Access Control (RBAC) principles for LABGOTO.
- The initial administrative roles.
- The permission matrix for each role.
- The rules for role assignment and management.
- The mechanisms for preventing privilege escalation.
- The server-side enforcement requirements.
- The audit requirements for role and permission changes.

## Affects

This document affects:

- The Database Architecture (future phases).
- The Backend Authorization Architecture (future phases).
- The Admin Portal Specification (future phases).
- The Security Architecture (future phases).
- The Testing Strategy (future phases).
- The API Architecture (future phases).

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the authorization boundaries for the Admin Portal.
- Ensures that administrative actions are restricted to authorized users.
- Prevents unauthorized access to sensitive system settings and user management.
- Guides the creation of database seeders for roles and permissions.
- Guides the implementation of Laravel policies and gates.

---

# 1. Purpose

This document establishes the User Roles and RBAC architecture for LABGOTO.

Because LABGOTO includes an Admin Portal that manages scientific content, users, and system settings, it requires a robust authorization model.

The RBAC model ensures that:

- Administrators have the access they need to perform their duties.
- Users are restricted to only the access they need (Principle of Least Privilege).
- Sensitive actions (e.g., managing roles, deleting content) are protected.
- All authorization decisions are enforced server-side.

---

# 2. RBAC Principles

The following principles govern the LABGOTO RBAC model.

## 2.1 Principle of Least Privilege

Users must receive only the permissions necessary to perform their approved tasks.

Default access must be restrictive. Permissions must be explicitly granted.

## 2.2 Server-Side Enforcement

All authorization must be enforced on the server.

Frontend restrictions (e.g., hiding buttons) are for user experience only and are not security controls.

Every route, controller action, and API endpoint must verify permissions before executing.

## 2.3 Granular Permissions

Permissions must be specific and actionable.

Examples:

- `tools.view`
- `tools.create`
- `tools.edit`
- `tools.publish`
- `tools.delete`
- `users.manage`

Broad permissions (e.g., `admin.all`) should be avoided except for the Super Administrator role.

## 2.4 Role Hierarchy

Roles are collections of permissions.

Users are assigned roles. Roles are assigned permissions.

There is no implicit inheritance between roles unless explicitly defined.

## 2.5 Auditability

All role assignments, permission changes, and administrative actions must be logged in the Audit Log.

---

# 3. Initial Role Definitions

The following roles are approved for the initial LABGOTO release.

## 3.1 Super Administrator

**Description:**
Full access to the entire platform. Responsible for system configuration, user management, and security.

**Access Level:**
Unrestricted.

**Key Responsibilities:**
- Manage all users and roles.
- Configure system settings.
- Review audit logs.
- Manage all content types.
- Perform destructive actions (delete resources, users).

## 3.2 Content Manager

**Description:**
Responsible for creating, editing, and publishing laboratory resources.

**Access Level:**
Content modules only. No access to user management or system settings.

**Key Responsibilities:**
- Create and edit Tools, Calculators, Reference Ranges, SOPs, Interpretation, and Knowledge resources.
- Manage taxonomy (Categories and Tags).
- Publish and archive content.
- View content analytics.

## 3.3 Viewer / Analyst

**Description:**
Read-only access to content and analytics.

**Access Level:**
View only. No create, edit, or delete permissions.

**Key Responsibilities:**
- View published and draft content.
- View analytics and audit logs (if authorized).
- Review content quality without modifying it.

---

# 4. Permission Matrix

The following matrix defines the permissions assigned to each role.

| Permission Group | Permission | Super Admin | Content Manager | Viewer / Analyst |
|---|---|---|---|---|
| **Tools** | View | ✓ | ✓ | ✓ |
| | Create | ✓ | ✓ | ✗ |
| | Edit | ✓ | ✓ | ✗ |
| | Publish | ✓ | ✓ | ✗ |
| | Delete | ✓ | ✗ | ✗ |
| **Calculators** | View | ✓ | ✓ | ✓ |
| | Create | ✓ | ✓ | ✗ |
| | Edit | ✓ | ✓ | ✗ |
| | Publish | ✓ | ✓ | ✗ |
| | Delete | ✓ | ✗ | ✗ |
| **Reference Ranges** | View | ✓ | ✓ | ✓ |
| | Create | ✓ | ✓ | ✗ |
| | Edit | ✓ | ✓ | ✗ |
| | Publish | ✓ | ✓ | ✗ |
| | Delete | ✓ | ✗ | ✗ |
| **SOPs** | View | ✓ | ✓ | ✓ |
| | Create | ✓ | ✓ | ✗ |
| | Edit | ✓ | ✓ | ✗ |
| | Publish | ✓ | ✓ | ✗ |
| | Delete | ✓ | ✗ | ✗ |
| **Interpretation** | View | ✓ | ✓ | ✓ |
| | Create | ✓ | ✓ | ✗ |
| | Edit | ✓ | ✓ | ✗ |
| | Publish | ✓ | ✓ | ✗ |
| | Delete | ✓ | ✗ | ✗ |
| **Knowledge** | View | ✓ | ✓ | ✓ |
| | Create | ✓ | ✓ | ✗ |
| | Edit | ✓ | ✓ | ✗ |
| | Publish | ✓ | ✓ | ✗ |
| | Delete | ✓ | ✗ | ✗ |
| **Taxonomy** | View | ✓ | ✓ | ✓ |
| | Manage | ✓ | ✓ | ✗ |
| **Users** | View | ✓ | ✗ | ✗ |
| | Create | ✓ | ✗ | ✗ |
| | Edit | ✓ | ✗ | ✗ |
| | Delete | ✓ | ✗ | ✗ |
| | Assign Roles | ✓ | ✗ | ✗ |
| **Roles** | View | ✓ | ✗ | ✗ |
| | Manage | ✓ | ✗ | ✗ |
| **Audit Logs** | View | ✓ | ✗ | ✓ |
| **Analytics** | View | ✓ | ✓ | ✓ |
| **Settings** | View | ✓ | ✗ | ✗ |
| | Manage | ✓ | ✗ | ✗ |

**Notes:**

- The Content Manager role does not have delete permissions to prevent accidental data loss. Deletion is reserved for the Super Administrator.
- The Viewer / Analyst role has read-only access to content and analytics.
- The Audit Log is viewable by the Viewer / Analyst role for transparency, but only the Super Administrator can manage user access.

---

# 5. Role Assignment and Management

## 5.1 Role Assignment

- Only Super Administrators can assign roles to users.
- Role assignment must be logged in the Audit Log.
- Users cannot assign roles to themselves.

## 5.2 Role Creation

- New roles can be created by Super Administrators.
- New roles must be assigned a specific set of permissions.
- System roles (Super Administrator) cannot be deleted.

## 5.3 Role Deactivation

- Roles can be deactivated if they are no longer needed.
- Deactivating a role must not delete the users assigned to it.
- Users with deactivated roles must be reassigned or deactivated.

---

# 6. Privilege Escalation Prevention

The following rules prevent privilege escalation:

1.  **Self-Assignment Prohibition:** Users cannot modify their own roles or permissions.
2.  **Higher-Role Protection:** Users cannot modify users with a higher privilege level (e.g., a Content Manager cannot modify a Super Administrator).
3.  **Permission Validation:** The system must validate that the user performing an action has the required permission before executing it.
4.  **Mass Assignment Protection:** The `User` model must protect the `roles` and `permissions` attributes from mass assignment.
5.  **Route Protection:** All administrative routes must be protected by middleware that verifies the user's authentication and role.

---

# 7. Public User Accounts

Public user accounts are not confirmed in the current scope.

If public user accounts are approved in the future, they will require a separate role and permission model.

Public users must not have access to the Admin Portal or administrative permissions.

---

# 8. Audit Requirements

The following actions must be logged in the Audit Log:

- User creation.
- User deactivation.
- User deletion.
- Role assignment.
- Role removal.
- Permission change.
- Login attempts (successful and failed).
- Logout.
- Content publication.
- Content deletion.
- System setting changes.

Audit logs must be immutable and protected from unauthorized access.

---

# Document Completion Check

## Requirements Covered

- RBAC principles defined.
- Initial roles defined.
- Permission matrix defined.
- Role assignment and management rules defined.
- Privilege escalation prevention defined.
- Public user account boundary defined.
- Audit requirements defined.

## Decisions Established

- LABGOTO uses Role-Based Access Control.
- Server-side enforcement is mandatory.
- Principle of Least Privilege is enforced.
- Content Manager role does not have delete permissions.
- Super Administrator role is unrestricted.
- Public user accounts are deferred.
- All role changes must be audited.

## Dependencies

- Approved LABGOTO Master Directive.
- Approved Core Content Model.
- Approved Information Architecture.
- Approved Technology Stack.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/12_User_Roles_and_RBAC.md

It directly dictates the authorization logic for the Admin Portal and the database seeders for roles and permissions.

## Open Questions

1. Should the Viewer / Analyst role have access to audit logs?
2. Should there be a separate role for managing SOPs vs. general content?
3. Should two-factor authentication be mandatory for Super Administrators?

## TBD Decisions

- TBD: Final decision on audit log access for Viewer / Analyst role.
- TBD: Final decision on two-factor authentication requirements.
- TBD: Future public user role model.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE01-DOC12

## Context

LABGOTO requires a robust Role-Based Access Control (RBAC) model to ensure that the Admin Portal is secure and that users have only the access they need. This document defines the initial roles, permissions, and enforcement rules.

This prompt implements the documentation outcome of the User Roles and RBAC document.

## Objective

Create the official LABGOTO User Roles and RBAC document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository will have a binding authorization model for the platform.

## Existing Architecture

Relevant approved decisions:

- LABGOTO uses Laravel for the backend.
- Authorization must be enforced server-side.
- The Admin Portal is restricted to authenticated administrators.
- Audit logs are required for administrative actions.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- Documents 01 through 11.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/12_User_Roles_and_RBAC.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines the conceptual RBAC model.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that privilege escalation prevention rules are clearly documented.

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
- The file clearly defines the roles and permission matrix.
- The file defines privilege escalation prevention rules.

## Integration Requirements

This document must be used as the primary reference when defining the Backend Authorization Architecture and Admin Portal permissions.

## Definition of Done

This implementation is complete when:

- The User Roles and RBAC file exists.
- The file matches the approved Phase 01 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.