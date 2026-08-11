# 01 — Resource Foundation

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Core Product Areas, Core Content Model, Information Architecture).
- The approved Phase 02 documentation (specifically Database Foundation, RBAC Implementation, Admin Portal Foundation, Public Site Foundation).
- The completed Slice 1 Application Foundation implementation.

## Defines

This document defines:

- The common resource architecture shared by all LabGato resource types.
- The approved resource types.
- The common resource attributes.
- The resource lifecycle states.
- The resource status enum.
- The base resource model pattern.
- The common resource relationships.
- The common resource validation rules.
- The common resource SEO patterns.
- The common resource permission patterns.
- The common resource audit logging rules.
- The shared resource components.
- The resource URL patterns.
- The resource breadcrumb patterns.

## Affects

This document affects:

- All future resource architecture documents in Phase 03.
- All future resource database migrations.
- All future resource Eloquent models.
- All future resource admin CRUD interfaces.
- All future resource public pages.
- All future resource tests.
- The Taxonomy Implementation document.
- The Search Implementation document.
- The Resource Admin CRUD Patterns document.
- The Resource Public Page Patterns document.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the common foundation that all resource types must follow.
- Prevents inconsistent resource implementations.
- Ensures all resources share common lifecycle, validation, SEO, and permission patterns.
- Provides the base architecture for vertical-slice resource implementation.

---

# 1. Purpose

This document establishes the Resource Foundation for LabGato.

LabGato supports multiple resource types, each serving a specific laboratory professional need. Despite their differences, all resources share common architectural patterns for lifecycle management, taxonomy, SEO, permissions, and auditing.

This document defines those shared patterns so that each resource type can be implemented consistently and efficiently.

---

# 2. Approved Resource Types

The following resource types are approved for LabGato.

## 2.1 Calculators

Validated laboratory calculation utilities.

Examples:

- Anion Gap Calculator
- Absolute Neutrophil Count Calculator
- Corrected Calcium Calculator
- MCV, MCH, MCHC Calculators

Architecture document:

03_Resource_Architecture/03_Calculators_Architecture.md

## 2.2 Laboratory Tools

Interactive laboratory utilities that are not strictly formula-based calculators.

Examples:

- Unit Converters
- Dilution Helpers
- Centrifugation Converters
- Buffer Preparation Tools

Architecture document:

03_Resource_Architecture/04_Laboratory_Tools_Architecture.md

## 2.3 Reference Ranges

Structured laboratory reference interval data.

Examples:

- Hemoglobin reference ranges by age and sex
- Serum electrolyte reference ranges
- Liver function test reference ranges

Architecture document:

Phase 04 — Content & Reference Architecture

## 2.4 SOP Resources

Standard Operating Procedure documents.

Examples:

- Phlebotomy SOP
- Hematology Analyzer SOP
- Quality Control SOP

Architecture document:

Phase 04 — Content & Reference Architecture

## 2.5 Result Interpretation Resources

Educational resources explaining laboratory result interpretation.

Examples:

- Interpretation of Elevated Liver Enzymes
- Interpretation of Anemia Indices
- Interpretation of Urinalysis Results

Architecture document:

Phase 04 — Content & Reference Architecture

## 2.6 Knowledge & Education Resources

General laboratory science educational content.

Examples:

- Hematology study guides
- Clinical chemistry topic overviews
- Microbiology identification guides

Architecture document:

Phase 04 — Content & Reference Architecture

---

# 3. Common Resource Attributes

All resource types share a common set of attributes.

## 3.1 Required Common Attributes

Every resource table must include:

id
- Type: unsignedBigInteger
- Constraints: primary key, auto increment

name
- Type: string
- Constraints: required
- Description: The display name of the resource.

slug
- Type: string
- Constraints: required, unique
- Description: URL-friendly identifier.

short_description
- Type: text
- Nullable: true
- Description: Brief summary for listing pages and meta descriptions.

status
- Type: string
- Constraints: required, default draft
- Description: Resource lifecycle status.

published_at
- Type: timestamp
- Nullable: true
- Description: When the resource was published.

created_by
- Type: unsignedBigInteger
- Nullable: true
- Constraints: foreign key to users.id, null on delete
- Description: The administrator who created the resource.

