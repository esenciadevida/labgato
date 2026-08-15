# 09 — Resource Build Order

## Depends On

This document depends on:

- All preceding Phase 03 documents (01 through 08).
- The approved Phase 01 and Phase 02 documentation.
- The completed Slice 1 Application Foundation implementation.
- The approved vertical-slice development strategy.

## Defines

This document defines:

- The exact step-by-step implementation order for the remaining foundation modules (Taxonomy Admin, Search).
- The exact step-by-step implementation order for Slice 2 (Calculators Vertical Slice).
- The exact step-by-step implementation order for Slice 3 (Laboratory Tools Vertical Slice).
- The dependencies between these builds.
- The Definition of Done for a complete resource vertical slice.
- The criteria for transitioning from resource implementation to Phase 04 documentation.

## Affects

This document affects:

- The immediate development workflow for the engineering team.
- The sequence of pull requests and commits for the next several weeks.
- The readiness assessment for Phase 04 (Content & Reference Architecture).

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Prevents out-of-order development that causes merge conflicts or architectural debt.
- Ensures that shared infrastructure (Taxonomy, Search) is stable before resource-specific CRUD is built.
- Provides a clear checklist for completing the first two major product features.

---

# 1. Purpose

This document establishes the Resource Build Order for LABGOTO.

With the application foundation (Slice 1) complete and the resource architecture (Phase 03) documented, we must now build the actual features. Building resources out of order (e.g., building the Calculator UI before the Taxonomy Admin module exists) will result in blocked work and technical debt.

This build order ensures that shared infrastructure is implemented first, followed by complete, end-to-end vertical slices for Calculators and Laboratory Tools.

---

# 2. Build Order Philosophy

We will continue to use the **Vertical Slice** methodology.

Instead of building "all database migrations," then "all backend controllers," then "all frontend views," we will build complete features from the database to the UI, testing and verifying each step.

However, because resources share common infrastructure (Taxonomy and Search), we must build the **Shared Infrastructure Slices** first.

---

# 3. Step-by-Step Build Order

## Build 10: Taxonomy Admin Module

**Objective**: Build the administrative interface for managing Categories and Tags, and the reusable UI components for assigning them to resources.

**Tasks**:
1. Create `CategoryController` and `TagController` for the Admin Portal.
2. Create Admin CRUD views (or Livewire components) for Categories (hierarchical tree) and Tags (flat list).
3. Implement the reusable `<x-admin.category-selector>` and `<x-admin.tag-input>` Blade/Alpine components.
4. Write feature tests for Taxonomy CRUD and assignment.
5. Seed initial laboratory categories (e.g., Hematology, Clinical Chemistry, Microbiology).

**Dependencies**: Slice 1 (Admin Foundation).

## Build 11: Search Implementation

**Objective**: Build the global search functionality, including the backend service, public search page, and analytics logging.

**Tasks**:
1. Add Full-Text indexes to existing/future resource tables via migrations.
2. Implement `SearchService` to query Calculators and Tools (and future resources).
3. Create `SearchController` and the public `/search` route.
4. Build the public Search Results page with filtering, pagination, and empty states.
5. Implement search query logging for analytics.
6. Write feature tests for search queries, filtering, and empty states.

**Dependencies**: Build 10 (Taxonomy is needed for search filters).

## Build 12: Calculators Vertical Slice (Slice 2)

**Objective**: Build the complete, end-to-end Calculator feature, from database to public interactive UI.

**Tasks**:
1. Create `calculators` and pivot table migrations.
2. Create `Calculator` model with lifecycle traits and relationships.
3. Create `CalculatorInterface` and the `CalculatorService` resolution logic.
4. Implement the first concrete calculator: `AnionGapCalculator` (PHP class).
5. Build Admin CRUD for Calculators (Index, Create, Edit, Delete, Archive) using the patterns from Doc 06.
6. Integrate Taxonomy assignment and SEO fields into the Calculator admin form.
7. Build the Public Calculator Detail page with the interactive Livewire/Alpine form.
8. Implement the dynamic Input/Output schema rendering.
9. Write Unit tests for `AnionGapCalculator` (math accuracy, rounding, edge cases).
10. Write Feature tests for Admin CRUD, Public rendering, and calculation execution.
11. Verify SEO, Accessibility, and Performance for the Calculator pages.

**Dependencies**: Build 10, Build 11.

## Build 13: Laboratory Tools Vertical Slice (Slice 3)

**Objective**: Build the complete, end-to-end Laboratory Tools feature, reusing the patterns established in Slice 2.

