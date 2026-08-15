# 11 — Core Content Model

## Depends On

This document depends on:

- The approved LABGOTO Master Software Project directive.
- The approved Project Charter.
- The approved Product Scope and Non-Goals document.
- The approved Target Users and Journeys document.
- The approved Core Product Areas document.
- The approved Information Architecture document.
- The approved Technology Stack document.

## Defines

This document defines:

- The high-level entities required to support LABGOTO.
- The primary attributes for each entity.
- The relationships between entities.
- The content lifecycle states.
- The taxonomy structure.
- The system and audit entities.
- The boundaries between content models and future database schema.

## Affects

This document affects:

- The Database Architecture (future phases).
- The API Architecture (future phases).
- The Admin Portal Specification (future phases).
- The Frontend Component Architecture (future phases).
- The Search Architecture (future phases).
- The SEO metadata strategy.
- The validation rules for administrative forms.

## Implementation Impact

This document does not directly implement application code or database migrations.

Its implementation impact is:

- Establishes the conceptual data model for the platform.
- Ensures that all core product areas have a defined place in the system.
- Prevents data duplication and inconsistent relationships.
- Guides the creation of Eloquent models, migrations, and admin CRUD interfaces.

---

# 1. Purpose

This document establishes the Core Content Model for LABGOTO.

The Content Model defines the "nouns" of the system: the things that administrators create, manage, and publish, and the things that users consume.

A strong Content Model ensures that calculators, reference ranges, SOPs, and knowledge articles are structured consistently, related correctly, and managed securely.

---

# 2. Core Entity Overview

The LABGOTO Content Model is divided into four groups:

1.  **Access Entities:** Users, Roles, and Permissions.
2.  **Taxonomy Entities:** Categories and Tags.
3.  **Resource Entities:** The core laboratory content types.
4.  **System Entities:** Settings, Audit Logs, and Analytics events.

---

# 3. Access Entities

These entities manage who can access the Admin Portal and what they can do.

## 3.1 User

Represents an authenticated administrator.

**Key Attributes:**
- `id`
- `name`
- `email` (unique)
- `password` (hashed)
- `is_active` (boolean)
- `last_login_at`
- `created_at`, `updated_at`

**Relationships:**
- Belongs to many Roles.
- Has many Audit Logs (as the actor).

**Rules:**
- Users must be authenticated to access the Admin Portal.
- Deactivated users must be unable to log in.
- Passwords must be hashed securely.

## 3.2 Role

Represents a collection of permissions.

**Key Attributes:**
- `id`
- `name` (e.g., Super Administrator, Content Manager)
- `slug`
- `description`
- `is_system_role` (boolean, prevents deletion of critical roles)
- `created_at`, `updated_at`

**Relationships:**
- Belongs to many Permissions.
- Belongs to many Users.

**Rules:**
- Roles must follow the principle of least privilege.
- System roles cannot be deleted.

## 3.3 Permission

Represents a specific allowed action.

**Key Attributes:**
- `id`
- `name` (e.g., "View Calculators", "Publish SOPs")
- `slug` (e.g., `calculators.view`, `sops.publish`)
- `group` (e.g., Calculators, SOPs, Users)
- `created_at`, `updated_at`

**Relationships:**
- Belongs to many Roles.

**Rules:**
- Permissions must be granular.
- Permissions must be enforced server-side.

---

# 4. Taxonomy Entities

These entities organize resources into logical groups.

## 4.1 Category

Represents a hierarchical classification.

**Key Attributes:**
- `id`
- `name` (e.g., Hematology, Chemistry, Phlebotomy)
- `slug`
- `description`
- `parent_id` (nullable, for subcategories)
- `sort_order`
- `is_active`
- `created_at`, `updated_at`

**Relationships:**
- Has many Resources (polymorphic or specific relationships).
- Has many child Categories.
- Belongs to one parent Category.

**Rules:**
- Categories should be reusable across resource types where appropriate.
- Slugs must be unique.

## 4.2 Tag

Represents a flat keyword or label.

**Key Attributes:**
- `id`
- `name`
- `slug`
- `created_at`, `updated_at`

**Relationships:**
- Belongs to many Resources (polymorphic).

**Rules:**
- Tags should be used for cross-cutting topics.
- Tags should not be hierarchical.