updated_by
- Type: unsignedBigInteger
- Nullable: true
- Constraints: foreign key to users.id, null on delete
- Description: The administrator who last updated the resource.

seo_title
- Type: string
- Nullable: true
- Description: Custom SEO title.

seo_description
- Type: text
- Nullable: true
- Description: Custom SEO meta description.

created_at
updated_at

## 3.2 Optional Common Attributes

Resource types may include additional attributes as needed.

Examples:

- description or content for long-form text.
- references for scientific citations.
- version for version-controlled resources.
- featured_image for visual resources.

## 3.3 Attribute Naming Conventions

- Use snake_case for column names.
- Use singular names for columns.
- Use _id suffix for foreign keys.
- Use _at suffix for timestamps.
- Use is_ prefix for boolean flags.

---

# 4. Resource Lifecycle States

All resources follow a common lifecycle.

## 4.1 Status Definitions

| Status | Description | Public Visibility |
|---|---|---|
| draft | Resource is being prepared. | Hidden |
| under_review | Resource is awaiting approval. | Hidden |
| published | Resource is approved and live. | Visible |
| archived | Resource is no longer active but preserved. | Hidden or marked |
| obsolete | Resource is invalid and must not be used. | Hidden |

## 4.2 Status Transitions

Allowed transitions:

- draft → under_review
- draft → published
- under_review → draft
- under_review → published
- published → archived
- published → obsolete
- archived → published
- archived → obsolete

Rules:

- Only users with publish permission can transition to published.
- Only users with edit permission can transition to draft or under_review.
- Obsolete resources must not be publicly accessible.
- Archived resources may be accessible with a clear archived indicator.

## 4.3 Status Enum

Create a PHP enum for resource status.

Location:

app/Enums/ResourceStatus.php

Cases:

- Draft
- UnderReview
- Published
- Archived
- Obsolete

Methods:

- label(): string
- color(): string
- isPublic(): bool
- canTransitionTo(ResourceStatus $target): bool

---

# 5. Base Resource Model Pattern

All resource models should follow a common pattern.

## 5.1 Model Location

Resource models should be located in:

app/Models/

Examples:

- app/Models/Calculator.php
- app/Models/LaboratoryTool.php
- app/Models/ReferenceRange.php
- app/Models/SopResource.php
- app/Models/InterpretationResource.php
- app/Models/KnowledgeResource.php

## 5.2 Common Model Traits

All resource models should use:

- HasFactory
- Common resource methods via a trait or base class.

Consider creating a trait:

app/Models/Traits/HasResourceLifecycle.php

This trait should provide:

- scopePublished
- scopeDraft
- scopeArchived
- isPublished(): bool
- isDraft(): bool
- publish(): void
- archive(): void
- markObsolete(): void

## 5.3 Common Model Relationships

All resource models should define:

- categories(): belongsToMany
- tags(): belongsToMany
- createdBy(): belongsTo User
- updatedBy(): belongsTo User

## 5.4 Common Model Casts

All resource models should cast:

- status to ResourceStatus enum.
- published_at to datetime.
- created_at to datetime.
- updated_at to datetime.

## 5.5 Common Model Scopes

All resource models should define:

- scopePublished: where status is published and published_at is not null.
- scopeSearch: basic search by name and short_description.

---

# 6. Common Resource Relationships

## 6.1 Categories

Resources belong to many categories.

Pivot table naming convention:

{resource_table}_category

Examples:

- calculator_category
- laboratory_tool_category
- reference_range_category

## 6.2 Tags

Resources belong to many tags.

Pivot table naming convention:

{resource_table}_tag

Examples:

- calculator_tag
- laboratory_tool_tag
- reference_range_tag

## 6.3 User Relationships

Resources track creation and update attribution.

- created_by: foreign key to users.id
- updated_by: foreign key to users.id

Rules:

- Set created_by on resource creation.
- Set updated_by on resource update.
- Use null on delete to preserve resource history if user is deleted.

---

# 7. Common Resource Validation Rules

All resource forms must validate common attributes.

## 7.1 Name Validation

- Required.
- String.
- Maximum 255 characters.

