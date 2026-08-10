labgato/
├── app/
│   └── Models/
│       ├── User.php
│       ├── Role.php
│       ├── Permission.php
│       ├── Setting.php
│       ├── AuditLog.php
│       ├── Category.php
│       └── Tag.php
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── RoleFactory.php
│   │   ├── PermissionFactory.php
│   │   ├── SettingFactory.php
│   │   ├── AuditLogFactory.php
│   │   ├── CategoryFactory.php
│   │   └── TagFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_roles_table.php
│   │   ├── 0001_01_01_000002_create_permissions_table.php
│   │   ├── 0001_01_01_000003_create_role_user_table.php
│   │   ├── 0001_01_01_000004_create_permission_role_table.php
│   │   ├── 0001_01_01_000005_create_settings_table.php
│   │   ├── 0001_01_01_000006_create_audit_logs_table.php
│   │   ├── 0001_01_01_000007_create_categories_table.php
│   │   ├── 0001_01_01_000008_create_tags_table.php
│   │   ├── 0001_01_01_000009_create_sessions_table.php
│   │   ├── 0001_01_01_000010_create_cache_table.php
│   │   ├── 0001_01_01_000011_create_jobs_table.php
│   │   └── 0001_01_01_000012_create_password_reset_tokens_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RolePermissionSeeder.php
│       └── AdminUserSeeder.php
├── tests/
│   └── Feature/
│       └── Database/
│           └── FoundationDatabaseTest.php
└── .env.example


Laravel already generated default migrations for users, sessions, cache, jobs, failed_jobs, or password_reset_tokens, modify them instead of duplicating them.
If your Laravel version already includes the sessions table inside the users migration, keep that structure and do not create a duplicate sessions migration.
Migration file names may vary slightly based on Laravel’s default timestamps. Preserve Laravel’s order and naming convention.

IMPLEMENTATION PROMPT ID: LABGATO-SLICE1-BUILD2

CONTEXT:
LabGato is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Build 1 is complete. The Laravel application boots, environment configuration exists, and database/session/cache/queue drivers are configured to use the database where practical.

We are now implementing Build 2: Database Foundation & Migrations.

OBJECTIVE:
Create the foundation database schema, Eloquent models, factories, and seeders for LabGato.

This build must create the database foundation for:
- Users
- Roles
- Permissions
- Role-user pivot
- Permission-role pivot
- Settings
- Audit logs
- Categories
- Tags
- Framework tables for sessions, cache, jobs, failed jobs, and password reset tokens

This build must NOT create resource-specific tables yet, such as:
- calculators
- tools
- reference_ranges
- sops
- interpretation_resources
- knowledge_resources

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Database engine: MySQL/MariaDB with InnoDB.
- Charset: utf8mb4.
- Collation: utf8mb4_unicode_ci.
- Sessions use database driver.
- Cache uses database driver.
- Queue uses database driver.
- Audit logs are immutable.
- Settings use key-value storage.
- Categories support hierarchy.
- Tags are flat.
- RBAC uses users, roles, permissions, and pivot tables.
- Public user accounts are not part of the current scope.
- No Redis.
- No external services required.

FILES TO INSPECT:
- database/migrations/
- database/factories/
- database/seeders/
- app/Models/User.php
- config/database.php
- config/session.php
- config/cache.php
- config/queue.php
- .env.example
- composer.json

FILES TO CREATE:
- app/Models/Role.php
- app/Models/Permission.php
- app/Models/Setting.php
- app/Models/AuditLog.php
- app/Models/Category.php
- app/Models/Tag.php
- database/factories/RoleFactory.php
- database/factories/PermissionFactory.php
- database/factories/SettingFactory.php
- database/factories/AuditLogFactory.php
- database/factories/CategoryFactory.php
- database/factories/TagFactory.php
- database/migrations/xxxx_create_roles_table.php
- database/migrations/xxxx_create_permissions_table.php
- database/migrations/xxxx_create_role_user_table.php
- database/migrations/xxxx_create_permission_role_table.php
- database/migrations/xxxx_create_settings_table.php
- database/migrations/xxxx_create_audit_logs_table.php
- database/migrations/xxxx_create_categories_table.php
- database/migrations/xxxx_create_tags_table.php
- database/seeders/RolePermissionSeeder.php
- database/seeders/AdminUserSeeder.php
- tests/Feature/Database/FoundationDatabaseTest.php