---

# 5. Resource Entities

These entities represent the core laboratory content.

All Resource Entities share common lifecycle attributes:
- `status` (Draft, Under Review, Published, Archived, Obsolete)
- `published_at` (nullable)
- `created_by` (User ID)
- `updated_by` (User ID)
- `seo_title` (nullable)
- `seo_description` (nullable)
- `slug` (unique)

## 5.1 LaboratoryTool

Represents an interactive utility.

**Key Attributes:**
- `id`
- `name`
- `slug`
- `short_description`
- `description`
- `instructions`
- `input_schema` (JSON, defines inputs)
- `output_schema` (JSON, defines outputs)
- `logic_type` (Enum: Formula, Conversion, Lookup)
- `logic_payload` (JSON or encrypted string, secure logic definition)
- `references` (text)
- `status`
- `published_at`

**Relationships:**
- Belongs to many Categories.
- Belongs to many Tags.

**Rules:**
- Must not execute arbitrary code.
- Logic must be defined in a safe, structured format.

## 5.2 Calculator

Represents a validated laboratory calculation.

**Key Attributes:**
- `id`
- `name`
- `slug`
- `short_description`
- `formula_display` (string, e.g., "MCH = Hb / RBC")
- `input_parameters` (JSON, defines inputs, units, limits)
- `output_parameters` (JSON, defines outputs, units, rounding)
- `reference_ranges` (JSON or related ReferenceRange IDs)
- `interpretation_notes` (text)
- `references` (text)
- `status`
- `published_at`

**Relationships:**
- Belongs to many Categories.
- Belongs to many Tags.

**Rules:**
- Must have defined expected outputs for testing.
- Must specify rounding rules.

## 5.3 ReferenceRange

Represents a structured laboratory reference interval.

**Key Attributes:**
- `id`
- `analyte_name` (e.g., Hemoglobin)
- `slug`
- `specimen_type` (e.g., Whole Blood)
- `methodology` (nullable)
- `population_group` (e.g., Adult, Pediatric)
- `age_min` (nullable)
- `age_max` (nullable)
- `sex` (Enum: Any, Male, Female)
- `unit` (e.g., g/dL)
- `lower_limit` (decimal)
- `upper_limit` (decimal)
- `critical_low` (decimal, nullable)
- `critical_high` (decimal, nullable)
- `conversion_factor` (decimal, nullable)
- `source_citation` (text)
- `effective_date` (date)
- `status`
- `published_at`

**Relationships:**
- Belongs to many Categories.
- Has many related InterpretationResources.

**Rules:**
- Must support multiple variations for the same analyte.
- Must include source citation.

## 5.4 SOPResource

Represents a Standard Operating Procedure.

**Key Attributes:**
- `id`
- `title`
- `slug`
- `document_code` (e.g., SOP-HEM-001)
- `version` (string)
- `author_name`
- `approved_by`
- `effective_date` (date)
- `next_review_date` (date)
- `scope` (text)
- `procedure_content` (longtext)
- `safety_information` (text)
- `quality_control` (text)
- `attachments` (JSON, file paths)
- `status`
- `published_at`

**Relationships:**
- Belongs to many Categories.
- Belongs to many Tags.

**Rules:**
- Must preserve version history.
- Must clearly display status and review dates.

## 5.5 InterpretationResource

Represents educational result interpretation.

**Key Attributes:**
- `id`
- `title`
- `slug`
- `summary`
- `content` (longtext)
- `disclaimer` (text)
- `related_analytes` (JSON or related ReferenceRange IDs)
- `references` (text)
- `status`
- `published_at`

**Relationships:**
- Belongs to many Categories.
- Belongs to many Tags.
- Belongs to many ReferenceRanges.

**Rules:**
- Must include a clear educational disclaimer.
- Must not provide diagnostic advice.

## 5.6 KnowledgeResource

Represents general educational content.

**Key Attributes:**
- `id`
- `title`
- `slug`
- `summary`
- `content` (longtext)
- `author_name`
- `estimated_reading_time` (integer, minutes)
- `featured_image` (string, path)
- `status`
- `published_at`

**Relationships:**
- Belongs to many Categories.
- Belongs to many Tags.

**Rules:**
- Must support rich text formatting.
- Must support related resources.

