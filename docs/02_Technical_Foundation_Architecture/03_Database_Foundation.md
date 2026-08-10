# 03 — Database Foundation

## Depends On

This document depends on:

- The approved Environment and Configuration document.
- The approved Core Content Model document from Phase 01.
- The approved User Roles and RBAC document from Phase 01.
- The approved Technology Stack document.
- The approved Deployment Constraints document.

## Defines

This document defines:

- The database engine and naming conventions.
- The foundation database schema for access control.
- The foundation database schema for system entities.
- The foundation database schema for taxonomy.
- The required framework tables for sessions, cache, and queues.
- The migration discipline.
- The indexing and constraint strategy.

## Affects

This document affects:

- All future database migrations.
- All future Eloquent models.
- The Authentication and Session Security document.
- The RBAC Implementation document.
- The Logging, Audit, and Error Handling document.
- The Testing Foundation document.
- All future vertical-slice resource tables.

## Implementation Impact

This document does not directly implement database migrations.

Its implementation impact is:

- Establishes the core database schema required before any feature can be built.
- Ensures that authentication, authorization, auditing, and taxonomy are structurally sound.
- Prevents future schema conflicts by defining naming and constraint rules early.
- Provides the foundation for database-driven sessions, cache, and queues.

---

# 1. Purpose

This document establishes the Database Foundation for LabGato.

Before any laboratory resource (Calculator, Tool, Reference Range, etc.) can be built, the platform requires a stable database foundation for:

- Administrator accounts.
- Role-based access control.
- System settings.
- Audit logging.
- Taxonomy (Categories and Tags).
- Framework infrastructure (Sessions, Cache, Jobs).

This document defines the schema for these foundation entities.

---

# 2. Database Engine and Conventions

## 2.1 Engine

Approved engine:

MySQL or MariaDB

Storage engine:

InnoDB

Charset:

utf8mb4

Collation:

utf8mb4_unicode_ci

## 2.2 Naming Conventions

Table names:

- Plural snake_case.
- Examples: users, roles, permissions, categories, tags, settings, audit_logs.

Column names:

- Singular snake_case.
- Examples: name, email, is_active, created_at.

Foreign keys:

- Singular snake_case with _id suffix.
- Example: user_id, role_id.

Indexes:

- Named descriptively.
- Example: users_email_unique, audit_logs_user_id_index.

## 2.3 Timestamps

All tables must include:

created_at timestamp nullable
updated_at timestamp nullable

Unless the table is immutable (e.g., audit_logs), in which case only created_at is required.

## 2.4 Soft Deletes

Soft deletes must be used for content entities where historical preservation is important.

Foundation tables such as users may use soft deletes if required by retention policy.

Framework tables (sessions, cache, jobs) must not use soft deletes.

## 2.5 JSON Columns

JSON columns may be used for:

- Permission lists.
- Setting values.
- Audit log changes.
- Resource input/output schemas in future phases.

JSON columns must not be used for data that requires relational querying or foreign key constraints.

---

# 3. Access Tables

These tables support authentication and RBAC.

## 3.1 users

Stores administrator accounts.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

name
- Type: string
- Constraints: required

email
- Type: string
- Constraints: required, unique

password
- Type: string
- Constraints: required

is_active
- Type: boolean
- Default: true

last_login_at
- Type: timestamp
- Nullable: true

remember_token
- Type: string
- Nullable: true

created_at
updated_at

Indexes:

- users_email_unique on email

## 3.2 roles

Stores role definitions.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

name
- Type: string
- Constraints: required, unique

slug
- Type: string
- Constraints: required, unique

description
- Type: text
- Nullable: true

is_system_role
- Type: boolean
- Default: false

created_at
updated_at

Indexes:

- roles_slug_unique on slug
- roles_name_unique on name

## 3.3 permissions

Stores permission definitions.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

name
- Type: string
- Constraints: required, unique

slug
- Type: string
- Constraints: required, unique

group
- Type: string
- Nullable: true

created_at
updated_at

Indexes:

- permissions_slug_unique on slug
- permissions_group_index on group

## 3.4 role_user

