# 01 — Reference Ranges Architecture

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Core Product Areas, Core Content Model).
- The approved Phase 02 documentation (specifically Database Foundation, Testing Foundation).
- The approved Phase 03 Resource Architecture (specifically Resource Foundation, Admin CRUD Patterns, Public Page Patterns).
- The completed Slice 1, Build 10-13 implementation.

## Defines

This document defines:

- The Reference Range resource data model.
- The architecture for handling demographic and methodological variations.
- The unit and conversion factor strategy.
- The administrative management and grouping patterns for reference data.
- The public display, search, and disclaimer requirements.
- The testing and data integrity verification strategy.

## Affects

This document affects:

- The Reference Ranges database migration.
- The ReferenceRange Eloquent model.
- The Reference Range Admin CRUD interface.
- The Reference Range Public Detail page.
- The Search Implementation integration.
- The Resource Testing Strategy.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a structured, safe, and searchable architecture for laboratory reference intervals.
- Prevents the system from acting as a diagnostic engine.
- Ensures demographic variations and unit conversions are handled accurately.
- Provides the blueprint for the Reference Ranges vertical slice.

---

# 1. Purpose

This document establishes the Reference Ranges Architecture for LABGOTO.

Reference Ranges are structured data records that provide standard laboratory reference intervals for various analytes. Because reference intervals vary significantly based on patient demographics, specimen types, and laboratory assay methods, the architecture must support multiple variations per analyte while clearly communicating that these are educational references, not diagnostic absolutes.

---

# 2. Reference Range Data Model

The `reference_ranges` table must extend the common resource attributes defined in the Resource Foundation.

## 2.1 Additional Columns

In addition to the common attributes (`id`, `name`, `slug`, `status`, etc.), the `reference_ranges` table must include:

analyte_name
- Type: string
- Required: true
- Description: The name of the test or analyte (e.g., "Glucose", "Hemoglobin").

specimen_type
- Type: string
- Nullable: true
- Description: The specimen type (e.g., "Serum", "Whole Blood", "Plasma", "Urine").

methodology
- Type: string
- Nullable: true
- Description: The assay method or instrument type, if relevant.

population_group
- Type: string
- Nullable: true
- Description: General population descriptor (e.g., "Adult", "Pediatric", "Neonate", "Pregnant").

age_min
- Type: decimal (8,4)
- Nullable: true
- Description: Minimum age in years (or days/months if standardized).

age_max
- Type: decimal (8,4)
- Nullable: true
- Description: Maximum age in years.

sex
- Type: string
- Default: 'any'
- Description: Enum-like string: 'any', 'male', 'female'.

unit
- Type: string
- Required: true
- Description: The conventional or SI unit (e.g., "mg/dL", "mmol/L", "g/dL").

lower_limit
- Type: decimal (12,4)
- Nullable: true
- Description: The lower bound of the reference interval.

upper_limit
- Type: decimal (12,4)
- Nullable: true
- Description: The upper bound of the reference interval.

critical_low
- Type: decimal (12,4)
- Nullable: true
- Description: The critical panic low value.

critical_high
- Type: decimal (12,4)
- Nullable: true
- Description: The critical panic high value.

conversion_factor
- Type: decimal (12,6)
- Nullable: true
- Description: Factor to convert to an alternative standard unit (e.g., mg/dL to mmol/L).

source_citation
- Type: text
- Nullable: true
- Description: The textbook, journal, or institutional source of the range.

effective_date
- Type: date
- Nullable: true
- Description: The date this range became effective or was published.

## 2.2 Relationships

- `categories()`: belongsToMany Category.
- `tags()`: belongsToMany Tag.
- `createdBy()`: belongsTo User.
- `updatedBy()`: belongsTo User.

---

# 3. Demographic and Methodological Variations

A single analyte (e.g., "Hemoglobin") will have multiple reference range records to account for different demographics.

## 3.1 Grouping Strategy

Instead of creating a separate "Analyte" parent table for the MVP, LABGOTO will group variations logically on the public detail page using the `slug` or a designated `analyte_group` identifier.

For MVP simplicity, the `slug` will represent the base analyte (e.g., `hemoglobin`), and the public controller will fetch all published `ReferenceRange` records that share a related tag, category, or explicit `analyte_name` match.

*Alternative MVP approach:* Use the `name` field for the specific variation title (e.g., "Hemoglobin - Adult Male") and use a dedicated `analyte_name` field to group them on the public page.

## 3.2 Display Rules

- The public page must display a clean, responsive data table.
- Rows should be grouped by Population/Sex/Age.
- Columns should include: Specimen, Population, Sex, Age Range, Unit, Reference Interval, Critical Values.

---

# 4. Units and Conversion Factors

Laboratory professionals frequently need to convert between Conventional and SI units.

## 4.1 Conversion Logic

If a `conversion_factor` is provided, the public UI may offer a simple, client-side or Livewire-driven toggle to view the range in the alternative unit.

Formula:
Alternative Value = Base Value * Conversion Factor

## 4.2 Rules

- Conversions must be clearly labeled with both units.
- If no conversion factor is provided, the toggle must be hidden.
- Rounding for converted values should match standard laboratory reporting (usually 1 or 2 decimal places).

---

# 5. Admin Management

Administrators with `reference-ranges.create` and `reference-ranges.edit` permissions can manage reference ranges.

## 5.1 Reference Range Form