---

# 6. System Entities

These entities support platform operation and security.

## 6.1 AuditLog

Represents a record of administrative or security actions.

**Key Attributes:**
- `id`
- `user_id` (nullable, actor)
- `action` (e.g., `login`, `update`, `delete`, `publish`)
- `resource_type` (e.g., `Calculator`)
- `resource_id`
- `changes` (JSON, old/new values)
- `ip_address`
- `user_agent`
- `created_at`

**Relationships:**
- Belongs to one User.

**Rules:**
- Audit logs must be immutable.
- Audit logs must not store sensitive data (e.g., passwords).

## 6.2 Setting

Represents global platform configuration.

**Key Attributes:**
- `id`
- `key` (unique, e.g., `site_name`, `default_meta_title`)
- `value` (text or JSON)
- `type` (string, boolean, json)
- `created_at`, `updated_at`

**Rules:**
- Settings must be cached for performance.
- Sensitive settings must be encrypted or stored in environment variables.

---

# 7. Entity Relationships Summary

The following relationships must be supported:

- Users belong to many Roles.
- Roles belong to many Permissions.
- Resources belong to many Categories.
- Resources belong to many Tags.
- InterpretationResources relate to ReferenceRanges.
- AuditLogs belong to Users.
- Categories can have parent/child hierarchies.

Polymorphic relationships may be used for Tags and Categories to reduce duplication, but explicit relationships may be preferred for performance and clarity. This decision will be finalized in the Database Architecture phase.

---

# 8. Content Lifecycle States

All Resource Entities must support the following states:

| State | Description | Public Visibility |
|---|---|---|
| Draft | Content is being prepared. | Hidden |
| Under Review | Content is awaiting approval. | Hidden |
| Published | Content is approved and live. | Visible |
| Archived | Content is no longer active but preserved. | Hidden or marked |
| Obsolete | Content is invalid and must not be used. | Hidden |

Transitions between states must be authorized and logged.

---

# Document Completion Check

## Requirements Covered

- Access entities defined.
- Taxonomy entities defined.
- Resource entities defined.
- System entities defined.
- Entity relationships defined.
- Content lifecycle states defined.
- Common attributes defined.

## Decisions Established

- All resources share common lifecycle attributes.
- Categories support hierarchy; Tags are flat.
- Audit logs are immutable.
- Settings are key-value based.
- Content lifecycle states are standardized.
- Polymorphic vs. explicit taxonomy relationships are deferred to Database Architecture.

## Dependencies

- Approved LABGOTO Master Directive.
- Approved Core Product Areas.
- Approved Information Architecture.
- Approved Technology Stack.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/11_Core_Content_Model.md

It directly dictates the structure of future Eloquent models and database migrations.

## Open Questions

1. Should Categories and Tags use polymorphic relationships or explicit pivot tables per resource type?
2. How should version history be stored for SOPs and Reference Ranges?
3. Should Settings be stored in the database or environment variables?
4. What is the exact JSON schema for Calculator input/output parameters?

## TBD Decisions

- TBD: Final taxonomy relationship strategy.
- TBD: Final versioning strategy.
- TBD: Final settings storage strategy.
- TBD: Final JSON schema for interactive tools.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE01-DOC11

## Context

LABGOTO requires a structured Content Model to ensure that all laboratory resources, users, and system entities are organized consistently. This document defines the high-level entities, attributes, and relationships that will guide the database architecture.

This prompt implements the documentation outcome of the Core Content Model document.

## Objective

Create the official LABGOTO Core Content Model document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository will have a binding conceptual data model for the platform.

## Existing Architecture

Relevant approved decisions:

- LABGOTO is a professional laboratory toolkit.
- Resources must support lifecycle states.
- Audit logs must be immutable.
- Taxonomy must support categories and tags.
- Shared hosting constraints apply.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- Documents 01 through 10.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/11_Core_Content_Model.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines the conceptual model.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that sensitive data handling rules are documented.

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
- The file clearly defines all core entities.
- The file defines the content lifecycle states.

## Integration Requirements

This document must be used as the primary reference when defining the Database Architecture and Eloquent models.

## Definition of Done

This implementation is complete when:

- The Core Content Model file exists.
- The file matches the approved Phase 01 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.