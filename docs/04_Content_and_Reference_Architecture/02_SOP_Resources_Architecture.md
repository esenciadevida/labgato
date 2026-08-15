# 02 — SOP Resources Architecture

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Core Product Areas, Core Content Model).
- The approved Phase 02 documentation (specifically Database Foundation, Admin Portal Foundation).
- The approved Phase 03 Resource Architecture (specifically Resource Foundation, Admin CRUD Patterns, Public Page Patterns).
- The approved Phase 04 Reference Ranges Architecture document.

## Defines

This document defines:

- The SOP Resource data model.
- The document control metadata requirements.
- The structured content section architecture.
- The versioning strategy.
- The attachment handling rules.
- The administrative management and review workflow patterns.
- The public display and search requirements.
- The testing and verification strategy.

## Affects

This document affects:

- The SOP Resources database migration.
- The SopResource Eloquent model.
- The SOP Admin CRUD interface.
- The SOP Public Detail page.
- The Search Implementation integration.
- The Resource Testing Strategy.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a structured, document-controlled architecture for Standard Operating Procedures.
- Ensures SOPs include required metadata (version, author, review dates).
- Provides a clear separation of procedural content sections.
- Defines safe attachment handling rules.

---

# 1. Purpose

This document establishes the SOP Resources Architecture for LABGOTO.

Standard Operating Procedures (SOPs) are formal, structured documents used to ensure consistency and quality in laboratory operations. Unlike calculators or reference ranges, SOPs are document-centric resources that require strict document control metadata, version tracking, and structured procedural content.

This architecture ensures SOPs are managed professionally while remaining safe and accessible on the Hostinger shared hosting environment.

---

# 2. SOP Data Model

The `sop_resources` table must extend the common resource attributes defined in the Resource Foundation.

## 2.1 Additional Columns

In addition to the common attributes (`id`, `name`, `slug`, `status`, etc.), the `sop_resources` table must include:

document_code
- Type: string
- Nullable: true
- Description: Internal document identifier (e.g., "SOP-HEM-001").

version
- Type: string
- Nullable: true
- Description: Version number or identifier (e.g., "1.0", "2.1").

author_name
- Type: string
- Nullable: true
- Description: The author of the SOP.

approved_by
- Type: string
- Nullable: true
- Description: The person who approved the SOP.

effective_date
- Type: date
- Nullable: true
- Description: The date the SOP became effective.

next_review_date
- Type: date
- Nullable: true
- Description: The date the SOP is due for review.

purpose
- Type: text
- Nullable: true
- Description: The purpose of the procedure.

scope
- Type: text
- Nullable: true
- Description: The scope of the procedure.

procedure_content
- Type: longtext
- Nullable: true
- Description: The step-by-step procedure.

quality_control
- Type: text
- Nullable: true
- Description: Quality control requirements.

safety_information
- Type: text
- Nullable: true
- Description: Safety warnings and precautions.

references
- Type: text
- Nullable: true
- Description: References and citations.

attachments
- Type: json
- Nullable: true
- Description: Array of attachment file paths and metadata.

## 2.2 Relationships

- `categories()`: belongsToMany Category.
- `tags()`: belongsToMany Tag.
- `createdBy()`: belongsTo User.
- `updatedBy()`: belongsTo User.

---

# 3. Document Control Metadata

Document control is a critical requirement for SOPs.

## 3.1 Required Metadata

Every SOP should display the following metadata on the public detail page:

- Document Code
- Version
- Author
- Approved By
- Effective Date
- Next Review Date
- Status (Draft, Published, Archived, Obsolete)

## 3.2 Review Status Indicator

The public and admin interfaces should visually indicate if an SOP is due for review.

Rules:
- If `next_review_date` is in the past and status is `published`, display a "Review Overdue" badge.
- If `next_review_date` is within 30 days, display a "Review Due Soon" badge.
- This indicator must not block access, only inform.

---

# 4. Structured Content Sections

SOPs must use structured content sections rather than a single unstructured text blob.

## 4.1 Standard Sections

The following sections are defined for the MVP:

1. Purpose
2. Scope
3. Procedure
4. Quality Control
5. Safety Information
6. References

## 4.2 Rendering Rules

- Sections must be rendered in a logical order.
- Empty sections must not be displayed.
- Section headings must use semantic HTML (`<h2>`, `<h3>`).
- Procedure content may contain rich text formatting (lists, bold, etc.).

## 4.3 Rich Text Handling

For the MVP, `procedure_content` may store safe HTML or Markdown.

Rules:
- Sanitize all HTML before rendering to prevent XSS.
- Do not allow `<script>` tags or inline event handlers.
- Use a safe HTML purifier or Markdown parser.

---

# 5. Versioning Strategy

## 5.1 MVP Versioning

For the MVP, versioning is handled via the `version` column and the resource `status` lifecycle.

- When a new version of an SOP is created, it is created as a new record with a new `version` number.
- The old record is transitioned to `archived` or `obsolete` status.
- Only the latest `published` version should be easily discoverable in public search.

## 5.2 Future Version History

A dedicated version history table may be implemented in a future phase to track all revisions of a single SOP document.

For the MVP, manual creation of new versions is acceptable.

---

# 6. Attachments

## 6.1 Attachment Storage

Attachments must be stored on the local filesystem using Laravel's storage abstraction.

Public path:

storage/app/public/sops/