## 7.2 Slug Validation

- Required.
- String.
- Maximum 255 characters.
- Unique within the resource table.
- Lowercase.
- Hyphens only.
- No spaces or special characters.

## 7.3 Short Description Validation

- Optional.
- String.
- Maximum 500 characters.

## 7.4 Status Validation

- Required.
- Must be a valid ResourceStatus value.

## 7.5 SEO Title Validation

- Optional.
- String.
- Maximum 255 characters.

## 7.6 SEO Description Validation

- Optional.
- String.
- Maximum 500 characters.

---

# 8. Common Resource SEO Patterns

All public resource pages must follow common SEO patterns.

## 8.1 Meta Title

Format:

{Resource Name} | LabGato

If custom seo_title is set:

{seo_title} | LabGato

## 8.2 Meta Description

Use custom seo_description if set.

Otherwise, use short_description.

Otherwise, use a default description.

## 8.3 Canonical URL

Use the resource detail page URL.

## 8.4 Open Graph Tags

- og:title: Resource name.
- og:description: Short description.
- og:url: Resource detail URL.
- og:type: article or website.
- og:image: Resource featured image if available, otherwise default.

## 8.5 Structured Data

Resource detail pages should include JSON-LD structured data.

Recommended schema types:

- Calculators: SoftwareApplication or WebApplication.
- Laboratory Tools: SoftwareApplication or WebApplication.
- Reference Ranges: MedicalWebPage or Article.
- SOP Resources: Article or TechArticle.
- Interpretation Resources: MedicalWebPage or Article.
- Knowledge Resources: Article or LearningResource.

## 8.6 Indexing Rules

- Published resources are indexable.
- Draft resources are not accessible publicly.
- Archived resources may be accessible with noindex.
- Obsolete resources are not accessible publicly.

---

# 9. Common Resource Permission Patterns

All resource types follow a common permission structure.

## 9.1 Permission Naming

Format:

{resource_module}.{action}

Examples:

- calculators.view
- calculators.create
- calculators.edit
- calculators.publish
- calculators.delete

## 9.2 Standard Actions

Every resource module should support:

- view: View resource listings and details.
- create: Create new resources.
- edit: Edit existing resources.
- publish: Publish or unpublish resources.
- delete: Delete resources.

## 9.3 Policy Methods

Every resource policy should define:

- viewAny
- view
- create
- update
- publish
- delete

## 9.4 Admin Route Protection

Admin resource routes must be protected by:

- auth middleware
- active middleware
- permission middleware

Example:

Route::middleware('permission:calculators.view')->group(...)

---

# 10. Common Resource Audit Logging

All resource changes must be audited.

## 10.1 Audit Actions

Resource audit actions should follow this naming convention:

{resource_module}.{action}

Examples:

- calculators.created
- calculators.updated
- calculators.published
- calculators.archived
- calculators.deleted

## 10.2 Audit Payload

Resource audit logs should capture:

- user_id: The acting administrator.
- action: The action performed.
- resource_type: The resource model class.
- resource_id: The resource ID.
- changes: The changed attributes, sanitized.

## 10.3 Sensitive Field Masking

Resource audit logs must not store:

- Passwords.
- API keys.
- Secrets.
- Tokens.

---

# 11. Shared Resource Components

Common Blade components should be created for resource pages.

## 11.1 Admin Components

- Resource listing table.
- Resource form fields.
- Resource status badge.
- Resource publish button.
- Resource archive button.
- Resource delete confirmation.

## 11.2 Public Components

- Resource card for listing pages.
- Resource detail header.
- Resource breadcrumbs.
- Resource meta information.
- Resource related items.
- Resource share links.

## 11.3 Component Location

Admin components:

resources/views/components/admin/resources/

Public components:

resources/views/components/public/resources/

---

# 12. Resource URL Patterns

All resource URLs must follow common patterns.

## 12.1 Public URLs

Listing page:

/{resource_module}

Detail page:

/{resource_module}/{slug}

Examples:

- /calculators
- /calculators/anion-gap-calculator
- /tools
- /tools/unit-converter

## 12.2 Admin URLs

Listing page:

/admin/{resource_module}

Create page:

/admin/{resource_module}/create

Edit page:

/admin/{resource_module}/{id}/edit

Examples:

- /admin/calculators
- /admin/calculators/create
- /admin/calculators/1/edit

## 12.3 URL Rules

- Use lowercase.
- Use hyphens for word separation.
- Avoid trailing slashes.
- Use slugs for public URLs.
- Use IDs for admin URLs.

---

# 13. Resource Breadcrumb Patterns

All resource detail pages must include breadcrumbs.

## 13.1 Breadcrumb Structure

Home > {Resource Module} > {Resource Name}

Examples:

- Home > Calculators > Anion Gap Calculator
- Home > Tools > Unit Converter

## 13.2 Breadcrumb Implementation

Use the public breadcrumbs component created in Build 6.

Include JSON-LD BreadcrumbList structured data.

---

# 14. Resource Related Items

Resources should link to related resources where appropriate.

## 14.1 Related Item Types

- Calculators may relate to Reference Ranges.
- Reference Ranges may relate to Interpretation Resources.
- Interpretation Resources may relate to Calculators and Reference Ranges.
- Knowledge Resources may relate to any resource type.

## 14.2 Related Item Implementation

Related items may be implemented via:

- Tags.
- Categories.
- Explicit relationship tables.
- Manual selection in admin forms.

The specific implementation will be defined in each resource architecture document.

---

# Document Completion Check

## Requirements Covered

- Approved resource types defined.
- Common resource attributes defined.
- Resource lifecycle states defined.
- Resource status enum defined.
- Base resource model pattern defined.
- Common resource relationships defined.
- Common resource validation rules defined.
- Common resource SEO patterns defined.
- Common resource permission patterns defined.
- Common resource audit logging rules defined.
- Shared resource components defined.
- Resource URL patterns defined.
- Resource breadcrumb patterns defined.
- Resource related items defined.

## Decisions Established

- All resources share common lifecycle states.
- All resources use the ResourceStatus enum.
- All resources track created_by and updated_by.
- All resources support categories and tags.
- All resources follow common SEO patterns.
- All resources follow common permission patterns.
- All resource changes are audited.
- Public URLs use slugs.
- Admin URLs use IDs.
- Resource detail pages include breadcrumbs.

## Dependencies

- Approved Phase 01 Core Product Areas.
- Approved Phase 01 Core Content Model.
- Approved Phase 02 Database Foundation.
- Approved Phase 02 RBAC Implementation.
- Completed Slice 1 Application Foundation.

## Files Affected

This document creates:

03_Resource_Architecture/01_Resource_Foundation.md

It affects all future resource architecture documents and implementations.

## Open Questions

1. Should related items use explicit relationship tables or tag-based relationships?
2. Should archived resources be publicly accessible with a warning?
3. Should resources support multiple featured images?

## TBD Decisions

- TBD: Final related items implementation strategy.
- TBD: Final archived resource visibility policy.
- TBD: Final featured image strategy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE03-DOC01

## Context

LabGato requires a common resource foundation that all resource types will follow. This document defines the shared architecture for resource lifecycle, relationships, validation, SEO, permissions, and auditing.

This prompt implements the documentation outcome of the Resource Foundation document.

## Objective

Create the official LabGato Resource Foundation document inside the approved Phase 03 documentation structure.

After this prompt is completed, the repository will have a binding foundation for all resource type implementations.

## Existing Architecture

Relevant approved decisions:

- LabGato supports multiple resource types.
- All resources share common lifecycle states.
- All resources use categories and tags.
- All resources follow common SEO and permission patterns.
- Resource changes are audited.

## Files to Inspect

Inspect, if available:

- docs/
- 03_Resource_Architecture/
- Phase 01 and Phase 02 documents.
- Any existing resource models or migrations.

## Files to Create

Create:

03_Resource_Architecture/01_Resource_Foundation.md

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

No public SEO impact. This is an internal architecture document.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines common resource attributes.
- The file defines resource lifecycle states.

## Integration Requirements

This document must be used as the primary reference for all future resource architecture documents.

## Definition of Done

This implementation is complete when:

- The Resource Foundation file exists.
- The file matches the approved Phase 03 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.