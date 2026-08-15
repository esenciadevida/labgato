# 04 — Content & Reference Build Order

## Depends On

This document depends on:

- All preceding Phase 04 documents (01 through 03).
- The approved Phase 01, Phase 02, and Phase 03 documentation.
- The completed Slice 1, Builds 10-13 implementation.
- The approved vertical-slice development strategy.

## Defines

This document defines:

- The exact step-by-step implementation order for Slice 4 (Reference Ranges).
- The exact step-by-step implementation order for Slice 5 (SOP Resources).
- The exact step-by-step implementation order for Slice 6 (Interpretation & Knowledge).
- The dependencies between these builds.
- The Definition of Done for a content/reference vertical slice.
- The criteria for transitioning from resource implementation to Phase 05 documentation.

## Affects

This document affects:

- The immediate development workflow for the engineering team.
- The sequence of pull requests and commits for the remaining MVP resources.
- The readiness assessment for Phase 05 (AI & Advanced Features Architecture).

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Prevents out-of-order development of the remaining resource types.
- Ensures that shared infrastructure (attachments, rich text handling) is implemented cleanly within the respective slices.
- Provides a clear checklist for completing the core MVP content features.

---

# 1. Purpose

This document establishes the Content & Reference Build Order for LABGOTO.

With the interactive resources (Calculators and Tools) complete, we must now build the structured data and educational content resources. Building these out of order could result in duplicated effort, especially regarding shared features like file attachments and rich text sanitization.

This build order ensures that Reference Ranges, SOPs, Interpretation, and Knowledge resources are implemented as complete, end-to-end vertical slices.

---

# 2. Build Order Philosophy

We will continue to use the **Vertical Slice** methodology.

Each resource type will be built from the database migration to the public UI, including admin CRUD, search integration, tests, and mandatory disclaimers.

Because these resources are less computationally complex than Calculators/Tools but more content-heavy, the focus will be on data integrity, document control, rich text safety, and reading experience.

---

# 3. Step-by-Step Build Order

## Build 14: Reference Ranges Vertical Slice (Slice 4)

**Objective**: Build the complete, end-to-end Reference Ranges feature, focusing on demographic variations, unit conversions, and data integrity.

**Tasks**:
1. Create `reference_ranges` and pivot table migrations.
2. Create `ReferenceRange` model with lifecycle traits and relationships.
3. Build Admin CRUD for Reference Ranges (Index, Create, Edit, Delete, Archive) with rapid-entry UX.
4. Implement data integrity validation (e.g., lower_limit < upper_limit).
5. Build the Public Reference Ranges Listing page (grouped by analyte).
6. Build the Public Reference Range Detail page with demographic tables and the mandatory educational disclaimer.
7. Implement the Search provider for Reference Ranges.
8. Write Feature tests for Admin CRUD, Public rendering, and data validation.
9. Verify SEO, Accessibility, and Performance.

**Dependencies**: Builds 10-13 (Shared taxonomy and search foundation).

## Build 15: SOP Resources Vertical Slice (Slice 5)

**Objective**: Build the complete, end-to-end SOP Resources feature, focusing on document control metadata, structured sections, and secure attachments.

**Tasks**:
1. Create `sop_resources` and pivot table migrations.
2. Create `SopResource` model.
3. Build Admin CRUD for SOPs with structured content sections and document control fields.
4. Implement secure file upload handling for SOP attachments (PDF, DOCX) with MIME/extension validation.
5. Build the Public SOP Listing page.
6. Build the Public SOP Detail page with document control header, structured sections, attachment downloads, and the mandatory educational disclaimer.
7. Implement the Search provider for SOPs.
8. Write Feature tests for Admin CRUD, file upload validation, and Public rendering.
9. Verify SEO, Accessibility, and Performance.

**Dependencies**: Build 14 (Reuses established admin patterns).

## Build 16: Interpretation & Knowledge Vertical Slices (Slice 6)

**Objective**: Build the complete, end-to-end Interpretation and Knowledge features, focusing on rich text handling, image uploads, and reading experience.

**Tasks**:
1. Create `interpretation_resources` and `knowledge_resources` tables and pivot migrations.
2. Create `InterpretationResource` and `KnowledgeResource` models.
3. Integrate a safe rich text editor or Markdown parser in the Admin CRUD forms.
4. Implement secure image upload handling with sanitization and alt-text requirements.
5. Build Admin CRUD for both resource types.
6. Build Public Listing and Detail pages for both resource types, ensuring a comfortable reading experience (max-width, readable line height).
7. Ensure the mandatory disclaimer is displayed on all Interpretation pages.
8. Implement Search providers for both resource types.
9. Write Feature tests for rich text sanitization, image uploads, and Public rendering.
10. Verify SEO, Accessibility, and Performance.