**Tasks**:
1. Create `laboratory_tools` and pivot table migrations.
2. Create `LaboratoryTool` model.
3. Create `ToolInterface` and the `ToolService` resolution logic.
4. Implement the first concrete tool: `DilutionCalculatorTool` or `UnitConverterTool`.
5. Build Admin CRUD for Tools (reusing shared resource components where possible).
6. Build the Public Tool Detail page with the interactive form.
7. Write Unit tests for the specific tool logic.
8. Write Feature tests for Admin CRUD, Public rendering, and tool execution.
9. Verify SEO, Accessibility, and Performance.

**Dependencies**: Build 12 (Reuses the established patterns and shared components).

---

# 4. Definition of Done for a Resource Slice

A resource vertical slice (e.g., Calculators) is only considered **COMPLETE** when:

- [ ] Database migrations run successfully and include Full-Text indexes.
- [ ] Eloquent models are implemented with correct relationships and scopes.
- [ ] Calculation/Tool engine logic is implemented in dedicated PHP classes.
- [ ] Admin CRUD (Index, Create, Edit, Delete, Archive) is fully functional and secured by RBAC.
- [ ] Taxonomy (Categories/Tags) can be assigned via the admin form.
- [ ] Public listing page renders with filtering, pagination, and correct empty states.
- [ ] Public detail page renders the interactive form and displays results accurately.
- [ ] Input validation prevents invalid calculations and displays accessible error messages.
- [ ] Unit tests verify mathematical accuracy and edge cases.
- [ ] Feature tests verify CRUD, authorization, and public rendering.
- [ ] SEO meta tags, canonical URLs, and structured data are correct.
- [ ] Pages pass manual keyboard navigation and screen reader checks.
- [ ] Audit logs record all administrative changes to the resource.
- [ ] No dead UI, fake statistics, or disconnected pages exist.

---

# 5. Transition to Phase 04

Once Build 12 (Calculators) and Build 13 (Laboratory Tools) are marked **COMPLETE**, the project will have its first fully functional, public-facing laboratory resources.

At that point, we will pause implementation and transition to **Phase 04: Content & Reference Architecture**.

Phase 04 will document the architecture for:
- Reference Ranges
- SOP Resources
- Result Interpretation Resources
- Knowledge & Education Resources

---

# Document Completion Check

## Requirements Covered

- Build order philosophy defined.
- Step-by-step build order for Taxonomy and Search defined.
- Step-by-step build order for Calculators (Slice 2) defined.
- Step-by-step build order for Laboratory Tools (Slice 3) defined.
- Definition of Done for a resource slice defined.
- Transition criteria to Phase 04 defined.

## Decisions Established

- Taxonomy Admin and Search must be built before resource-specific CRUD.
- Calculators will be the first resource vertical slice (Slice 2).
- Laboratory Tools will be the second resource vertical slice (Slice 3).
- A resource slice is only complete when DB, Backend, Admin UI, Public UI, Tests, SEO, and A11y are all verified.

## Dependencies

- All Phase 03 documents.
- Completed Slice 1 Application Foundation.

## Files Affected

This document creates:

03_Resource_Architecture/09_Resource_Build_Order.md

It serves as the execution plan for the next major development cycle.

## Open Questions

1. Which specific Laboratory Tool should be the first one built in Slice 3 (e.g., Dilution Calculator, Unit Converter)?

## TBD Decisions

- TBD: Selection of the first Laboratory Tool for Slice 3.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE03-DOC09

## Context

Phase 03 documentation is concluding. This document establishes the exact build order for the shared resource infrastructure (Taxonomy, Search) and the first two resource vertical slices (Calculators, Tools).

This prompt implements the documentation outcome of the Resource Build Order document.

## Objective

Create the official LABGOTO Resource Build Order document inside the approved Phase 03 documentation structure.

After this prompt is completed, Phase 03 documentation is fully drafted and ready for final review and repository commit.

## Existing Architecture

Relevant approved decisions:

- Shared infrastructure (Taxonomy, Search) precedes resource CRUD.
- Vertical slices must include DB, Backend, Admin UI, Public UI, and Tests.
- Calculators are Slice 2; Tools are Slice 3.

## Files to Inspect

Inspect, if available:

- docs/
- 03_Resource_Architecture/
- Documents 01 through 08.

## Files to Create

Create:

03_Resource_Architecture/09_Resource_Build_Order.md

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

No public SEO impact.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the 4 build steps (Taxonomy, Search, Calculators, Tools).
- The file defines the Definition of Done for a resource slice.

## Integration Requirements

This document bridges the gap between Phase 03 (Resource Architecture) and actual code implementation for Slice 2. It must be referenced when starting Build 10.

## Definition of Done

This implementation is complete when:

- The Resource Build Order file exists.
- The file matches the approved Phase 03 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.