Pivot table linking roles to users.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

role_id
- Type: unsignedBigInteger
- Constraints: foreign key to roles.id, cascade on delete

user_id
- Type: unsignedBigInteger
- Constraints: foreign key to users.id, cascade on delete

created_at
updated_at

Indexes:

- role_user_role_id_user_id_unique on (role_id, user_id)

## 3.5 permission_role

Pivot table linking permissions to roles.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

permission_id
- Type: unsignedBigInteger
- Constraints: foreign key to permissions.id, cascade on delete

role_id
- Type: unsignedBigInteger
- Constraints: foreign key to roles.id, cascade on delete

created_at
updated_at

Indexes:

- permission_role_permission_id_role_id_unique on (permission_id, role_id)

---

# 4. System Tables

These tables support global configuration and auditing.

## 4.1 settings

Stores dynamic administrator-managed settings.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

key
- Type: string
- Constraints: required, unique

value
- Type: text
- Nullable: true

type
- Type: string
- Default: string
- Description: string, boolean, json, integer

created_at
updated_at

Indexes:

- settings_key_unique on key

## 4.2 audit_logs

Stores immutable records of administrative and security actions.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

user_id
- Type: unsignedBigInteger
- Nullable: true
- Constraints: foreign key to users.id, null on delete

action
- Type: string
- Constraints: required
- Description: login, logout, create, update, delete, publish, etc.

resource_type
- Type: string
- Nullable: true

resource_id
- Type: unsignedBigInteger
- Nullable: true

changes
- Type: json
- Nullable: true
- Description: Old and new values. Must not include sensitive data.

ip_address
- Type: string
- Nullable: true

user_agent
- Type: string
- Nullable: true

created_at

Indexes:

- audit_logs_user_id_index on user_id
- audit_logs_action_index on action
- audit_logs_resource_type_resource_id_index on (resource_type, resource_id)
- audit_logs_created_at_index on created_at

Rules:

- Audit logs must be immutable.
- Audit logs must not store passwords, API keys, or session tokens.
- Audit logs must not be deleted by normal administrative actions.

---

# 5. Taxonomy Tables

These tables support resource classification.

## 5.1 categories

Stores hierarchical categories.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

name
- Type: string
- Constraints: required

slug
- Type: string
- Constraints: required, unique

description
- Type: text
- Nullable: true

parent_id
- Type: unsignedBigInteger
- Nullable: true
- Constraints: foreign key to categories.id, null on delete

sort_order
- Type: integer
- Default: 0

is_active
- Type: boolean
- Default: true

created_at
updated_at

Indexes:

- categories_slug_unique on slug
- categories_parent_id_index on parent_id
- categories_is_active_index on is_active

## 5.2 tags

Stores flat tags.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

name
- Type: string
- Constraints: required, unique

slug
- Type: string
- Constraints: required, unique

created_at
updated_at

Indexes:

- tags_slug_unique on slug
- tags_name_unique on name

---

# 6. Framework Tables

These tables support Laravel's database-driven session, cache, and queue drivers.

## 6.1 sessions

Stores user sessions.

Columns:

id
- Type: string
- Constraints: primary key

user_id
- Type: unsignedBigInteger
- Nullable: true
- Constraints: foreign key to users.id, cascade on delete

ip_address
- Type: string
- Nullable: true

user_agent
- Type: text
- Nullable: true

payload
- Type: text
- Constraints: required

last_activity
- Type: integer
- Constraints: required

Indexes:

- sessions_user_id_index on user_id
- sessions_last_activity_index on last_activity

## 6.2 cache

Stores application cache entries.

Columns:

key
- Type: string
- Constraints: primary key

value
- Type: text
- Constraints: required

expiration
- Type: integer
- Constraints: required

## 6.3 cache_locks

Stores cache locks.

Columns:

key
- Type: string
- Constraints: primary key

owner
- Type: string
- Constraints: required

expiration
- Type: integer
- Constraints: required

## 6.4 jobs

Stores queued jobs.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

queue
- Type: string
- Constraints: required

payload
- Type: text
- Constraints: required

