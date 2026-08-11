# 02 — Taxonomy Implementation

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Core Content Model, Information Architecture).
- The approved Phase 02 documentation (specifically Database Foundation, Admin Portal Foundation).
- The approved Phase 03 Resource Foundation document.
- The completed Slice 1 Application Foundation implementation.

## Defines

This document defines:

- The taxonomy strategy for LabGato resources.
- The Category model architecture and hierarchical rules.
- The Tag model architecture and flat labeling rules.
- The explicit pivot table strategy for resource relationships.
- The administrative management patterns for taxonomy.
- The resource assignment and detachment rules.
- The public filtering and navigation integration.
- The validation rules for taxonomy inputs.
- The SEO and URL implications of taxonomy.

## Affects

This document affects:

- All future resource architecture documents (Calculators, Tools, etc.).
- All future resource database migrations (pivot tables).
- All future resource Eloquent models (relationships).
- All future resource admin CRUD forms (assignment UI).
- All future public listing pages (filtering UI).
- The Search Implementation document.
- The Resource Admin CRUD Patterns document.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes how resources are classified and discovered.
- Prevents inconsistent taxonomy assignments across different resource types.
- Ensures database queries for filtering and searching remain efficient on shared hosting.
- Provides the blueprint for the Taxonomy Admin Module and resource assignment interfaces.

---

# 1. Purpose

This document establishes the Taxonomy Implementation architecture for LabGato.

Taxonomy (Categories and Tags) is the primary mechanism for organizing, filtering, and discovering laboratory resources. A consistent taxonomy strategy ensures that a Hematology Calculator and a Hematology SOP can be logically grouped, while still maintaining strict database integrity and query performance on the Hostinger shared hosting environment.

---

# 2. Taxonomy Strategy

## 2.1 Explicit Pivot Tables vs. Polymorphic Relations

LabGato will use **explicit pivot tables** for resource taxonomy relationships, rather than Laravel's polymorphic `morphToMany` relationships.

Reasons:
- Explicit foreign keys enforce stricter database integrity.
- Querying and joining explicit tables is generally faster and simpler on MySQL/MariaDB shared hosting.
- It prevents accidental cross-contamination of taxonomy data between unrelated entities.
- It makes database administration and manual debugging easier.

## 2.2 Pivot Table Naming Convention

As defined in the Resource Foundation, pivot tables must follow this pattern:

Categories: `{resource_table}_category`
Tags: `{resource_table}_tag`

Examples:
- `calculator_category`
- `calculator_tag`
- `laboratory_tool_category`
- `laboratory_tool_tag`

---

# 3. Category Architecture

Categories provide a hierarchical, structured classification for resources.

## 3.1 Category Model Rules

- Categories support a single-level or multi-level hierarchy via `parent_id`.
- For the MVP, a maximum depth of 2 levels (Parent > Child) is recommended to prevent overly complex navigation.
- Categories must have a unique `slug`.
- Categories must have an `is_active` boolean flag. Inactive categories must not be assignable to resources and must not appear in public filters.
- Categories must have a `sort_order` integer for manual ordering in lists.

## 3.2 Category Relationships

The `Category` model must define:
- `parent()`: belongsTo Category.
- `children()`: hasMany Category.
- `calculators()`: belongsToMany Calculator (via `calculator_category`).
- `laboratoryTools()`: belongsToMany LaboratoryTool (via `laboratory_tool_category`).
- *(Future resource relationships will be added as those resource types are implemented).*

## 3.3 Category Validation Rules

- `name`: Required, string, max 255 characters.
- `slug`: Required, string, max 255 characters, unique, alpha-dash (hyphens allowed).
- `parent_id`: Optional, exists in categories table, cannot be self-referential.
- `description`: Optional, string, max 1000 characters.

---

# 4. Tag Architecture

Tags provide a flat, unstructured labeling system for cross-cutting topics.

## 4.1 Tag Model Rules