A symlink must be created from `public/storage/sops` to `storage/app/public/sops`.

## 6.2 Allowed File Types

For the MVP, allowed attachment types are:

- PDF (.pdf)
- Microsoft Word (.doc, .docx)
- Plain text (.txt)

## 6.3 File Size Limits

- Maximum file size: 5 MB per attachment.
- Maximum attachments per SOP: 5.

## 6.4 Security Rules

- Validate file extensions and MIME types.
- Sanitize file names before storage.
- Do not allow executable files (.exe, .php, .sh, etc.).
- Do not expose the raw storage path publicly.

---

# 7. Admin Management

Administrators with `sops.create` and `sops.edit` permissions can manage SOPs.

## 7.1 SOP Form

The admin form must include:
- Common resource fields (name, slug, status, short_description).
- Document Control section (document_code, version, author_name, approved_by, effective_date, next_review_date).
- Content sections (purpose, scope, procedure_content, quality_control, safety_information, references).
- Attachment upload field.
- Taxonomy assignment.

## 7.2 Attachment Upload UI

- Use a standard file input.
- Display existing attachments with a delete option.
- Show file size and type validation errors.

## 7.3 Audit Logging

Changes to SOP content and document control metadata must be audited.

Audit actions:
- sops.created
- sops.updated
- sops.deleted
- sops.published
- sops.archived

Capture changes to `version`, `status`, and `next_review_date`.

---

# 8. Public Display & Search

## 8.1 Public Listing Page

- Route: `/sops`
- Displays a searchable list of published SOPs.
- Show document code, version, and effective date in the list.
- Filter by category and tag.

## 8.2 Public Detail Page

- Route: `/sops/{slug}`
- Display the SOP title and document control metadata header.
- Display the structured content sections.
- Display attachments as downloadable links.
- Display the mandatory educational disclaimer.
- Show related resources via shared tags.

## 8.3 Mandatory Disclaimer

Every public SOP page must display the following disclaimer (or similar approved text):

> **Educational Template Only:** This Standard Operating Procedure is provided for educational and reference purposes. It must be adapted to your specific laboratory's instruments, reagents, and regulatory requirements before use. Always follow your institution's approved procedures.

## 8.4 Search Integration

- SOPs must be searchable by `name`, `document_code`, and `short_description`.
- The SearchService provider for SOPs should match against these fields.

---

# 9. Testing & Verification

## 9.1 Data Integrity Tests

- Test that `next_review_date` is correctly parsed.
- Test that attachment file paths are stored correctly.
- Test that HTML sanitization removes script tags.

## 9.2 Feature Tests

- Test that the public page displays the mandatory disclaimer.
- Test that unpublished SOPs are not accessible.
- Test that attachments are downloadable.
- Test that audit logs capture changes to document control metadata.
- Test that file upload validation rejects disallowed file types.

---

# Document Completion Check

## Requirements Covered

- SOP data model defined.
- Document control metadata requirements defined.
- Structured content section architecture defined.
- Versioning strategy defined.
- Attachment handling rules defined.
- Admin management patterns defined.
- Public display and search requirements defined.
- Testing and verification strategy defined.

## Decisions Established

- SOPs use structured content columns for MVP.
- Document control metadata is stored on the main table.
- Versioning is handled via new records and status lifecycle for MVP.
- Attachments are stored locally with strict file type and size limits.
- A mandatory educational disclaimer is required on all public SOP pages.

## Dependencies

- Approved Phase 03 Resource Foundation.
- Approved Phase 03 Admin CRUD Patterns.
- Approved Phase 03 Public Page Patterns.

## Files Affected

This document creates:

04_Content_and_Reference_Architecture/02_SOP_Resources_Architecture.md

It dictates the implementation of the sop_resources table, SopResource model, and public/admin interfaces.

## Open Questions

1. Should we implement a dedicated version history table in the MVP or defer it?
2. Should attachments be publicly accessible without authentication, or should they require a session?

## TBD Decisions

- TBD: Final decision on version history implementation.
- TBD: Final attachment access control strategy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE04-DOC02

## Context

LABGOTO requires a structured architecture for Standard Operating Procedures. This document defines the data model, document control metadata, structured content sections, versioning, and attachment handling.

This prompt implements the documentation outcome of the SOP Resources Architecture document.

## Objective

Create the official LABGOTO SOP Resources Architecture document inside the approved Phase 04 documentation structure.

After this prompt is completed, the repository will have a binding specification for the SOP Resources vertical slice.

## Existing Architecture

Relevant approved decisions:

- Resources use the common lifecycle and RBAC patterns.
- SOPs require document control metadata.
- Attachments must be stored securely with file type validation.
- Mandatory disclaimers are required on public pages.

## Files to Inspect

Inspect, if available:

- docs/
- 04_Content_and_Reference_Architecture/
- 03_Resource_Architecture/01_Resource_Foundation.md

## Files to Create

Create:

04_Content_and_Reference_Architecture/02_SOP_Resources_Architecture.md

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
- Ensure the mandatory disclaimer and attachment security rules are clearly documented.

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
- The file clearly defines the data model and document control metadata.
- The file defines the mandatory disclaimer requirement.

## Integration Requirements

This document must be used as the primary reference when building the SOP Resources vertical slice.

## Definition of Done

This implementation is complete when:

- The SOP Resources Architecture file exists.
- The file matches the approved Phase 04 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.