attempts
- Type: unsignedTinyInteger
- Constraints: required

reserved_at
- Type: unsignedInteger
- Nullable: true

available_at
- Type: unsignedInteger
- Constraints: required

created_at
- Type: unsignedInteger
- Constraints: required

Indexes:

- jobs_queue_index on queue

## 6.5 failed_jobs

Stores failed queued jobs.

Columns:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

uuid
- Type: string
- Constraints: required, unique

connection
- Type: text
- Constraints: required

queue
- Type: text
- Constraints: required

payload
- Type: text
- Constraints: required

exception
- Type: text
- Constraints: required

failed_at
- Type: timestamp
- Constraints: required, default current timestamp

Indexes:

- failed_jobs_uuid_unique on uuid

## 6.6 password_reset_tokens

Stores password reset tokens.

Columns:

email
- Type: string
- Constraints: primary key

token
- Type: string
- Constraints: required

created_at
- Type: timestamp
- Nullable: true

---

# 7. Migration Discipline

## 7.1 Migration Naming

Migrations must be named descriptively.

Examples:

- 0001_01_01_000000_create_users_table.php
- 0001_01_01_000001_create_roles_table.php
- 0001_01_01_000002_create_permissions_table.php
- 0001_01_01_000003_create_settings_table.php
- 0001_01_01_000004_create_audit_logs_table.php
- 0001_01_01_000005_create_categories_table.php
- 0001_01_01_000006_create_tags_table.php

## 7.2 Migration Order

Migrations must be ordered by dependency.

1. Framework tables (sessions, cache, jobs).
2. Access tables (users, roles, permissions).
3. System tables (settings, audit_logs).
4. Taxonomy tables (categories, tags).
5. Resource tables (future phases).

## 7.3 Rollback

All migrations must include a safe down() method.

Destructive operations must be reviewed carefully.

## 7.4 Seeders

Seeders must be created for:

- Default roles.
- Default permissions.
- Default Super Administrator account (local development only).
- Default categories and tags (optional).

Production seeders must not create real user accounts without explicit approval.

---

# Document Completion Check

## Requirements Covered

- Database engine and conventions defined.
- Access tables defined.
- System tables defined.
- Taxonomy tables defined.
- Framework tables defined.
- Migration discipline defined.
- Indexing and constraint strategy defined.

## Decisions Established

- MySQL/MariaDB with InnoDB is approved.
- Database-driven sessions, cache, and queues are approved.
- Audit logs are immutable.
- Settings use key-value storage.
- Categories support hierarchy.
- Tags are flat.
- Soft deletes are reserved for content entities.

## Dependencies

- Approved Environment and Configuration.
- Approved Core Content Model.
- Approved User Roles and RBAC.
- Approved Technology Stack.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/03_Database_Foundation.md

It directly dictates the structure of all foundation migrations and models.

## Open Questions

1. Should users use soft deletes?
2. Should audit logs be partitioned by date for performance?
3. Should settings values be encrypted?

## TBD Decisions

- TBD: Final decision on user soft deletes.
- TBD: Final decision on audit log retention policy.
- TBD: Final decision on setting encryption.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE02-DOC03

## Context

LabGato requires a stable database foundation before any feature can be built. This document defines the core schema for access control, system entities, taxonomy, and framework infrastructure.

This prompt implements the documentation outcome of the Database Foundation document.

## Objective

Create the official LabGato Database Foundation document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have a binding schema definition for the foundation tables.

## Existing Architecture

Relevant approved decisions:

- LabGato uses MySQL/MariaDB.
- Sessions, cache, and queues use database drivers.
- Audit logs are immutable.
- RBAC uses roles and permissions.
- Taxonomy uses categories and tags.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 and 02.
- Any existing migrations.

## Files to Create

Create:

02_Technical_Foundation_Architecture/03_Database_Foundation.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines the schema.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that audit log immutability is documented.

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
- The file clearly defines all foundation tables.
- The file defines migration discipline.

## Integration Requirements

This document must be used as the primary reference when writing foundation migrations and models.

## Definition of Done

This implementation is complete when:

- The Database Foundation file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.