# 05 — Core Product Areas

## Depends On

This document depends on:

- The approved LABGOTO Master Software Project directive.
- The approved Project Charter.
- The approved Product Scope and Non-Goals document.
- The approved Target Users and Journeys document.
- The decision that LABGOTO is a professional laboratory toolkit.

## Defines

This document defines:

- The conceptual boundaries of each approved core product area.
- The required data attributes for each resource type.
- The functional requirements for each resource type.
- The quality and validation requirements for scientific resources.
- The administrative management requirements for each area.
- The explicit restrictions applied to each area.

## Affects

This document affects:

- The Core Content Model (Document 11).
- The Database Architecture (future phases).
- The API Architecture (future phases).
- The Information Architecture (Document 10).
- The Admin Portal Specification (future phases).
- The Testing Strategy (future phases).
- The Accessibility Requirements for complex interactive elements.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the exact data structures and functional boundaries for the platform's main features.
- Prevents feature overlap (e.g., confusing a general tool with a validated clinical calculator).
- Ensures scientific accuracy and safety constraints are built into the foundation.
- Guides the creation of specific vertical-slice implementation prompts in later phases.

---

# 1. Purpose

This document details the approved core product areas of LABGOTO.

While the Product Scope document defines what is included and excluded at a high level, this document defines the internal mechanics, data requirements, and quality constraints for each specific resource type.

This ensures that when the database schema and admin interfaces are designed, they are perfectly tailored to the unique needs of laboratory science resources.

---

# 2. Laboratory Tools

Laboratory Tools are interactive, practical utilities used for daily laboratory operations, preparation, and general calculations that do not necessarily involve direct patient diagnostic formulas.

## 2.1 Examples

- Dilution calculators (e.g., C1V1 = C2V2).
- Unit converters (e.g., mg/dL to mmol/L).
- Centrifugation converters (e.g., RPM to RCF/g-force).
- Buffer and reagent preparation calculators.
- Sample size or aliquot calculators.

## 2.2 Functional Requirements

- Must accept user inputs with defined units.
- Must perform instant or on-submit calculations.
- Must display the result clearly with the correct output units.
- Must provide a brief explanation of how the tool works.
- Must allow users to clear or reset inputs easily.

## 2.3 Data Attributes

- Tool Name
- Slug/URL
- Category (e.g., Preparation, Conversion, General)
- Short Description
- Detailed Instructions
- Input Definitions (Label, Unit, Validation Rules)
- Output Definitions (Label, Unit)
- Underlying Logic/Formula (Securely stored, not executable arbitrary code)
- References/Citations
- Status (Draft, Published, Archived)

## 2.4 Restrictions

- Must not execute arbitrary administrator-provided code (e.g., no raw PHP/JS eval).
- Must not store patient identifiable information (PII).
- Must handle edge cases (e.g., division by zero, negative volumes) gracefully with clear validation errors.

---

# 3. Calculators

Calculators are validated laboratory and clinical formulas, often used in hematology, chemistry, immunology, and microbiology to derive specific indices or corrected values.

## 3.1 Examples

- Hematology: MCV, MCH, MCHC, Absolute Neutrophil Count (ANC), Corrected Reticulocyte Count.
- Chemistry: Anion Gap, Corrected Calcium, eGFR, Osmolality.
- Microbiology: Colony Forming Units (CFU) calculator.

## 3.2 Functional Requirements

- Must strictly enforce input types and ranges (e.g., preventing biologically impossible values).
- Must display the formula or methodology used for transparency.
- Must provide standard reference ranges for the calculated output where applicable.
- Must include clinical/laboratory notes or caveats (e.g., "Corrected Calcium is less accurate in severe hypoalbuminemia").

## 3.3 Data Attributes

- Calculator Name
- Slug/URL
- Category (e.g., Hematology, Clinical Chemistry)
- Formula/Equation (Display format)
- Input Parameters (Name, Unit, Min/Max limits, Required/Optional)
- Output Parameter (Name, Unit, Expected Reference Range)
- Interpretation Notes
- Scientific References (Textbooks, Journals, Guidelines)
- Version Number
- Status (Draft, Published, Archived)

## 3.4 Quality and Testing Constraints

- Every calculator must have documented expected outputs for a set of known inputs.
- Automated unit tests must be written for every calculator to verify mathematical accuracy.
- Rounding rules must be explicitly defined and consistent (e.g., round to 1 decimal place for MCV).

---

# 4. Reference Ranges

Reference Ranges provide structured, searchable data for standard laboratory test intervals.

## 4.1 Functional Requirements

- Must support multiple populations (e.g., Adult, Pediatric, Neonatal).
- Must support demographic variations (e.g., Male, Female, Pregnant).
- Must support different specimen types (e.g., Serum, Plasma, Whole Blood, Urine).
- Must support different unit systems (e.g., SI Units vs. Conventional Units) with conversion factors.
- Must clearly display the source of the reference range.

## 4.2 Data Attributes