- Tags do not support hierarchy.
- Tags must have a unique `slug` and `name`.
- Tags are inherently active; there is no `is_active` flag. If a tag is no longer needed, it should be detached from resources and deleted.
- Tag names should be normalized to lowercase before saving to prevent duplicates (e.g., "Hematology" and "hematology").

## 4.2 Tag Relationships

The `Tag` model must define:
- `calculators()`: belongsToMany Calculator (via `calculator_tag`).
- `laboratoryTools()`: belongsToMany LaboratoryTool (via `laboratory_tool_tag`).
- *(Future resource relationships will be added as those resource types are implemented).*

## 4.3 Tag Validation Rules

- `name`: Required, string, max 255 characters, unique (case-insensitive).
- `slug`: Required, string, max 255 characters, unique, alpha-dash.

---

# 5. Administrative Management

Taxonomy must be managed through the Admin Portal by users with the `taxonomy.manage` permission.

## 5.1 Category Admin Interface

- **Listing Page**: Display categories in a hierarchical tree or indented list. Show name, slug, resource counts, and active status.
- **Create/Edit Form**: Allow setting name, slug (auto-generated from name if left blank), parent category, description, sort order, and active status.
- **Deletion**: Prevent deletion of a category if it has active child categories or if it is currently assigned to published resources (require manual detachment first, or cascade detach with a warning).

## 5.2 Tag Admin Interface

- **Listing Page**: Display tags in a paginated table or cloud view. Show name, slug, and total usage count across all resource types.
- **Create/Edit Form**: Allow setting name and slug.
- **Deletion**: Allow deletion. Detach from all resources automatically upon deletion.

---

# 6. Resource Assignment

When creating or editing a resource (e.g., a Calculator), the administrator must be able to assign categories and tags.

## 6.1 Category Assignment UI

- Use a multi-select dropdown, a checkbox tree, or a nested checkbox list.
- Only show active categories.
- Limit the number of primary categories a resource can belong to if necessary (e.g., max 3 categories per resource to prevent SEO dilution).

## 6.2 Tag Assignment UI

- Use a tag input component (e.g., Tom Select, Choices.js, or a custom Alpine.js component).
- Allow selecting existing tags from a dropdown.
- Allow creating new tags on the fly (if the user has `taxonomy.manage` permission). If the user lacks permission, they can only select existing tags.
- Normalize new tag input to lowercase and generate the slug automatically.

## 6.3 Detachment Rules

- Removing a category or tag from a resource must update the pivot table immediately.
- Changes to taxonomy assignments must trigger an audit log entry for the resource (e.g., `calculators.updated` with changes reflecting the taxonomy shift).

---

# 7. Public Filtering and Navigation

Taxonomy drives the discovery experience on the public site.

## 7.1 Public Category Navigation

- Resource listing pages (e.g., `/calculators`) must include a sidebar or top-bar filter for Categories.
- Selecting a category filters the list to show only published resources in that category (and optionally its children).
- URL structure for filtering: `/calculators?category=hematology` or `/calculators/hematology` (Route parameters are preferred for SEO, but query strings are acceptable for MVP filtering). Let's use query strings for MVP filtering to keep routes simple: `/calculators?category=hematology`.

## 7.2 Public Tag Display

- Tags should be displayed as clickable badges on resource cards and resource detail pages.
- Clicking a tag navigates to a global search or a filtered listing: `/search?tag=anemia` or `/calculators?tag=anemia`.
- Tags should not be used as primary navigation items in the main header.

## 7.3 Empty States

- If a category or tag filter results in zero published resources, display a clear empty state: "No calculators found for this category."
- Do not display categories or tags in the public filter UI if they have zero published resources associated with them.

---

# 8. SEO and URL Implications

## 8.1 Category Pages

If dedicated category landing pages are created in the future (e.g., `/categories/hematology`), they must have unique meta titles and descriptions.

For the MVP, filtering via query strings on the main resource listing page (e.g., `/calculators?category=hematology`) is sufficient. These filtered URLs should either use canonical tags pointing to the base `/calculators` URL, or be marked `noindex` to prevent duplicate content penalties.