The admin form must include:
- Common resource fields (name, slug, status, short_description).
- Analyte & Methodology section (analyte_name, specimen_type, methodology).
- Demographics section (population_group, age_min, age_max, sex).
- Values section (unit, lower_limit, upper_limit, critical_low, critical_high, conversion_factor).
- Metadata section (source_citation, effective_date).
- Taxonomy assignment.

## 5.2 Data Entry UX

Because entering reference ranges one by one is tedious, the Admin UI should support:
- "Save and Create Another" button for rapid entry of variations.
- Clear numeric input steps to prevent formatting errors.

*(Note: Bulk CSV import is deferred to a future phase to keep MVP scope manageable).*

## 5.3 Audit Logging

Changes to critical limits (`critical_low`, `critical_high`) and reference intervals must be strictly audited. The AuditLogService must capture the old and new values for these specific fields.

---

# 6. Public Display & Search

## 6.1 Public Listing Page

- Route: `/reference-ranges`
- Displays a searchable list of unique analytes (grouped by `analyte_name`).
- Clicking an analyte navigates to the detail page showing all demographic variations.

## 6.2 Public Detail Page

- Route: `/reference-ranges/{slug}` (where slug represents the analyte group, or specific record if 1:1).
- Displays the mandatory educational disclaimer prominently at the top.
- Displays the data table of variations.
- Displays source citations.
- Shows related Calculators or Interpretation resources via shared tags.

## 6.3 Mandatory Disclaimer

Every public Reference Range page must display the following disclaimer (or similar approved text):

> **Educational Reference Only:** Reference ranges vary significantly between laboratories based on the specific assay, instrument, and patient population. Always use the reference range provided by the testing laboratory on the patient's specific lab report. This data is for educational purposes and must not be used for clinical diagnosis or treatment decisions.

## 6.4 Search Integration

- Reference ranges must be highly searchable by `analyte_name`.
- The SearchService provider for reference ranges should match against `analyte_name`, `name`, and `short_description`.

---

# 7. Testing & Verification

## 7.1 Data Integrity Tests

- Test that `lower_limit` is strictly less than `upper_limit` (if both are provided).
- Test that `critical_low` is lower than `lower_limit` (if both are provided).
- Test that `critical_high` is higher than `upper_limit` (if both are provided).
- Test that `age_min` is less than or equal to `age_max`.

## 7.2 Feature Tests

- Test that the public page displays the mandatory disclaimer.
- Test that demographic variations are grouped correctly on the public page.
- Test that unpublished ranges are not accessible.
- Test that audit logs capture changes to critical limits.

---

# Document Completion Check

## Requirements Covered

- Reference Range data model defined.
- Demographic and methodological variations strategy defined.
- Unit and conversion factor strategy defined.
- Admin management patterns defined.
- Public display, search, and disclaimer requirements defined.
- Testing and data integrity verification defined.

## Decisions Established

- Reference Ranges use a flat table with demographic columns rather than a complex relational tree for MVP.
- Grouping on the public page is handled by matching `analyte_name` or tags.
- Unit conversions are handled via a `conversion_factor` column.
- A mandatory educational disclaimer is required on all public Reference Range pages.
- Bulk CSV import is deferred to a future phase.

## Dependencies

- Approved Phase 03 Resource Foundation.
- Approved Phase 03 Admin CRUD Patterns.
- Approved Phase 03 Public Page Patterns.

## Files Affected

This document creates:

04_Content_and_Reference_Architecture/01_Reference_Ranges_Architecture.md

It dictates the implementation of the reference_ranges table, ReferenceRange model, and public/admin interfaces.

## Open Questions

1. Should the public URL structure be `/reference-ranges/{analyte-slug}` (grouping all variations) or `/reference-ranges/{specific-variation-slug}`? (Recommendation: Group by analyte slug for better UX).
2. Should we implement a simple client-side unit converter toggle for the MVP?

## TBD Decisions

- TBD: Final URL routing strategy for grouped analytes.
- TBD: Final decision on client-side unit conversion UI.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE04-DOC01

## Context

LABGOTO requires a structured architecture for laboratory reference intervals. This document defines the data model, demographic variations, unit conversions, and mandatory safety disclaimers.

This prompt implements the documentation outcome of the Reference Ranges Architecture document.

## Objective

Create the official LABGOTO Reference Ranges Architecture document inside the approved Phase 04 documentation structure.

After this prompt is completed, the repository will have a binding specification for the Reference Ranges vertical slice.

## Existing Architecture

Relevant approved decisions:

- Resources use the common lifecycle and RBAC patterns.
- Reference ranges are educational data, not diagnostic engines.
- Demographic variations are stored in a single flat table.
- Mandatory disclaimers are required on public pages.

## Files to Inspect

Inspect, if available:

- docs/
- 04_Content_and_Reference_Architecture/
- 03_Resource_Architecture/01_Resource_Foundation.md

## Files to Create

Create:

04_Content_and_Reference_Architecture/01_Reference_Ranges_Architecture.md

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
- Ensure the mandatory disclaimer requirement is clearly documented.

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
- The file clearly defines the data model and demographic columns.
- The file defines the mandatory disclaimer requirement.

## Integration Requirements

This document must be used as the primary reference when building the Reference Ranges vertical slice.

## Definition of Done

This implementation is complete when:

- The Reference Ranges Architecture file exists.
- The file matches the approved Phase 04 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.