- Analyte/Test Name (e.g., Alanine Aminotransferase - ALT)
- Slug/URL
- Specimen Type
- Methodology/Platform (optional, e.g., "Roche Cobas")
- Population Group
- Age Range (Min/Max)
- Sex
- Unit of Measure
- Lower Limit
- Upper Limit
- Critical Low Value (Panic value)
- Critical High Value (Panic value)
- Conversion Factor (for unit switching)
- Source/Citation
- Effective Date
- Status (Draft, Published, Archived)

## 4.3 Administrative Requirements

- Administrators must be able to update ranges when new guidelines are published.
- Historical versions of reference ranges should be preserved or clearly marked with effective dates to prevent confusion.

---

# 5. SOP Resources (Standard Operating Procedures)

SOP Resources provide structured, version-controlled procedural documentation for laboratory workflows, aligned with general quality management principles (e.g., ISO 15189 concepts).

## 5.1 Functional Requirements

- Must support document control metadata (Document ID, Version, Author, Reviewer).
- Must support structured content sections (Purpose, Scope, Responsibilities, Safety, Procedure, Quality Control, References).
- Must support status workflows (Draft, Under Review, Approved/Published, Obsolete/Archived).
- Must clearly display the current version and the next scheduled review date.

## 5.2 Data Attributes

- Document Title
- Document ID / Code
- Slug/URL
- Category/Department (e.g., Phlebotomy, Hematology, Quality Assurance)
- Version Number
- Author
- Approved By
- Effective Date
- Next Review Date
- Content Body (Rich text or structured blocks)
- Attachments (e.g., forms, flowcharts - subject to file upload security rules)
- Status

## 5.3 Restrictions

- SOPs are reference materials. LABGOTO does not enforce real-world laboratory compliance; it only provides the documented reference.
- File uploads for SOP attachments must be strictly validated for type and size.

---

# 6. Result Interpretation Resources

Result Interpretation resources are educational guides explaining the clinical and laboratory significance of specific test results, biomarkers, or patterns.

## 6.1 Functional Requirements

- Must explain what the test measures.
- Must explain the significance of high/low results.
- Must list common interfering factors (e.g., hemolysis, lipemia, medications).
- Must include strong, clear disclaimers that the content is for educational and professional reference purposes only, and not for direct patient diagnosis.

## 6.2 Data Attributes

- Title (e.g., "Interpretation of Elevated Serum Lipase")
- Slug/URL
- Category
- Summary/Abstract
- Main Content (Structured with headings)
- Related Tests/Analytes (Linking to Reference Ranges)
- Disclaimer Text (Standardized or custom)
- References
- Status

---

# 7. Knowledge and Education

Knowledge and Education resources encompass general laboratory science articles, study guides, topic overviews, and professional development materials.

## 7.1 Functional Requirements

- Must support rich text formatting, images, and code blocks (if applicable for bioinformatics/tools).
- Must support categorization and tagging for easy filtering.
- Must support "Related Resources" linking to tools, calculators, or other articles.
- Must track basic view analytics (if approved).

## 7.2 Data Attributes

- Title
- Slug/URL
- Category
- Tags
- Author
- Content Body
- Featured Image
- Estimated Reading Time
- Status (Draft, Published, Archived)
- Publish Date

---

# 8. Search

Search is the central discovery engine across all approved LABGOTO resources.

## 8.1 Functional Requirements

- Must allow users to search across Tools, Calculators, Reference Ranges, SOPs, Interpretations, and Knowledge articles simultaneously or via specific filters.
- Must handle typos or partial matches gracefully (depending on the chosen search engine technology).
- Must display the resource type clearly in the search results (e.g., [Calculator], [SOP]).
- Must record search queries for administrative analytics (to understand what users are looking for and identify content gaps).

## 8.2 Technical Constraints

- Search must be optimized for the Hostinger shared hosting environment.
- Initial search will likely rely on optimized MySQL full-text search or scoped `LIKE` queries.
- External search engines (e.g., Meilisearch, Elasticsearch) are excluded from the MVP due to hosting constraints, unless a lightweight, shared-hosting-compatible alternative is approved.

---

# 9. AI Assistance (Future Controlled Feature)

AI Assistance is a planned feature that must only be implemented after the core resource architecture is stable.

## 9.1 Functional Requirements

- Must act as a controlled laboratory assistant.
- Must only use approved LABGOTO content (Knowledge Base) as its source of truth.
- Must cite the specific LABGOTO resource it used to generate an answer.
- Must refuse to answer questions outside its approved scope.

## 9.2 Restrictions

- Must not provide patient-specific diagnostic advice.
- Must not hallucinate reference ranges or formulas.
- Must not act as an authorization or security bypass.
- Must be provider-independent (architecture must support swapping LLM providers).
- Must be rate-limited to prevent abuse and control costs.

---

# 10. Admin Portal

The Admin Portal is the secure, internal workspace for managing the entire LABGOTO platform.

## 10.1 Functional Requirements