## 8.2 Breadcrumbs

When a user navigates via a category, the breadcrumb should reflect the hierarchy if appropriate:
`Home > Calculators > Hematology > Anion Gap Calculator`

---

# 9. Testing Requirements

## 9.1 Unit Tests

- Test Category parent/child relationships.
- Test Tag normalization (e.g., saving "Hematology" results in "hematology").
- Test prevention of self-referential category parents.

## 9.2 Feature Tests (Admin)

- Test that a user with `taxonomy.manage` can create, edit, and delete categories and tags.
- Test that a user without `taxonomy.manage` cannot access taxonomy management.
- Test that inactive categories cannot be assigned to resources.
- Test that deleting a tag detaches it from all associated resources.

## 9.3 Feature Tests (Public)

- Test that filtering a resource listing by a valid category returns only matching published resources.
- Test that filtering by an empty category returns the correct empty state.
- Test that inactive categories do not appear in the public filter UI.

---

# Document Completion Check

## Requirements Covered

- Taxonomy strategy defined (explicit pivot tables).
- Category architecture and hierarchy rules defined.
- Tag architecture and flat labeling rules defined.
- Administrative management patterns defined.
- Resource assignment and detachment rules defined.
- Public filtering and navigation integration defined.
- SEO and URL implications defined.
- Testing requirements defined.

## Decisions Established

- LabGato uses explicit pivot tables for taxonomy, not polymorphic relations.
- Categories support hierarchy; Tags are flat.
- Tag names are normalized to lowercase.
- Inactive categories are hidden from public filters and assignment UIs.
- Filtered listing pages will use query strings for MVP to preserve route simplicity.
- Canonical tags or noindex rules must be applied to filtered URLs to protect SEO.

## Dependencies

- Approved Phase 03 Resource Foundation.
- Approved Phase 02 Database Foundation.
- Approved Phase 02 RBAC Implementation.

## Files Affected

This document creates:

03_Resource_Architecture/02_Taxonomy_Implementation.md

It dictates the implementation of taxonomy models, pivot migrations, admin CRUD, and public filtering logic.

## Open Questions

1. Should users without `taxonomy.manage` permission be allowed to create new tags on the fly when editing a resource, or strictly limited to existing tags?
2. Should category pages eventually become dedicated indexable routes (e.g., `/calculators/hematology`) instead of query string filters?

## TBD Decisions

- TBD: Final decision on tag creation permissions for Content Managers.
- TBD: Future dedicated category route strategy for advanced SEO.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE03-DOC02

## Context

LabGato requires a robust taxonomy system to organize and filter laboratory resources. This document defines the architecture for Categories and Tags, including explicit pivot tables, hierarchical rules, admin management, and public filtering.

This prompt implements the documentation outcome of the Taxonomy Implementation document.

## Objective

Create the official LabGato Taxonomy Implementation document inside the approved Phase 03 documentation structure.

After this prompt is completed, the repository will have a binding specification for how resources are classified and discovered.

## Existing Architecture

Relevant approved decisions:

- Explicit pivot tables are used for resource taxonomy.
- Categories support hierarchy; Tags are flat.
- Taxonomy management requires `taxonomy.manage` permission.
- Public filtering uses query strings for MVP.

## Files to Inspect

Inspect, if available:

- docs/
- 03_Resource_Architecture/01_Resource_Foundation.md
- app/Models/Category.php
- app/Models/Tag.php
- database/migrations/ (categories and tags tables)

## Files to Create

Create:

03_Resource_Architecture/02_Taxonomy_Implementation.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines the taxonomy architecture.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI requirements for taxonomy management and filtering.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

This document defines the SEO strategy for filtered pages (canonical/noindex rules).

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the explicit pivot table strategy.
- The file defines category hierarchy and tag normalization rules.

## Integration Requirements

This document must be used as the primary reference when building resource pivot migrations, taxonomy admin CRUD, and public listing filters.

## Definition of Done

This implementation is complete when:

- The Taxonomy Implementation file exists.
- The file matches the approved Phase 03 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.