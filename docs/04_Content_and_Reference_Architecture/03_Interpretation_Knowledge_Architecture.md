# 03 — Interpretation & Knowledge Architecture

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Core Product Areas, Core Content Model).
- The approved Phase 02 documentation (specifically Database Foundation, Public Site Foundation).
- The approved Phase 03 Resource Architecture (specifically Resource Foundation, Admin CRUD Patterns, Public Page Patterns).
- The approved Phase 04 Reference Ranges Architecture document.
- The approved Phase 04 SOP Resources Architecture document.

## Defines

This document defines:

- The shared content resource architecture for Result Interpretation and Knowledge & Education.
- The Result Interpretation resource data model.
- The Knowledge & Education resource data model.
- The rich text and media handling rules.
- The administrative management patterns.
- The public display and reading experience requirements.
- The testing and verification strategy.

## Affects

This document affects:

- The Interpretation and Knowledge database migrations.
- The InterpretationResource and KnowledgeResource Eloquent models.
- The Interpretation and Knowledge Admin CRUD interfaces.
- The Interpretation and Knowledge Public Detail pages.
- The Search Implementation integration.
- The Resource Testing Strategy.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a structured, safe, and readable architecture for educational content.
- Ensures Result Interpretation resources include mandatory disclaimers.
- Provides a consistent reading experience for Knowledge & Education resources.
- Defines safe rich text and media handling rules.

---

# 1. Purpose

This document establishes the Interpretation & Knowledge Architecture for LABGOTO.

Result Interpretation and Knowledge & Education resources are content-rich, educational materials. Unlike Calculators, Tools, or Reference Ranges, these resources are primarily reading experiences that explain laboratory concepts, result patterns, and study topics.

This architecture ensures that educational content is structured, accessible, and safe, while maintaining a consistent reading experience across the platform.

---

# 2. Content Resource Foundation

Both Result Interpretation and Knowledge & Education resources share common patterns.

## 2.1 Shared Attributes

In addition to the common resource attributes (`id`, `name`, `slug`, `status`, etc.), both resource types should support:

content
- Type: longtext
- Description: The main rich text content.

author_name
- Type: string
- Nullable: true
- Description: The author or contributor.

featured_image
- Type: string
- Nullable: true
- Description: Path to the featured image.

reading_time_minutes
- Type: integer
- Nullable: true
- Description: Estimated reading time.

## 2.2 Shared Relationships

- `categories()`: belongsToMany Category.
- `tags()`: belongsToMany Tag.
- `createdBy()`: belongsTo User.
- `updatedBy()`: belongsTo User.

## 2.3 Rich Text Handling

Both resource types must use safe rich text rendering.

Rules:
- Sanitize all HTML before rendering to prevent XSS.
- Do not allow `<script>` tags or inline event handlers.
- Use a safe HTML purifier or Markdown parser.
- Support basic formatting: headings, paragraphs, lists, bold, italic, links, images.

---

# 3. Result Interpretation Architecture

Result Interpretation resources explain how to interpret specific laboratory results or patterns.

## 3.1 Interpretation Data Model

The `interpretation_resources` table must extend the common resource attributes and shared content attributes.

Additional columns:

clinical_context
- Type: text
- Nullable: true
- Description: The clinical context or scenario for the interpretation.

related_analytes
- Type: json
- Nullable: true
- Description: Array of related analyte names or reference range IDs.

mandatory_disclaimer
- Type: text
- Nullable: false
- Description: The educational disclaimer displayed on the public page.

## 3.2 Mandatory Disclaimer

Every public Result Interpretation page must display a mandatory disclaimer.

Default disclaimer text:

> **Educational Interpretation Only:** This content is for educational purposes and explains common laboratory result patterns. It does not constitute medical advice or diagnosis. Always interpret laboratory results in the context of the patient's clinical condition and consult with a qualified healthcare professional.

## 3.3 Related Resources

Result Interpretation resources should link to:
- Related Reference Ranges (via `related_analytes`).
- Related Calculators or Tools (via shared tags).

---

