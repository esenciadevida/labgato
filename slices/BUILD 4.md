LABGOTO/
├── app/
│   ├── Models/
│   │   ├── User.php
│   │   └── Role.php
│   ├── Policies/
│   │   ├── UserPolicy.php
│   │   ├── RolePolicy.php
│   │   ├── SettingPolicy.php
│   │   ├── CategoryPolicy.php
│   │   ├── TagPolicy.php
│   │   └── AuditLogPolicy.php
│   ├── Http/
│   │   └── Middleware/
│   │       ├── EnsureUserIsActive.php
│   │       └── EnsureUserHasPermission.php
│   ├── Services/
│   │   ├── AuditLogService.php
│   │   └── PermissionCacheService.php
│   ├── Observers/
│   │   ├── RoleObserver.php
│   │   └── PermissionObserver.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php
├── database/
│   └── seeders/
│       └── RolePermissionSeeder.php
└── tests/
    ├── Unit/
    │   └── Models/
    │       ├── UserRbacTest.php
    │       └── RolePermissionTest.php
    └── Feature/
        └── Admin/
            ├── RbacAccessTest.php
            └── PrivilegeEscalationTest.php


            IMPLEMENTATION PROMPT ID: LABGOTO-SLICE1-BUILD4

CONTEXT:
LABGOTO is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Build 1, Build 2, and Build 3 are complete. Administrator authentication, session security, password reset, and active-user enforcement are implemented.

We are now implementing Build 4: RBAC Implementation.

This build implements the Role-Based Access Control system that protects administrative routes and actions.

OBJECTIVE:
Implement server-side Role-Based Access Control using roles, permissions, middleware, gates, policies, and permission caching.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- RBAC is enforced server-side.
- Frontend hiding is not security.
- Users belong to many Roles.
- Roles belong to many Permissions.
- Permissions use module.action naming convention.
- Principle of least privilege applies.
- Default access must be denied.
- Users must not assign roles they do not possess.
- Users must not modify Super Administrator accounts unless they are Super Administrators.
- Users must not assign roles to themselves.
- Permission changes must be audited.
- Permission caching must use the database cache driver.
- No Redis.
- Public user RBAC is out of scope.

FILES TO INSPECT:
- app/Models/User.php
- app/Models/Role.php
- app/Models/Permission.php
- database/seeders/RolePermissionSeeder.php
- database/migrations/
- app/Services/AuditLogService.php
- app/Http/Middleware/EnsureUserIsActive.php
- bootstrap/app.php
- config/cache.php
- config/LABGOTO.php
- tests/Feature/Auth/

FILES TO CREATE:
- app/Http/Middleware/EnsureUserHasPermission.php
- app/Policies/UserPolicy.php
- app/Policies/RolePolicy.php
- app/Policies/SettingPolicy.php
- app/Policies/CategoryPolicy.php
- app/Policies/TagPolicy.php
- app/Policies/AuditLogPolicy.php
- app/Services/PermissionCacheService.php
- app/Observers/RoleObserver.php
- app/Observers/PermissionObserver.php
- tests/Unit/Models/UserRbacTest.php
- tests/Unit/Models/RolePermissionTest.php
- tests/Feature/Admin/RbacAccessTest.php
- tests/Feature/Admin/PrivilegeEscalationTest.php

FILES TO MODIFY:
- app/Models/User.php
- app/Models/Role.php
- app/Models/Permission.php
- app/Providers/AppServiceProvider.php
- bootstrap/app.php
- database/seeders/RolePermissionSeeder.php
- app/Services/AuditLogService.php if enhancement is required

USER MODEL RBAC REQUIREMENTS:

Modify app/Models/User.php.

Add the following methods:

1. hasRole(string $roleSlug): bool
   - Return true if the user has a role with the given slug.
   - Use cached roles where practical.

2. hasPermission(string $permissionSlug): bool
   - Return true if the user has the given permission through any assigned role.
   - Return true immediately if the user has admin.full-access.
   - Use the PermissionCacheService.
   - Return false by default.

3. hasAnyPermission(array $permissionSlugs): bool
   - Return true if the user has at least one of the given permissions.

4. hasAllPermissions(array $permissionSlugs): bool
   - Return true only if the user has every given permission.

5. isSuperAdministrator(): bool
   - Return true if the user has the super-administrator role or admin.full-access permission.

Rules:
- Do not query the database directly inside permission checks when caching is available.
- Do not expose role assignment through mass assignment.
- Keep the roles relationship already created in Build 2.

ROLE MODEL RBAC REQUIREMENTS:

Modify app/Models/Role.php.

Add the following methods:

1. hasPermission(string $permissionSlug): bool
   - Return true if the role has the given permission.

2. isSystemRole(): bool
   - Return true if is_system_role is true.

Rules:
- Prevent deletion of system roles.
- Keep the permissions relationship already created in Build 2.

PERMISSION CACHE SERVICE REQUIREMENTS:

Create app/Services/PermissionCacheService.php.

Responsibilities:
- Retrieve permissions for a user ID.
- Store permissions in the database cache driver.
- Invalidate permissions for a user ID.
- Invalidate permissions for all users with a given role ID.
- Invalidate permissions for all users when a permission is modified.

