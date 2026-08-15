labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       ├── UserController.php
│   │   │       ├── RoleController.php
│   │   │       ├── SettingsController.php
│   │   │       └── AuditLogController.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StoreUserRequest.php
│   │           ├── UpdateUserRequest.php
│   │           ├── StoreRoleRequest.php
│   │           └── UpdateRoleRequest.php
│   ├── Models/
│   │   └── Setting.php
│   └── Policies/
│       ├── UserPolicy.php
│       ├── RolePolicy.php
│       ├── SettingPolicy.php
│       └── AuditLogPolicy.php
├── resources/
│   └── views/
│       ├── admin/
│       │   ├── users/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   ├── roles/
│       │   │   ├── index.blade.php
│       │   │   ├── create.blade.php
│       │   │   └── edit.blade.php
│       │   ├── settings/
│       │   │   └── index.blade.php
│       │   └── audit-logs/
│       │       ├── index.blade.php
│       │       └── show.blade.php
│       └── components/
│           └── admin/
│               └── permission-matrix.blade.php
├── routes/
│   └── admin.php
└── tests/
    └── Feature/
        └── Admin/
            ├── UserManagementTest.php
            ├── RoleManagementTest.php
            ├── SettingsManagementTest.php
            └── AuditLogViewerTest.php


IMPLEMENTATION PROMPT ID: LABGATO-BUILD24-ADMIN-SYSTEM

CONTEXT:
LabGato RBAC infrastructure, settings table, and audit logging all exist in the backend, but their admin management screens were left as "Soon" placeholders. This build implements all four admin system screens: Users, Roles, Settings, and Audit Logs.

OBJECTIVE:
Implement full admin management for Users, Roles, Settings, and a read-only Audit Logs viewer. Wire up the previously dead sidebar navigation links.

EXISTING ARCHITECTURE:
- User, Role, Permission models exist (Build 4).
- Setting model/table exists (Build 2).
- AuditLog model + AuditLogService exist (Build 7).
- RBAC middleware and policies pattern established.
- Theme tokens and admin CRUD patterns established.

FILES TO CREATE:
- app/Http/Controllers/Admin/UserController.php
- app/Http/Controllers/Admin/RoleController.php
- app/Http/Controllers/Admin/SettingsController.php
- app/Http/Controllers/Admin/AuditLogController.php
- app/Http/Requests/Admin/StoreUserRequest.php
- app/Http/Requests/Admin/UpdateUserRequest.php
- app/Http/Requests/Admin/StoreRoleRequest.php
- app/Http/Requests/Admin/UpdateRoleRequest.php
- app/Models/Setting.php
- app/Policies/UserPolicy.php
- app/Policies/RolePolicy.php
- app/Policies/SettingPolicy.php
- app/Policies/AuditLogPolicy.php
- resources/views/admin/users/index.blade.php
- resources/views/admin/users/create.blade.php
- resources/views/admin/users/edit.blade.php
- resources/views/admin/roles/index.blade.php
- resources/views/admin/roles/create.blade.php
- resources/views/admin/roles/edit.blade.php
- resources/views/admin/settings/index.blade.php
- resources/views/admin/audit-logs/index.blade.php
- resources/views/admin/audit-logs/show.blade.php
- resources/views/components/admin/permission-matrix.blade.php
- tests/Feature/Admin/UserManagementTest.php
- tests/Feature/Admin/RoleManagementTest.php
- tests/Feature/Admin/SettingsManagementTest.php
- tests/Feature/Admin/AuditLogViewerTest.php

FILES TO MODIFY:
- routes/admin.php
- resources/views/components/admin/sidebar.blade.php (wire the "Soon" links)
- resources/views/components/admin/navigation.blade.php

TASK 1: USER MANAGEMENT

Create UserController with: index, create, store, edit, update, destroy (soft deactivate).

Routes:
- GET /admin/users (users.view)
- GET /admin/users/create (users.create)
- POST /admin/users (users.create)
- GET /admin/users/{user}/edit (users.edit)
- PUT /admin/users/{user} (users.edit)
- DELETE /admin/users/{user} (users.delete)

Index page:
- List users with name, email, role, status, last login.
- Search by name/email.
- Filter by role and status.
- Pagination.

Create/Edit form:
- Name, email, password (optional on edit), role select, active toggle.

Rules:
- Cannot deactivate yourself.
- Cannot deactivate the last active Super Administrator.
- Cannot assign a role higher than your own (unless you are Super Admin).
- Password must meet strength requirements.
- Audit log all user changes.