# 4. Knowledge & Education Architecture

Knowledge & Education resources provide general laboratory science educational content.

## 4.1 Knowledge Data Model

The `knowledge_resources` table must extend the common resource attributes and shared content attributes.

Additional columns:

topic_category
- Type: string
- Nullable: true
- Description: The general topic category (e.g., "Hematology", "Clinical Chemistry").

difficulty_level
- Type: string
- Nullable: true
- Description: Difficulty level (e.g., "Beginner", "Intermediate", "Advanced").

learning_objectives
- Type: text
- Nullable: true
- Description: List of learning objectives.

## 4.2 Learning Objectives

Knowledge resources may display learning objectives at the top of the content.

Rules:
- Render as a bulleted list.
- Use semantic HTML (`<ul>`, `<li>`).
- Do not display if empty.

## 4.3 Related Resources

Knowledge resources should link to:
- Related Knowledge articles (via shared tags or categories).
- Related Calculators, Tools, or Reference Ranges (via shared tags).

---

# 5. Rich Text & Media Handling

## 5.1 Image Storage

Images must be stored on the local filesystem using Laravel's storage abstraction.

Public path:

storage/app/public/content/

A symlink must be created from `public/storage/content` to `storage/app/public/content`.

## 5.2 Allowed Image Types

- JPEG (.jpg, .jpeg)
- PNG (.png)
- WebP (.webp)
- SVG (.svg) — must be sanitized

## 5.3 Image Size Limits

- Maximum file size: 2 MB per image.
- Maximum images per resource: 10.

## 5.4 Alt Text

All images must have alt text for accessibility.

Rules:
- Provide an alt text input field in the admin form.
- Use empty alt text (`alt=""`) for decorative images.
- Use descriptive alt text for informative images.

## 5.5 Security Rules

- Validate image extensions and MIME types.
- Sanitize file names before storage.
- Do not allow executable files.
- Do not expose the raw storage path publicly.

---

# 6. Admin Management

Administrators with `interpretation.create` / `knowledge.create` and `interpretation.edit` / `knowledge.edit` permissions can manage these resources.

## 6.1 Interpretation Form

The admin form must include:
- Common resource fields (name, slug, status, short_description).
- Content editor (rich text or Markdown).
- Clinical context field.
- Related analytes field (JSON or multi-select).
- Mandatory disclaimer field (pre-filled with default text).
- Featured image upload.
- Taxonomy assignment.

## 6.2 Knowledge Form

The admin form must include:
- Common resource fields (name, slug, status, short_description).
- Content editor (rich text or Markdown).
- Topic category field.
- Difficulty level field.
- Learning objectives field.
- Author name field.
- Reading time field.
- Featured image upload.
- Taxonomy assignment.

## 6.3 Audit Logging

Changes to Interpretation and Knowledge resources must be audited.

Audit actions:
- interpretation.created / knowledge.created
- interpretation.updated / knowledge.updated
- interpretation.deleted / knowledge.deleted
- interpretation.published / knowledge.published
- interpretation.archived / knowledge.archived

---

# 7. Public Display & Reading Experience

## 7.1 Public Listing Pages

Interpretation Listing:
- Route: `/interpretation`
- Displays a searchable list of published Interpretation resources.
- Show short description and reading time.
- Filter by category and tag.

Knowledge Listing:
- Route: `/knowledge`
- Displays a searchable list of published Knowledge resources.
- Show short description, reading time, and difficulty level.
- Filter by category and tag.

## 7.2 Public Detail Pages

Interpretation Detail:
- Route: `/interpretation/{slug}`
- Display the title, breadcrumbs, and meta info.
- Display the mandatory disclaimer prominently at the top.
- Display the clinical context.
- Display the rich text content.
- Display related analytes and resources.
- Show reading time.

Knowledge Detail:
- Route: `/knowledge/{slug}`
- Display the title, breadcrumbs, and meta info.
- Display learning objectives (if available).
- Display the rich text content.
- Display featured image (if available).
- Display related resources.
- Show reading time and difficulty level.