**Dependencies**: Build 15 (Reuses established admin and attachment patterns).

---

# 4. Definition of Done for a Content/Reference Slice

A content or reference vertical slice is only considered **COMPLETE** when:

- [ ] Database migrations run successfully and include Full-Text indexes.
- [ ] Eloquent models are implemented with correct relationships and scopes.
- [ ] Admin CRUD is fully functional, secured by RBAC, and includes resource-specific fields (e.g., document control, demographics).
- [ ] Taxonomy (Categories/Tags) can be assigned via the admin form.
- [ ] File/Image uploads (if applicable) are strictly validated and stored securely.
- [ ] Rich text/HTML content (if applicable) is sanitized before rendering.
- [ ] Public listing page renders with filtering, pagination, and correct empty states.
- [ ] Public detail page renders content accurately and includes the mandatory educational disclaimer where required.
- [ ] Feature tests verify CRUD, authorization, file validation, and public rendering.
- [ ] SEO meta tags, canonical URLs, and structured data are correct.
- [ ] Pages pass manual keyboard navigation and screen reader checks.
- [ ] Audit logs record all administrative changes to the resource.
- [ ] No dead UI, fake data, or disconnected pages exist.

---

# 5. Transition to Phase 05

Once Build 14, Build 15, and Build 16 are marked **COMPLETE**, LABGOTO will have its full core MVP resource library implemented.

At that point, we will pause implementation and transition to **Phase 05: AI & Advanced Features Architecture (Post-MVP)** or an **MVP Hardening & Release Phase**, depending on project priorities.

Phase 05 will document the architecture for:
- Controlled AI Knowledge Base Generation
- AI Provider Adapter & Security (Groq)
- AI UI & Rate Limiting
- Advanced Analytics

---

# Document Completion Check

## Requirements Covered

- Build order philosophy defined.
- Step-by-step build order for Reference Ranges (Slice 4) defined.
- Step-by-step build order for SOP Resources (Slice 5) defined.
- Step-by-step build order for Interpretation & Knowledge (Slice 6) defined.
- Definition of Done for a content/reference slice defined.
- Transition criteria to Phase 05 defined.

## Decisions Established

- Reference Ranges will be built first (Slice 4) to establish complex data table patterns.
- SOP Resources will be built second (Slice 5) to establish secure attachment handling.
- Interpretation & Knowledge will be built third (Slice 6) to establish rich text and image handling.
- A slice is only complete when DB, Backend, Admin UI, Public UI, Tests, Disclaimers, SEO, and A11y are all verified.

## Dependencies

- All Phase 04 documents.
- Completed Builds 10-13.

## Files Affected

This document creates:

04_Content_and_Reference_Architecture/04_Content_Reference_Build_Order.md

It serves as the execution plan for the remaining MVP resource development.

## Open Questions

1. Should Interpretation and Knowledge be split into two separate build prompts (16a and 16b) to keep the implementation steps smaller?

## TBD Decisions

- TBD: Final decision on splitting Build 16 into two separate prompts.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE04-DOC04

## Context

Phase 04 documentation is concluding. This document establishes the exact build order for the remaining resource vertical slices (Reference Ranges, SOPs, Interpretation, Knowledge).

This prompt implements the documentation outcome of the Content & Reference Build Order document.

## Objective

Create the official LABGOTO Content & Reference Build Order document inside the approved Phase 04 documentation structure.

After this prompt is completed, Phase 04 documentation is fully drafted and ready for final review and repository commit.

## Existing Architecture

Relevant approved decisions:

- Vertical slices must include DB, Backend, Admin UI, Public UI, and Tests.
- Mandatory disclaimers are required for specific resource types.
- File and rich text handling must be strictly validated and sanitized.

## Files to Inspect

Inspect, if available:

- docs/
- 04_Content_and_Reference_Architecture/
- Documents 01 through 03.

## Files to Create

Create:

04_Content_and_Reference_Architecture/04_Content_Reference_Build_Order.md

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
- The file clearly defines the 3 build steps (Slices 4, 5, 6).
- The file defines the Definition of Done for a content slice.

## Integration Requirements

This document bridges the gap between Phase 04 (Content Architecture) and actual code implementation for the remaining MVP resources.

## Definition of Done

This implementation is complete when:

- The Content & Reference Build Order file exists.
- The file matches the approved Phase 04 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.