TASK 2: ROLE MANAGEMENT

Create RoleController with: index, create, store, edit, update, destroy.

Routes:
- GET /admin/roles (roles.view)
- GET /admin/roles/create (roles.create)
- POST /admin/roles (roles.create)
- GET /admin/roles/{role}/edit (roles.edit)
- PUT /admin/roles/{role} (roles.edit)
- DELETE /admin/roles/{role} (roles.delete)

Create/Edit form:
- Name, description.
- Permission matrix (checkbox grid grouped by module).

Create resources/views/components/admin/permission-matrix.blade.php:
- Group permissions by module (calculators, tools, reference-ranges, sops, etc.).
- Checkbox for each permission.
- "Select all" per module.

Rules:
- Cannot delete the Super Admin role.
- Cannot delete a role that has assigned users.
- Cannot modify your own role's permissions in a way that locks you out.
- Super Admin role permissions are locked (cannot be reduced).
- Audit log all role changes.

TASK 3: SETTINGS MANAGEMENT

Create Setting model (if not present) and SettingsController.

Routes:
- GET /admin/settings (settings.view)
- PUT /admin/settings (settings.edit)

Index page:
- Group settings by category (General, SEO, AI, Security, etc.).
- Render appropriate input type per setting (text, number, boolean, select).
- Save button per group or global save.

Security rules:
- NEVER expose secrets (API keys, passwords) as editable fields.
- Sensitive settings (GROQ_API_KEY, DB credentials) must be read-only or hidden.
- Only Super Admin can edit AI and Security settings.
- Audit log all setting changes (old value → new value, masking sensitive values).

TASK 4: AUDIT LOGS VIEWER

Create AuditLogController with: index, show.

Routes:
- GET /admin/audit-logs (audit-logs.view)
- GET /admin/audit-logs/{log} (audit-logs.view)

Index page:
- List audit logs with: timestamp, user, action, resource type, resource ID.
- Search by action or user.
- Filter by date range, action type, resource type.
- Pagination (50 per page).
- Read-only (no edit/delete).

Show page:
- Display full log details.
- Show sanitized changes (old → new).
- Show IP address and user agent.

Rules:
- Audit logs are immutable. No edit or delete actions.
- Mask any sensitive fields in the displayed changes.
- Only users with audit-logs.view can access.

TASK 5: WIRE NAVIGATION

Update sidebar.blade.php and navigation.blade.php:
- Replace "Users" Soon → route('admin.users.index')
- Replace "Roles" Soon → route('admin.roles.index')
- Replace "Settings" Soon → route('admin.settings.index')
- Replace "Audit Logs" Soon → route('admin.audit-logs.index')
- Each link only renders if the user has the corresponding view permission.

TASK 6: FEATURE TESTS

Create tests/Feature/Admin/UserManagementTest.php:
- Test admin can list/create/edit users with permission.
- Test cannot deactivate self.
- Test cannot deactivate last super admin.
- Test unauthorized users are blocked.

Create tests/Feature/Admin/RoleManagementTest.php:
- Test admin can list/create/edit roles with permission.
- Test cannot delete super admin role.
- Test cannot delete role with assigned users.
- Test permission matrix saves correctly.

Create tests/Feature/Admin/SettingsManagementTest.php:
- Test admin can view settings.
- Test admin can edit non-sensitive settings.
- Test sensitive settings are not editable via UI.
- Test setting changes are audited.

Create tests/Feature/Admin/AuditLogViewerTest.php:
- Test admin can view audit logs with permission.
- Test audit logs are read-only (no edit/delete routes).
- Test filtering and search work.
- Test sensitive fields are masked.

SECURITY REQUIREMENTS:
- All actions enforce RBAC server-side.
- Audit log every administrative change.
- Never expose or allow editing of secrets via Settings UI.
- Prevent privilege escalation (cannot grant yourself higher permissions).
- Prevent self-lockout (cannot deactivate last super admin or remove own permissions).

DEFINITION OF DONE:
- Users can be created, edited, and deactivated.
- Roles can be created, edited, and assigned permissions via matrix.
- Settings can be viewed and edited (non-sensitive only).
- Audit logs can be viewed, searched, and filtered (read-only).
- All four sidebar "Soon" links are wired and functional.
- All feature tests pass.
- No privilege escalation or self-lockout is possible.
- All changes are audit logged.