# 07 — Resource Public Page Patterns

## Depends On

This document depends on:

- The approved Phase 02 documentation (specifically Public Site Foundation, SEO and Accessibility Foundation).
- The approved Phase 03 Resource Foundation document.
- The approved Phase 03 Taxonomy Implementation document.
- The approved Phase 03 Calculators Architecture document.
- The approved Phase 03 Laboratory Tools Architecture document.
- The approved Phase 03 Search Implementation document.

## Defines

This document defines:

- The public listing page pattern for resource modules.
- The public detail page pattern for individual resources.
- The resource card component pattern.
- The filtering and sorting patterns for public listings.
- The breadcrumbs and navigation patterns.
- The SEO and structured data patterns for resource pages.
- The empty, loading, and error state patterns.
- The related resources pattern.
- The testing requirements for public resource pages.

## Affects

This document affects:

- All future resource public controllers.
- All future resource public Blade views.
- All future resource public Livewire components.
- The Search Implementation integration.
- The Resource Testing Strategy document.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes consistent, reusable public page patterns for all resource types.
- Prevents disconnected or inconsistent public interfaces.
- Ensures all resource pages are SEO-friendly, accessible, and performant.
- Provides the blueprint for building resource public modules efficiently.

---

# 1. Purpose

This document establishes the Resource Public Page Patterns for LabGato.

Every resource type (Calculators, Tools, Reference Ranges, etc.) requires public-facing pages for discovery and interaction. To ensure consistency, accessibility, and SEO performance, all resource public pages must follow the same patterns.

This document defines those patterns so that each resource vertical slice can be implemented rapidly and consistently.

---

# 2. Public Listing Page Pattern

The public listing page displays a collection of published resources for a specific module.

## 2.1 Route Structure

Base path: `/{resource_module}`

Examples:

- `/calculators`
- `/tools`

Route name:

`public.{resource_module}.index`

## 2.2 Page Layout

- Page header with module title and description.
- Search/filter bar.
- Resource grid or list.
- Pagination controls.
- Empty state if no resources exist.

## 2.3 Resource Grid

- Display resources as cards in a responsive grid.
- Desktop: 3 or 4 columns.
- Tablet: 2 columns.
- Mobile: 1 column.

## 2.4 Pagination

- Use Laravel's built-in pagination.
- Default: 12, 15, or 24 items per page.
- Preserve query parameters across pagination links.

## 2.5 Empty State

If no published resources exist for the module:
- Display a clear empty state message.
- Do not display fake resources.
- Provide a link to the home page or search.

---

# 3. Public Detail Page Pattern

The public detail page displays a single published resource.

## 3.1 Route Structure

Path: `/{resource_module}/{slug}`

Examples:

- `/calculators/anion-gap-calculator`
- `/tools/unit-converter`

Route name:

`public.{resource_module}.show`

## 3.2 Page Layout

- Breadcrumbs.
- Resource header (title, type badge, meta info).
- Resource content or interactive area.
- Taxonomy display (categories, tags).
- Interpretation notes or instructions.
- References.
- Related resources.
- SEO meta tags and structured data.

## 3.3 Resource Header

- Display resource name in an `<h1>` tag.
- Display resource type badge (e.g., "Calculator", "Tool").
- Display short description.
- Display categories and tags as clickable badges.

## 3.4 Interactive Area

For Calculators and Tools:
- Display the interactive form.
- Display the result area.
- Use Livewire or Alpine.js for dynamic interaction.

For non-interactive resources (e.g., Reference Ranges, SOPs):
- Display the content in a readable format.
- Use tables, lists, or rich text as appropriate.

## 3.5 References

- Display scientific citations or technical references.
- Use a numbered list or bibliography format.
- Include links to external sources where appropriate.

---

# 4. Resource Card Component Pattern

Resource cards are used in listing pages, search results, and related resources sections.

## 4.1 Card Structure

Each resource card must display:

- Resource name (linked to detail page).
- Resource type badge.
- Short description (truncated if necessary).
- Categories or tags (optional).
- "View" or "Use" call-to-action.

## 4.2 Card Styling

- Use a clean, professional card design.
- Include hover effects for interactivity.
- Ensure touch targets are large enough for mobile.
- Use consistent spacing and typography.

## 4.3 Component Location

Resource card component:

resources/views/components/public/resources/card.blade.php

---

# 5. Filtering & Sorting Pattern

Public listing pages must support filtering and sorting.

## 5.1 Filter Controls

- Category filter (dropdown or sidebar).
- Tag filter (optional).
- Search input (optional, can link to global search).

## 5.2 Sorting Options

- Default: Alphabetical by name.
- Optional: Most recent, Most popular (if analytics are available).

## 5.3 Query Parameters

Use query parameters for filtering:

- `?category=hematology`
- `?tag=anemia`
- `?sort=latest`

## 5.4 SEO Considerations

- Filtered pages should use canonical tags pointing to the base listing URL.
- Alternatively, mark filtered pages as `noindex` to prevent duplicate content.

---

# 6. Breadcrumbs & Navigation

## 6.1 Breadcrumb Structure

Resource detail pages must include breadcrumbs:

Home > {Resource Module} > {Resource Name}

Examples:

- Home > Calculators > Anion Gap Calculator
- Home > Tools > Unit Converter

## 6.2 Breadcrumb Implementation

Use the public breadcrumbs component created in Build 6.

Include JSON-LD BreadcrumbList structured data.

## 6.3 Navigation Links

- Provide a link back to the module listing page.
- Provide links to related resources.
- Provide links to categories and tags.