Cache key structure:
- user:{user_id}:permissions
- user:{user_id}:roles

Rules:
- Cache driver must remain database.
- Do not use Redis.
- Cached permissions must be simple arrays of permission slugs.
- Cached roles must be simple arrays of role slugs.
- Cache TTL may be long, but invalidation must occur immediately on role or permission changes.

PERMISSION MIDDLEWARE REQUIREMENTS:

Create app/Http/Middleware/EnsureUserHasPermission.php.

Middleware signature should support one or more permission slugs.

Example usage:

Route::middleware('permission:users.view')->group(...)
Route::middleware('permission:users.edit')->group(...)
Route::middleware('permission:users.view,users.edit')->group(...)

Behavior:
- If the user is not authenticated, redirect to auth.login.
- If the user is authenticated but does not have at least one required permission, abort with 403.
- If the user has admin.full-access, allow access.
- Log unauthorized attempts for sensitive modules where practical.

Register the middleware alias in bootstrap/app.php:

- Alias: permission
- Class: App\Http\Middleware\EnsureUserHasPermission

GATES REQUIREMENTS:

Define gates in app/Providers/AppServiceProvider.php or a dedicated AuthServiceProvider.

Use Gate::before to allow Super Administrator bypass:

Gate::before(function ($user, $ability) {
    if ($user->hasPermission('admin.full-access')) {
        return true;
    }
});

Define gates for foundation permissions:

Dashboard:
- dashboard.view

Users:
- users.view
- users.create
- users.edit
- users.delete
- users.assign-roles

Roles:
- roles.view
- roles.manage

Taxonomy:
- taxonomy.view
- taxonomy.manage

Audit Logs:
- audit-logs.view

Analytics:
- analytics.view

Settings:
- settings.view
- settings.manage

Each gate should check the corresponding permission using the User model.

POLICY REQUIREMENTS:

Create policies for foundation modules only.

Do not create policies for future resources such as calculators, tools, reference ranges, SOPs, interpretation, or knowledge in this build.

1. UserPolicy
   - viewAny: requires users.view
   - view: requires users.view
   - create: requires users.create
   - update: requires users.edit
   - delete: requires users.delete
   - assignRoles: requires users.assign-roles
   - modifySuperAdministrator: only allowed if the acting user is a Super Administrator

2. RolePolicy
   - viewAny: requires roles.view
   - view: requires roles.view
   - manage: requires roles.manage

3. SettingPolicy
   - viewAny: requires settings.view
   - update: requires settings.manage

4. CategoryPolicy
   - viewAny: requires taxonomy.view
   - view: requires taxonomy.view
   - manage: requires taxonomy.manage

5. TagPolicy
   - viewAny: requires taxonomy.view
   - view: requires taxonomy.view
   - manage: requires taxonomy.manage

6. AuditLogPolicy
   - viewAny: requires audit-logs.view
   - view: requires audit-logs.view

Rules:
- Policies must return boolean decisions.
- Policies must not modify data.
- Policies must not expose sensitive information.
- Policies must be registered in AppServiceProvider.

PRIVILEGE ESCALATION PREVENTION REQUIREMENTS:

Implement rules in UserPolicy and RolePolicy or supporting service logic.

Rules:

1. A user must not assign roles to themselves.
2. A user must not assign a role containing permissions they do not themselves possess, unless the acting user is a Super Administrator.
3. A non-Super Administrator must not modify a Super Administrator account.
4. A non-Super Administrator must not assign the super-administrator role.
5. A non-Super Administrator must not grant admin.full-access.
6. Role assignment must be validated server-side.
7. UI restrictions are not sufficient.

These rules will be used in Build 5 and later builds when user management UI exists, but the policy logic must exist now.

AUDIT LOGGING REQUIREMENTS:

Enhance app/Services/AuditLogService.php if needed.

Add support for RBAC audit actions:

- rbac.role_assigned
- rbac.role_removed
- rbac.permission_assigned_to_role
- rbac.permission_removed_from_role
- rbac.role_created
- rbac.role_updated
- rbac.role_deleted
- rbac.permission_created
- rbac.permission_updated
- rbac.permission_deleted

Rules:
- Do not store passwords or tokens.
- Store old and new role slugs where practical.
- Store the acting user ID.
- Store IP address and user agent.

OBSERVER REQUIREMENTS:

Create app/Observers/RoleObserver.php.

RoleObserver should invalidate permission caches when:
- A role's permissions are modified.
- A role is deleted.

Create app/Observers/PermissionObserver.php.

PermissionObserver should invalidate permission caches when:
- A permission is created.
- A permission is updated.
- A permission is deleted.

Register observers in AppServiceProvider.

Because pivot changes may not always trigger model observers, also ensure that any service or controller method that attaches or detaches permissions/roles calls PermissionCacheService invalidation explicitly.

SEEDER REQUIREMENTS:

Review database/seeders/RolePermissionSeeder.php.

Ensure it remains idempotent.

Ensure the following roles exist with correct permissions:

Super Administrator:
- slug: super-administrator
- is_system_role: true
- permissions: all permissions including admin.full-access

Content Manager:
- slug: content-manager
- permissions as defined in Build 2

Viewer / Analyst:
- slug: viewer-analyst
- permissions as defined in Build 2

If Build 2 already completed this correctly, do not duplicate. Only correct inconsistencies.

TESTING REQUIREMENTS:

Create tests/Unit/Models/UserRbacTest.php.

Required tests:

1. test_user_can_check_role_by_slug
   - Create user and role.
   - Attach role.
   - Assert hasRole returns true.
   - Assert hasRole returns false for missing role.

2. test_user_permission_check_returns_false_by_default
   - Create user without roles.
   - Assert hasPermission returns false.

3. test_user_permission_check_returns_true_when_role_has_permission
   - Create user, role, permission.
   - Attach permission to role.
   - Attach role to user.
   - Assert hasPermission returns true.

4. test_user_with_full_access_permission_bypasses_permission_checks
   - Create user and role.
   - Attach admin.full-access permission to role.
   - Attach role to user.
   - Assert hasPermission returns true for arbitrary permission slug.

5. test_has_any_permission_returns_true_when_user_has_one_permission
   - Create user with one permission.
   - Assert hasAnyPermission returns true when checking multiple permissions.

6. test_has_all_permissions_returns_false_when_user_missing_one_permission
   - Create user with one permission.
   - Assert hasAllPermissions returns false when checking multiple permissions.

Create tests/Unit/Models/RolePermissionTest.php.

Required tests:

1. test_role_can_check_permission_by_slug
   - Create role and permission.
   - Attach permission.
   - Assert role hasPermission returns true.

2. test_system_role_cannot_be_deleted
   - Create role with is_system_role true.
   - Attempt deletion.
   - Assert role still exists or deletion is blocked.

Create tests/Feature/Admin/RbacAccessTest.php.

Required tests:

1. test_guest_cannot_access_admin_dashboard
   - Visit /admin/dashboard as guest.
   - Assert redirected to auth.login.

2. test_active_user_with_dashboard_permission_can_access_admin_dashboard
   - Create active user with dashboard.view permission.
   - Authenticate.
   - Visit /admin/dashboard.
   - Assert successful response.

3. test_active_user_without_dashboard_permission_receives_403
   - Create active user without dashboard.view permission.
   - Authenticate.
   - Visit /admin/dashboard.
   - Assert 403 response.

4. test_permission_middleware_blocks_unauthorized_permission
   - Create active user without users.view permission.
   - Authenticate.
   - Attempt to access a route protected by permission:users.view if route exists.
   - If no route exists yet, test middleware directly.

Create tests/Feature/Admin/PrivilegeEscalationTest.php.

Required tests:

1. test_user_cannot_assign_roles_to_themselves
   - Use UserPolicy or future service logic.
   - Assert policy denies self role assignment.

2. test_non_super_admin_cannot_modify_super_admin
   - Create non-Super Administrator user.
   - Create Super Administrator user.
   - Assert UserPolicy denies update for Super Administrator target.

3. test_non_super_admin_cannot_assign_super_admin_role
   - Create non-Super Administrator user.
   - Assert policy denies assigning super-administrator role.

4. test_user_cannot_assign_permissions_they_do_not_possess
   - Create user with limited permissions.
   - Assert policy denies assigning a role containing permissions the user does not possess.

If role assignment controllers do not exist yet, test the policies directly. Controllers will be built later.

SECURITY REQUIREMENTS:

- All authorization must be enforced server-side.
- Middleware must not be bypassed by direct route access.
- Policies must not trust frontend input.
- Permission checks must use cached permissions where practical.
- Permission cache invalidation must occur immediately on RBAC changes.
- Do not expose missing permission slugs publicly in production error pages.
- Do not allow mass assignment of roles or permissions.
- Do not store secrets in audit logs.

ACCESSIBILITY REQUIREMENTS:

No UI is required for Build 4.

Accessibility requirements will apply when admin UI is built in Build 5 and later.

SEO REQUIREMENTS:

No SEO implementation required for Build 4.

API:

No API endpoints are required for Build 4.

DATABASE:

No new tables are required.

Use existing tables:
- users
- roles
- permissions
- role_user
- permission_role
- audit_logs

DEFINITION OF DONE:

- User model has hasRole, hasPermission, hasAnyPermission, hasAllPermissions, and isSuperAdministrator methods.
- Role model has hasPermission and isSystemRole methods.
- Permission middleware is registered and functional.
- Gates are defined for foundation permissions.
- Policies are created for foundation modules.
- Super Administrator bypass works through Gate::before or equivalent.
- Permission caching works using database cache.
- Permission cache invalidation works on role and permission changes.
- Privilege escalation prevention policies exist.
- RBAC audit actions are supported.
- Unit tests for User RBAC pass.
- Unit tests for Role permissions pass.
- Feature tests for RBAC access pass.
- Feature tests for privilege escalation prevention pass.
- No resource-specific policies are created.
- No admin CRUD UI is created.
- No public UI is created.
- No secrets are committed.