FILES TO MODIFY:
- app/Models/User.php
- database/factories/UserFactory.php
- database/seeders/DatabaseSeeder.php
- database/migrations/xxxx_create_users_table.php
- database/migrations/xxxx_create_sessions_table.php if not already provided by Laravel
- database/migrations/xxxx_create_cache_table.php if not already provided by Laravel
- database/migrations/xxxx_create_jobs_table.php if not already provided by Laravel
- database/migrations/xxxx_create_password_reset_tokens_table.php if not already provided by Laravel
- config/labgato.php
- .env.example

DATABASE REQUIREMENTS:

1. USERS TABLE
Modify the existing users table migration if it already exists.

Required columns:
- id: primary key
- name: string, required
- email: string, required, unique
- password: string, required
- is_active: boolean, default true
- last_login_at: timestamp, nullable
- remember_token: string, nullable
- created_at: timestamp
- updated_at: timestamp

Indexes:
- unique index on email

Rules:
- Do not add soft deletes to users in this build.
- Passwords must be hashed before storage.
- This table is for administrators only, not public users.

2. ROLES TABLE
Required columns:
- id: primary key
- name: string, required, unique
- slug: string, required, unique
- description: text, nullable
- is_system_role: boolean, default false
- created_at: timestamp
- updated_at: timestamp

Indexes:
- unique index on name
- unique index on slug

3. PERMISSIONS TABLE
Required columns:
- id: primary key
- name: string, required, unique
- slug: string, required, unique
- group: string, nullable
- created_at: timestamp
- updated_at: timestamp

Indexes:
- unique index on name
- unique index on slug
- index on group

4. ROLE_USER TABLE
Required columns:
- id: primary key
- role_id: unsignedBigInteger, foreign key to roles.id, cascade on delete
- user_id: unsignedBigInteger, foreign key to users.id, cascade on delete
- created_at: timestamp
- updated_at: timestamp

Indexes:
- unique composite index on role_id and user_id

5. PERMISSION_ROLE TABLE
Required columns:
- id: primary key
- permission_id: unsignedBigInteger, foreign key to permissions.id, cascade on delete
- role_id: unsignedBigInteger, foreign key to roles.id, cascade on delete
- created_at: timestamp
- updated_at: timestamp

Indexes:
- unique composite index on permission_id and role_id

6. SETTINGS TABLE
Required columns:
- id: primary key
- key: string, required, unique
- value: text, nullable
- type: string, default 'string'
- created_at: timestamp
- updated_at: timestamp

Indexes:
- unique index on key

Allowed type values:
- string
- boolean
- integer
- json

7. AUDIT_LOGS TABLE
Required columns:
- id: primary key
- user_id: unsignedBigInteger, nullable, foreign key to users.id, null on delete
- action: string, required
- resource_type: string, nullable
- resource_id: unsignedBigInteger, nullable
- changes: json, nullable
- ip_address: string, nullable, max length 45
- user_agent: text, nullable
- created_at: timestamp

Indexes:
- index on user_id
- index on action
- composite index on resource_type and resource_id
- index on created_at

Rules:
- Audit logs are immutable.
- Do not add updated_at.
- Do not store passwords, tokens, API keys, or secrets in changes.

8. CATEGORIES TABLE
Required columns:
- id: primary key
- name: string, required
- slug: string, required, unique
- description: text, nullable
- parent_id: unsignedBigInteger, nullable, foreign key to categories.id, null on delete
- sort_order: integer, default 0
- is_active: boolean, default true
- created_at: timestamp
- updated_at: timestamp

Indexes:
- unique index on slug
- index on parent_id
- index on is_active

9. TAGS TABLE
Required columns:
- id: primary key
- name: string, required, unique
- slug: string, required, unique
- created_at: timestamp
- updated_at: timestamp

Indexes:
- unique index on name
- unique index on slug

10. FRAMEWORK TABLES
Use Laravel’s existing migrations where available.

Ensure the following tables exist:
- sessions
- cache
- cache_locks
- jobs
- job_batches if provided by Laravel
- failed_jobs
- password_reset_tokens

Do not duplicate framework migrations if Laravel already provides them.

MODEL REQUIREMENTS:

1. USER MODEL
Modify app/Models/User.php.