---

# 7. SEO & Structured Data Patterns

## 7.1 Meta Tags

Resource detail pages must include:

- Title tag: `{Resource Name} | LabGato`
- Meta description: Use `seo_description` or `short_description`.
- Canonical URL: Resource detail page URL.
- Open Graph tags.

## 7.2 Structured Data

Resource detail pages should include JSON-LD structured data.

Recommended schema types:

- Calculators: `SoftwareApplication` or `WebApplication`.
- Laboratory Tools: `SoftwareApplication` or `WebApplication`.
- Reference Ranges: `MedicalWebPage` or `Article`.
- SOP Resources: `Article` or `TechArticle`.
- Interpretation Resources: `MedicalWebPage` or `Article`.
- Knowledge Resources: `Article` or `LearningResource`.

## 7.3 Indexing Rules

- Published resources are indexable.
- Draft, archived, and obsolete resources are not publicly accessible.
- Filtered listing pages should use canonical tags or `noindex`.

---

# 8. Empty, Loading, Error States

## 8.1 Empty States

For listing pages:
- Display a clear message if no resources exist.
- Provide a call-to-action to browse other modules or search.

For interactive resources:
- Display a placeholder result area before calculation.

## 8.2 Loading States

For interactive resources:
- Display a loading spinner or skeleton during calculation.
- Disable the submit button during loading.

## 8.3 Error States

For interactive resources:
- Display validation errors inline.
- Display a safe error message if the calculation engine fails.
- Do not expose stack traces.

For listing pages:
- Display a safe error message if the query fails.
- Log the error for administrators.

---

# 9. Related Resources Pattern

Resource detail pages should display related resources to improve navigation and SEO.

## 9.1 Related Item Sources

Related resources may be determined by:

- Shared categories.
- Shared tags.
- Explicit relationships (if implemented).
- Manual selection in admin forms (future).

## 9.2 Related Item Display

- Display related resources as cards or a list.
- Limit to 3-6 related items.
- Use the resource card component.

## 9.3 Empty Related Items

If no related resources exist:
- Do not display the related resources section.
- Do not display fake related items.

---

# 10. Testing Requirements

## 10.1 Feature Tests

For each resource public module, test:

- Listing page renders successfully.
- Listing page displays only published resources.
- Detail page renders for a published resource.
- Detail page returns 404 for unpublished resources.
- Filtering by category works.
- Filtering by tag works.
- Pagination works.
- Breadcrumbs are displayed correctly.

## 10.2 SEO Tests

- Test that meta tags are rendered correctly.
- Test that canonical URLs are correct.
- Test that structured data is valid.

## 10.3 Accessibility Tests

- Test that resource cards are keyboard accessible.
- Test that interactive forms are keyboard accessible.
- Test that error messages are accessible.

---

# Document Completion Check

## Requirements Covered

- Public listing page pattern defined.
- Public detail page pattern defined.
- Resource card component pattern defined.
- Filtering and sorting patterns defined.
- Breadcrumbs and navigation patterns defined.
- SEO and structured data patterns defined.
- Empty, loading, and error state patterns defined.
- Related resources pattern defined.
- Testing requirements defined.

## Decisions Established

- Public listing pages use responsive grids.
- Public detail pages include breadcrumbs, taxonomy, and related resources.
- Resource cards are reusable components.
- Filtering uses query parameters.
- Filtered pages use canonical tags or noindex.
- Published resources are indexable.
- Related resources are determined by shared taxonomy.

## Dependencies

- Approved Phase 02 Public Site Foundation.
- Approved Phase 03 Resource Foundation.
- Approved Phase 03 Taxonomy Implementation.
- Approved Phase 03 Search Implementation.

## Files Affected

This document creates:

03_Resource_Architecture/07_Resource_Public_Page_Patterns.md

It dictates the implementation of all future resource public controllers, views, and components.

## Open Questions

1. Should related resources use explicit relationship tables or taxonomy-based matching?
2. Should filtered listing pages be indexable with unique content in a future phase?

## TBD Decisions

- TBD: Final related resources matching strategy.
- TBD: Future indexable category page strategy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE03-DOC07

## Context

LabGato requires consistent, reusable public page patterns for all resource modules. This document defines the standard patterns for listing pages, detail pages, resource cards, filtering, and SEO.

This prompt implements the documentation outcome of the Resource Public Page Patterns document.

## Objective

Create the official LabGato Resource Public Page Patterns document inside the approved Phase 03 documentation structure.

After this prompt is completed, the repository will have a binding specification for resource public pages.

## Existing Architecture

Relevant approved decisions:

- Public listing pages use responsive grids.
- Public detail pages include breadcrumbs and taxonomy.
- Resource cards are reusable components.
- Filtering uses query parameters.
- Published resources are indexable.

## Files to Inspect

Inspect, if available:

- docs/
- 03_Resource_Architecture/01_Resource_Foundation.md
- 03_Resource_Architecture/02_Taxonomy_Implementation.md
- resources/views/components/public/ directory structure.

## Files to Create

Create:

03_Resource_Architecture/07_Resource_Public_Page_Patterns.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI requirements for public resource pages.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that unpublished resources are not exposed publicly.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

This document defines the SEO strategy for resource public pages.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the listing and detail page patterns.
- The file defines the resource card component pattern.

## Integration Requirements

This document must be used as the primary reference when building resource public controllers and views.

## Definition of Done

This implementation is complete when:

- The Resource Public Page Patterns file exists.
- The file matches the approved Phase 03 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.