- Must provide a secure login with session management.
- Must provide a dashboard with real, honest metrics (no fake statistics).
- Must provide CRUD interfaces for all core product areas defined above.
- Must enforce Role-Based Access Control (RBAC).
- Must log sensitive administrative actions.

## 10.2 Module Requirements

The Admin Portal must include dedicated management modules for:

- Dashboard
- Laboratory Tools
- Calculators
- Reference Ranges
- SOP Resources
- Result Interpretations
- Knowledge/Education
- Categories and Tags
- Search Analytics
- Users and Administrators
- Roles and Permissions
- Audit Logs
- System Settings

---

# 11. Cross-Area Integration

The core product areas must not exist in isolation. They must be interconnected to provide a seamless professional experience.

## 11.1 Resource Linking

- A Reference Range should link to its corresponding Result Interpretation resource.
- A Result Interpretation should link to relevant Calculators and Reference Ranges.
- A Calculator should link to the underlying Reference Ranges for its inputs/outputs.
- Knowledge articles should link to relevant Tools, SOPs, and Calculators.

## 11.2 Unified Taxonomy

- Categories and Tags should be managed centrally where possible, or strictly mapped across resource types to ensure consistent filtering and search behavior.

---

# Document Completion Check

## Requirements Covered

- Detailed boundaries for Laboratory Tools defined.
- Detailed boundaries for Calculators defined.
- Detailed boundaries for Reference Ranges defined.
- Detailed boundaries for SOP Resources defined.
- Detailed boundaries for Result Interpretation defined.
- Detailed boundaries for Knowledge/Education defined.
- Search requirements defined.
- AI constraints defined.
- Admin Portal modules defined.
- Cross-area integration rules defined.

## Decisions Established

- Tools and Calculators are distinct but related concepts.
- Calculators require strict mathematical testing and rounding rules.
- Reference ranges must support complex demographic and unit variations.
- SOPs require document control metadata (versioning, review dates).
- Search must be compatible with shared hosting (no heavy external engines for MVP).
- AI is strictly deferred and constrained to approved knowledge.
- Resources must be deeply interconnected.

## Dependencies

- Approved LABGOTO master directive.
- Approved Project Charter.
- Approved Product Scope and Non-Goals.
- Approved Target Users and Journeys.
- Hostinger shared hosting constraint.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/05_Core_Product_Areas.md

It directly dictates the structure of the future Core Content Model (Document 11) and Database Architecture.

## Open Questions

1. What specific taxonomies/categories will be used to organize these resources?
2. How will unit conversions be handled globally vs. per-resource?
3. What rich-text editor will be used for SOPs and Knowledge articles?
4. How will historical versions of SOPs and Reference Ranges be stored in the database?
5. What specific calculators and tools will be included in the MVP vertical slice?

## TBD Decisions

- TBD: Final rich-text editor selection.
- TBD: Specific MVP resource selection.
- TBD: Database strategy for document versioning (SOPs/References).
- TBD: Global vs. local unit conversion architecture.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE01-DOC05

## Context

LABGOTO requires a detailed definition of its core product areas to ensure the database schema, admin interfaces, and public pages are built correctly. This document translates the high-level scope into specific functional and data requirements for each resource type.

This prompt implements the documentation outcome of the Core Product Areas document.

## Objective

Create the official LABGOTO Core Product Areas document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository will have a clear blueprint of the data attributes and functional rules for Tools, Calculators, References, SOPs, Interpretations, and Knowledge resources.

## Existing Architecture

Relevant approved decisions:

- LABGOTO is a professional laboratory toolkit.
- Calculators must be strictly validated and tested.
- SOPs require document control metadata.
- AI is deferred and strictly controlled.
- Search must respect shared hosting constraints.
- Resources must be interconnected.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- 01_Project_Charter.md through 04_Target_Users_and_Journeys.md
- Any existing MLS Toolkit feature lists or database schemas.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/05_Core_Product_Areas.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

If a documentation index already exists, add a reference to:

01_Product_Foundation_and_Master_Architecture/05_Core_Product_Areas.md

Do not modify application code.

## Database

No database changes at this time. This document defines the *requirements* for the future database schema.

## API

No API changes at this time.

## UI

No UI changes at this time.

## Security Requirements

For this documentation implementation:

- Do not include secrets.
- Do not include API keys.
- Do not include database credentials.
- Do not include personal data.

## Accessibility Requirements

The Markdown document should use:

- Clear heading hierarchy.
- Readable list structure.
- Consistent formatting for data attributes.

## SEO Requirements

No public SEO impact. This is an internal architecture document.

## Analytics Requirements

No analytics events required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the required dependency section.
- The file contains the required completion check.
- The file contains the implementation prompt.
- The file clearly distinguishes between Tools and Calculators.
- The file includes the necessary data attributes for each resource type.

## Integration Requirements

This document must be used as the primary reference when writing Document 11 (Core Content Model) and future Database Architecture documents.

## Definition of Done

This implementation is complete when:

- The Core Product Areas file exists.
- The file matches the approved Phase 01 file tree.
- The file defines the attributes and rules for all 9 core areas.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
- No application code was changed.