## 7.3 Reading Experience Rules

- Use a readable line height (at least 1.5).
- Use a max width for reading content (e.g., `max-w-3xl`).
- Use semantic HTML for headings.
- Provide a clear table of contents for long articles (optional).
- Ensure images are responsive.

## 7.4 Search Integration

- Interpretation resources must be searchable by `name`, `short_description`, and `clinical_context`.
- Knowledge resources must be searchable by `name`, `short_description`, and `content` (if practical).
- The SearchService providers should match against these fields.

---

# 8. Testing & Verification

## 8.1 Data Integrity Tests

- Test that rich text content is sanitized correctly.
- Test that image paths are stored correctly.
- Test that reading time is calculated or stored correctly.

## 8.2 Feature Tests

- Test that the public page displays the mandatory disclaimer for Interpretation resources.
- Test that unpublished resources are not accessible.
- Test that images are displayed correctly.
- Test that audit logs capture changes.
- Test that file upload validation rejects disallowed file types.

## 8.3 Accessibility Tests

- Test that images have alt text.
- Test that headings are in logical order.
- Test that reading content is keyboard accessible.

---

# Document Completion Check

## Requirements Covered

- Shared content resource foundation defined.
- Result Interpretation architecture defined.
- Knowledge & Education architecture defined.
- Rich text and media handling rules defined.
- Admin management patterns defined.
- Public display and reading experience requirements defined.
- Testing and verification strategy defined.

## Decisions Established

- Interpretation and Knowledge resources use separate tables.
- Rich text content must be sanitized.
- Images are stored locally with strict file type and size limits.
- Result Interpretation resources require a mandatory disclaimer.
- Knowledge resources support learning objectives and difficulty levels.

## Dependencies

- Approved Phase 03 Resource Foundation.
- Approved Phase 03 Admin CRUD Patterns.
- Approved Phase 03 Public Page Patterns.
- Approved Phase 04 Reference Ranges Architecture.
- Approved Phase 04 SOP Resources Architecture.

## Files Affected

This document creates:

04_Content_and_Reference_Architecture/03_Interpretation_Knowledge_Architecture.md

It dictates the implementation of the interpretation_resources and knowledge_resources tables, models, and public/admin interfaces.

## Open Questions

1. Should we use a dedicated rich text editor library (e.g., TinyMCE, Quill) or a simple Markdown editor for MVP?
2. Should reading time be auto-calculated from content length or manually entered?

## TBD Decisions

- TBD: Final rich text editor selection.
- TBD: Final reading time calculation strategy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE04-DOC03

## Context

LABGOTO requires a structured architecture for Result Interpretation and Knowledge & Education resources. This document defines the shared content foundation, data models, rich text handling, and reading experience requirements.

This prompt implements the documentation outcome of the Interpretation & Knowledge Architecture document.

## Objective

Create the official LABGOTO Interpretation & Knowledge Architecture document inside the approved Phase 04 documentation structure.

After this prompt is completed, the repository will have a binding specification for the Interpretation and Knowledge vertical slices.

## Existing Architecture

Relevant approved decisions:

- Resources use the common lifecycle and RBAC patterns.
- Rich text content must be sanitized.
- Result Interpretation resources require mandatory disclaimers.
- Images must be stored securely with file type validation.

## Files to Inspect

Inspect, if available:

- docs/
- 04_Content_and_Reference_Architecture/
- 03_Resource_Architecture/01_Resource_Foundation.md

## Files to Create

Create:

04_Content_and_Reference_Architecture/03_Interpretation_Knowledge_Architecture.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines the schema architecture.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI requirements.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure the mandatory disclaimer and rich text sanitization rules are clearly documented.

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
- The file clearly defines the Interpretation and Knowledge data models.
- The file defines the mandatory disclaimer requirement.

## Integration Requirements

This document must be used as the primary reference when building the Interpretation and Knowledge vertical slices.

## Definition of Done

This implementation is complete when:

- The Interpretation & Knowledge Architecture file exists.
- The file matches the approved Phase 04 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