Requirements:
- Extend Authenticatable.
- Use HasFactory and Notifiable.
- Fillable fields: name, email, password, is_active, last_login_at.
- Hidden fields: password, remember_token.
- Cast password to hashed.
- Cast is_active to boolean.
- Cast last_login_at to datetime.
- Add roles() belongsToMany relationship using role_user table.
- Do not add complex permission methods yet. Those belong to Build 4.

2. ROLE MODEL
Requirements:
- Fillable fields: name, slug, description, is_system_role.
- Cast is_system_role to boolean.
- Add permissions() belongsToMany relationship using permission_role table.
- Add users() belongsToMany relationship using role_user table.
- Prevent deletion of system roles in the model deleting event if practical.

3. PERMISSION MODEL
Requirements:
- Fillable fields: name, slug, group.
- Add roles() belongsToMany relationship using permission_role table.

4. SETTING MODEL
Requirements:
- Fillable fields: key, value, type.
- Do not add complex setting retrieval services yet.

5. AUDIT LOG MODEL
Requirements:
- Fillable fields: user_id, action, resource_type, resource_id, changes, ip_address, user_agent, created_at.
- Cast changes to array.
- Cast created_at to datetime.
- Disable updated_at by setting const UPDATED_AT = null.
- Add user() belongsTo relationship.
- The model should be treated as immutable.
- Do not expose update or delete functionality.

6. CATEGORY MODEL
Requirements:
- Fillable fields: name, slug, description, parent_id, sort_order, is_active.
- Cast is_active to boolean.
- Add parent() belongsTo relationship.
- Add children() hasMany relationship.

7. TAG MODEL
Requirements:
- Fillable fields: name, slug.

FACTORY REQUIREMENTS:

Create factories for:
- User
- Role
- Permission
- Setting
- AuditLog
- Category
- Tag

UserFactory requirements:
- Generate valid name.
- Generate unique safe email.
- Generate hashed password.
- Set is_active to true.
- Set last_login_at to null by default.

RoleFactory requirements:
- Generate unique name.
- Generate unique slug based on name.
- Set is_system_role to false by default.

PermissionFactory requirements:
- Generate unique name.
- Generate unique slug using module.action format.
- Generate group.

SettingFactory requirements:
- Generate unique key.
- Generate value.
- Set type to string by default.

AuditLogFactory requirements:
- Generate action.
- Optionally associate with a user.
- Generate resource_type and resource_id.
- Generate changes as array without sensitive fields.
- Generate ip_address and user_agent.

CategoryFactory requirements:
- Generate unique name.
- Generate unique slug.
- Set is_active to true.
- Set parent_id to null by default.

TagFactory requirements:
- Generate unique name.
- Generate unique slug.

SEEDER REQUIREMENTS:

1. ROLE PERMISSION SEEDER
Create database/seeders/RolePermissionSeeder.php.

The seeder must be idempotent and safe to run multiple times.

Use firstOrCreate for permissions and roles.
Use sync for role permissions.

Create the following foundation permissions:

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

Tools:
- tools.view
- tools.create
- tools.edit
- tools.publish
- tools.delete

Calculators:
- calculators.view
- calculators.create
- calculators.edit
- calculators.publish
- calculators.delete

Reference Ranges:
- reference-ranges.view
- reference-ranges.create
- reference-ranges.edit
- reference-ranges.publish
- reference-ranges.delete

SOPs:
- sops.view
- sops.create
- sops.edit
- sops.publish
- sops.delete

Interpretation:
- interpretation.view
- interpretation.create
- interpretation.edit
- interpretation.publish
- interpretation.delete

Knowledge:
- knowledge.view
- knowledge.create
- knowledge.edit
- knowledge.publish
- knowledge.delete

Reserved:
- admin.full-access

Create the following roles:

Super Administrator:
- slug: super-administrator
- is_system_role: true
- permissions: all permissions, including admin.full-access

Content Manager:
- slug: content-manager
- is_system_role: false
- permissions:
  - dashboard.view
  - tools.view
  - tools.create
  - tools.edit
  - tools.publish
  - calculators.view
  - calculators.create
  - calculators.edit
  - calculators.publish
  - reference-ranges.view
  - reference-ranges.create
  - reference-ranges.edit
  - reference-ranges.publish
  - sops.view
  - sops.create
  - sops.edit
  - sops.publish
  - interpretation.view
  - interpretation.create
  - interpretation.edit
  - interpretation.publish
  - knowledge.view
  - knowledge.create
  - knowledge.edit
  - knowledge.publish
  - taxonomy.view
  - taxonomy.manage
  - analytics.view

Viewer / Analyst:
- slug: viewer-analyst
- is_system_role: false
- permissions:
  - dashboard.view
  - tools.view
  - calculators.view
  - reference-ranges.view
  - sops.view
  - interpretation.view
  - knowledge.view
  - taxonomy.view
  - audit-logs.view
  - analytics.view

Rules:
- Content Manager must not receive delete permissions.
- Viewer / Analyst must receive read-only permissions.
- admin.full-access must only be assigned to Super Administrator.
- Do not create actual user accounts in this seeder.

2. ADMIN USER SEEDER
Create database/seeders/AdminUserSeeder.php.

Rules:
- This seeder must only run in the local environment.
- It must never create a default administrator in production.
- It must use environment/configuration values, not hardcoded secrets.
- It must assign the Super Administrator role to the created user.

Expected configuration values:
- LABGATO_SEED_ADMIN=false by default
- LABGATO_ADMIN_EMAIL=
- LABGATO_ADMIN_PASSWORD=

If LABGATO_SEED_ADMIN is not true, the seeder must do nothing.

3. DATABASE SEEDER
Modify database/seeders/DatabaseSeeder.php.

It must call:
- RolePermissionSeeder
- AdminUserSeeder only when app environment is local and admin seeding is enabled

ENVIRONMENT REQUIREMENTS:

Modify .env.example and add:

LABGATO_SEED_ADMIN=false
LABGATO_ADMIN_EMAIL=
LABGATO_ADMIN_PASSWORD=

Modify config/labgato.php and add:

'seed' => [
    'admin' => [
        'enabled' => env('LABGATO_SEED_ADMIN', false),
        'email' => env('LABGATO_ADMIN_EMAIL'),
        'password' => env('LABGATO_ADMIN_PASSWORD'),
    ],
],

TESTING REQUIREMENTS:

Create tests/Feature/Database/FoundationDatabaseTest.php.

Use RefreshDatabase.

Tests required:

1. test_foundation_tables_exist
Assert that the following tables exist:
- users
- roles
- permissions
- role_user
- permission_role
- settings
- audit_logs
- categories
- tags
- sessions
- cache
- jobs
- failed_jobs
- password_reset_tokens

2. test_user_can_be_assigned_a_role
Create a user and role using factories.
Attach the role to the user.
Assert the user has the role in the relationship.

3. test_role_can_be_assigned_a_permission
Create a role and permission using factories.
Attach the permission to the role.
Assert the role has the permission in the relationship.

4. test_category_can_have_parent_and_children
Create a parent category and child category.
Assert the child parent_id matches the parent id.
Assert the parent children relationship contains the child.

5. test_audit_log_does_not_use_updated_at
Create an audit log using factory.
Assert the model uses created_at and does not use updated_at.

6. test_role_permission_seeder_is_idempotent
Run RolePermissionSeeder twice.
Assert role count and permission count do not increase after the second run.

COMMANDS TO RUN:

php artisan migrate:fresh --seed
php artisan test
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=RolePermissionSeeder

The second seeder run must not duplicate records.

SECURITY REQUIREMENTS:

- Passwords must be hashed.
- Do not store plaintext passwords.
- Do not commit .env.
- Do not commit real credentials.
- Do not store secrets in seeders.
- Audit log changes field must not include sensitive attributes.
- Default admin seeder must be disabled by default.
- Default admin seeder must never run in production.

API:
No API changes.

UI:
No UI changes.

ACCESSIBILITY:
No accessibility implementation required for Build 2.

SEO:
No SEO implementation required for Build 2.

DEFINITION OF DONE:

- All foundation migrations exist and run successfully.
- All foundation models exist with correct relationships.
- All foundation factories exist and generate valid data.
- RolePermissionSeeder creates approved roles and permissions.
- RolePermissionSeeder is idempotent.
- AdminUserSeeder only runs when explicitly enabled in local environment.
- DatabaseSeeder calls foundation seeders safely.
- Foundation database tests pass.
- No resource-specific tables are created.
- No admin controllers or UI are created.
- No public controllers or UI are created.
- No secrets are committed.