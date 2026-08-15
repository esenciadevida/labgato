# 01 — Project Charter

## Depends On

This document depends on:

- The approved LABGOTO Master Software Project directive.
- The decision to follow the new directive.
- The decision that LABGOTO is the renamed continuation of MLS Toolkit.
- The Hostinger PHP 8.4 hosting constraint.
- The absence of provided existing documentation or implementation.

## Defines

This document defines:

- Official project identity.
- Product purpose.
- Primary audience.
- Approved product direction.
- Explicit non-goals.
- Core product areas.
- Development philosophy.
- Governance rules.
- Current project state.
- Initial decision log.
- Success criteria.
- Position of this document within the documentation system.

## Affects

This document affects:

- All future LABGOTO documentation.
- All future architectural decisions.
- All future implementation prompts.
- All future phase planning.
- All future scope discussions.
- All future change requests.
- All future conflict-resolution decisions.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the official project foundation and governance rules.
- All future implementation must remain consistent with this charter.

---

# 1. Project Identity

Project Name: LABGOTO

Previous Name: MLS Toolkit

Project Type: Professional laboratory digital toolkit/workspace

Current Status: Active

Documentation Phase: Phase 01 — Product Foundation and Master Architecture

Current Document: 01_Project_Charter.md

LABGOTO is the renamed continuation of MLS Toolkit.

All previously approved MLS Toolkit decisions remain valid as LABGOTO decisions unless explicitly changed through the approved change-control process.

LABGOTO must not be merged with:

- SMEvolve
- unrelated laboratory management systems
- LIS systems
- hospital management systems
- patient management systems
- unrelated healthcare applications
- other unrelated projects

---

# 2. Product Purpose

LABGOTO is a professional digital toolkit/workspace designed primarily for:

- Medical Laboratory Scientists
- Medical Laboratory Science Students
- Laboratory Technicians
- Other relevant laboratory professionals

The platform brings together practical laboratory resources, including:

- Laboratory calculators
- Laboratory tools
- Reference ranges
- SOP resources
- Laboratory references
- Result interpretation resources
- Educational resources
- Search
- AI-assisted discovery/explanation where approved
- Other laboratory utilities defined by approved requirements

The goal is to make laboratory work and learning:

- Faster
- Easier
- More organized
- More accessible
- More practical

LABGOTO should feel like a serious professional laboratory product, not a generic content website and not a decorative AI chatbot.

---

# 3. Approved Product Direction

LABGOTO must be built as one coherent system.

The system must follow this sequence:

1. Plan
2. Architect
3. Document
4. Build
5. Integrate
6. Test
7. Secure
8. Verify
9. Deploy

Development must be performed through controlled vertical slices.

A vertical slice must include all necessary layers for a feature to be considered complete:

- Public page
- UI
- Input validation
- Logic/service layer
- Backend
- Database where required
- Admin management where required
- Permissions
- Analytics
- SEO
- Accessibility
- Testing
- Security
- Integration
- Documentation

A feature is not complete merely because a frontend page exists.

---

# 4. What LABGOTO Is Not

LABGOTO is not:

- A Laboratory Information System
- A hospital management system
- A patient management system
- A patient result portal unless explicitly added later
- A replacement for professional clinical judgment
- A replacement for official workplace SOPs
- A diagnostic decision engine
- A generic healthcare website
- Simply a blog
- Simply an educational website
- Simply a calculator website

LABGOTO does not currently include:

- Patient accounts
- Doctor portals
- Result delivery
- Clinical workflow management
- Specimen tracking
- Billing systems
- Insurance systems
- Electronic medical records
- Hospital administration systems

These may only be added through an approved architectural change request.

---

# 5. Core Product Areas

The approved LABGOTO architecture must support the following resource types, subject to detailed requirements in later documents.

## 5.1 Laboratory Tools

Interactive laboratory utilities.

Examples may include:

- Unit converters
- Dilution helpers
- Calculation utilities
- Workflow aids
- Other practical laboratory tools

The exact tool list must be defined in later requirements documents.

## 5.2 Calculators

Validated laboratory calculations.

Calculators must have:

- Clearly defined inputs
- Clearly defined units
- Clearly defined formulas or logic
- Clearly defined outputs
- Validation rules
- Reference information
- Testing against known expected results
- Versioning where appropriate

Calculators must not execute arbitrary administrator-provided code.

## 5.3 Reference Ranges

Structured laboratory reference information.

Reference ranges may include:

- Test/analyte
- Population
- Age
- Sex
- Specimen type
- Unit
- Lower limit
- Upper limit
- Source
- Effective date
- Version
- Status
- Notes

Reference ranges must be managed through controlled admin workflows.

## 5.4 SOP Resources

Laboratory procedure/reference resources.

SOP resources may support:

- Drafting
- Review
- Publishing
- Archiving
- Versioning
- Categorization
- Search
- Metadata
- Access control

Historical versions should be preserved where required.

## 5.5 Result Interpretation

Educational/reference interpretation resources.

These resources must be clearly educational and reference-oriented.

They must not be presented as replacements for professional clinical judgment.

## 5.6 Knowledge and Education

Laboratory learning resources.

These may include:

- Explanatory content
- Study resources
- Topic references
- Procedural explanations
- Laboratory science educational material

The content model must be defined in later documentation.

## 5.7 Search

Central discovery across the platform.

Search should allow users to find:

- Tools
- Calculators
- Reference ranges
- SOP resources
- Interpretation resources
- Knowledge/education content
- Other approved resources

Search architecture must be defined in a later phase.

## 5.8 AI

Controlled AI assistance based on approved LABGOTO information.

AI must only be introduced after the underlying resource architecture is sufficiently mature.

AI must not act as the application’s authorization layer.

AI must not invent missing information.

AI must not expose internal system prompts, credentials, or unauthorized data.

## 5.9 Admin Portal

The Admin Portal must be a serious internal application.

It must not be treated as a simple statistics page.

It should support management of approved platform entities, including where applicable:

- Dashboard
- Users
- Administrators
- Roles
- Permissions
- Tools
- Calculators
- Categories
- Reference ranges
- SOPs
- Knowledge/content
- Analytics
- Search analytics
- AI configuration
- System settings
- SEO/content settings
- Audit logs

The exact admin module list must follow approved architecture.

---

# 6. Development Philosophy

LABGOTO must be developed according to the following principles.

## 6.1 Documentation First

Implementation must be guided by approved documentation.

Documentation must not be an afterthought.

However, documentation and build should progress in controlled parallel:

1. Document enough to make a system implementation-ready.
2. Build it.
3. Integrate it.
4. Verify it.
5. Continue documentation.

## 6.2 Vertical Slices

Implementation must be performed through complete vertical slices.

No disconnected pages.

No dead buttons.

No fake functionality.

No orphan routes.

No unfinished workflows.

## 6.3 No Architectural Guessing

If a decision is missing, it must be marked:

TBD — Architectural Decision Required

Do not silently invent:

- Technology
- Framework
- Database
- API structure
- Pages
- Routes
- Components
- User roles
- Features
- Database relationships
- Security policies

## 6.4 Stop on Conflict

If a new requirement conflicts materially with existing approved architecture:

1. Stop.
2. Identify the conflict.
3. Reference the existing decision.
4. Propose options.
5. Wait for approval.

Do not resolve major architectural conflicts through guesswork.

## 6.5 Security by Default

Security is a first-class architecture concern.

LABGOTO must implement appropriate:

- Authentication security
- Authorization security
- Input validation
- Injection protection
- Session security
- API security
- AI security
- File upload security
- Audit logging
- Threat modeling
- Dependency management
- Environment separation

Frontend restrictions are not security.

All authorization must be enforced server-side.

## 6.6 Accessibility by Default

LABGOTO should target WCAG 2.2 AA where practical and applicable.

Important interactions must work with:

- Keyboard
- Mouse
- Touch
- Screen reader where appropriate

Accessibility is part of the definition of done.

## 6.7 SEO by Default

Public resources must have intentional SEO architecture.

Admin/private areas must not be indexable.

Public indexable pages should have:

- Title
- Meta description
- Canonical URL
- Heading structure
- Open Graph data
- Structured data where appropriate
- Internal links

Sitemap and robots configuration must be intentional.

## 6.8 Professional UI

LABGOTO must avoid generic AI-style UI.

Avoid:

- Excessive gradients
- Glassmorphism everywhere
- Random floating shapes
- Excessive shadows
- Giant meaningless headings
- Repetitive decorative card grids
- Fake statistics
- Excessive rounded cards
- Decorative icons everywhere
- Excessive animation
- Generic SaaS filler copy
- Unnecessary badges

The UI must be intentional, professional, and appropriate for a laboratory/scientific product.

## 6.9 Real States

Every data-driven feature must define:

- Loading state
- Empty state
- Error state
- Success state
- Validation state

No feature is complete without these states.

## 6.10 Testing and Verification

Testing must include, where appropriate:

- Unit tests
- Integration/feature tests
- E2E tests
- Security tests
- Accessibility tests
- Regression tests

Calculators must be tested against known expected outputs.

No regressions should be accepted when moving to the next feature.

---

# 7. Hosting Constraint

The following hosting constraint is currently approved:

- Hosting Provider: Hostinger
- Hosting Type: PHP/HTML hosting
- PHP Version: 8.4
- Backend Direction: Laravel may be used, subject to final technology stack approval
- Database Direction: MySQL/MariaDB preferred, subject to final approval

Shared hosting constraints must be respected.

Assumed shared-hosting limitations unless verified otherwise:

- No guaranteed Redis
- No guaranteed Node.js runtime on server
- No guaranteed Supervisor
- No guaranteed long-running workers
- Possible SSH limitations depending on plan
- Cron availability depends on plan
- Public web root may be public_html
- Frontend assets should be pre-built

Detailed deployment constraints will be documented in:

01_Product_Foundation_and_Master_Architecture/08_Deployment_Constraints.md

---

# 8. Governance Rules

LABGOTO must follow the approved source-of-truth hierarchy.

## 8.1 Source-of-Truth Hierarchy

Level 1: Latest approved architectural decision.

Level 2: Current master architecture.

Level 3: Latest approved file tree.

Level 4: Latest feature-specific documentation.

Level 5: Earlier documentation.

Level 6: AI assumptions.

AI assumptions must never override approved project decisions.

If two high-level decisions conflict, do not guess.

Identify the conflict and stop the affected implementation until the conflict is resolved.

## 8.2 Change-Control Rule

Any proposed architectural change must be documented using a change request.

A change request must identify:

- Change ID
- Existing Decision
- Proposed Change
- Reason
- Impact
- Files Affected
- Database Impact
- API Impact
- Security Impact
- SEO Impact
- Accessibility Impact
- Migration Plan
- Approval Requirement

Never silently change architecture.

## 8.3 Documentation Phase Rule

Before every documentation phase:

- Show file tree.
- Show file purposes.
- Show total file count.

Do not begin writing the phase documents until the file tree is approved.

Do not silently add extra files halfway through the phase.

## 8.4 Document Dependency Rule

Every document must identify:

- Depends On
- Defines
- Affects
- Implementation Impact

## 8.5 Document Completion Rule

Every document must end with:

- Document Completion Check
- Implementation Prompt

If a document does not directly produce code, the implementation prompt must describe the documentation/repository action required to implement the document’s outcome.

---

# 9. Current Project State

At the time of this document:

- Existing LABGOTO documentation: Not provided
- Existing MLS Toolkit documentation: Not provided
- Existing file tree: Not provided
- Existing implementation: Not provided
- Existing database schema: Not provided
- Existing tests: Not provided
- Existing deployment configuration: Not provided

Therefore, the working assumption is:

Greenfield documentation reset under the new LABGOTO master directive.

This assumption must be verified in:

01_Product_Foundation_and_Master_Architecture/02_Current_State_Assessment.md

If existing project assets are discovered, this document and the current-state assessment must be reviewed for conflicts.

---

# 10. Initial Decision Log

## Decision D-0001

Decision:

Adopt the new LABGOTO Master Software Project directive as the controlling product directive.

Status:

Approved.

Impact:

LABGOTO is defined as the renamed continuation of MLS Toolkit.

## Decision D-0002

Decision:

The previous laboratory business/quote/lead-generation brief is superseded.

Status:

Approved.

Impact:

Quote Wizard, lead generation, business packages, and laboratory business website scope are not part of the current LABGOTO scope unless explicitly re-added through a formal change request.

## Decision D-0003

Decision:

Because no existing documentation or implementation was provided, the project will proceed as a documentation reset unless existing project files are supplied.

Status:

Working assumption.

Impact:

Phase 01 documents will define the current state from available conversation context.

## Decision D-0004

Decision:

Hostinger PHP/HTML hosting with PHP 8.4 is a confirmed deployment constraint.

Status:

Approved.

Impact:

Architecture must remain compatible with shared PHP hosting limitations.

## Decision D-0005

Decision:

AI functionality must not be built before the underlying resource architecture is sufficiently mature.

Status:

Approved.

Impact:

AI is deferred until tools, references, SOPs, knowledge, search, and admin foundations are sufficiently defined.

---

# 11. Success Criteria

LABGOTO will be considered successful when it provides:

- Useful laboratory tools
- Validated calculators
- Structured reference ranges
- Controlled SOP resources
- Result interpretation resources
- Knowledge/education resources
- Reliable search
- Controlled AI assistance where approved
- Secure admin management
- Professional UI
- Accessible UX
- SEO-friendly public resources
- Tested functionality
- Maintainable architecture
- Clear documentation

Success is not measured by the number of generated pages.

Success is measured by the presence of complete, connected, verified, production-ready functionality.

---

# 12. Phase Position

This document is part of:

Phase 01 — Product Foundation and Master Architecture

This document is:

Document 01 of 13

The next document is:

02_Current_State_Assessment.md

---

# Document Completion Check

## Requirements Covered

- Project identity defined.
- Previous project name recorded.
- Product purpose defined.
- Target audience defined.
- Core product areas defined.
- Non-goals defined.
- Development philosophy defined.
- Governance rules defined.
- Hosting constraint recorded.
- Initial decision log created.
- Current state noted.
- Success criteria defined.

## Decisions Established

- LABGOTO follows the new master directive.
- LABGOTO is the renamed continuation of MLS Toolkit.
- Previous quote/lead/business website scope is superseded.
- Hostinger PHP 8.4 is a deployment constraint.
- AI is deferred until underlying architecture is mature.
- Documentation-first and vertical-slice development is mandatory.

## Dependencies

- Approved LABGOTO master directive.
- User approval of new directive.
- Approved Phase 01 file tree.
- Hostinger hosting constraint.
- Absence of existing documentation/code, unless later corrected.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/01_Project_Charter.md

It affects all future LABGOTO documentation files.

## Open Questions

1. Does any existing MLS Toolkit documentation or code exist?
2. Does any existing LABGOTO documentation or code exist?
3. What is the approved final technology stack?
4. Are public user accounts required in the MVP?
5. Are administrator accounts the only required accounts initially?
6. What is the first complete laboratory tool to build?
7. What is the first calculator to build?
8. What legal jurisdiction should legal pages reference?
9. What analytics events are approved for MVP?
10. What content is available for reference ranges, SOPs, and educational resources?

## TBD Decisions

- TBD: Final backend framework version confirmation
- TBD: Final frontend architecture confirmation
- TBD: Final database engine confirmation
- TBD: Final session/cache/queue drivers
- TBD: Final authentication implementation approach
- TBD: Final RBAC package or custom implementation approach
- TBD: Final analytics approach
- TBD: Final legal page content and jurisdiction review
- TBD: First vertical-slice tool selection

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE01-DOC01

## Context

LABGOTO has adopted the new master directive. The previous quote/lead/business platform scope is superseded. The project requires a controlled documentation foundation before application implementation begins.

This prompt implements the documentation outcome of the Project Charter.

## Objective

Create the official LABGOTO Project Charter document inside the approved documentation structure.

After this prompt is completed, the repository should contain the approved Phase 01 folder and the first charter document.

## Existing Architecture

Relevant approved decisions:

- LABGOTO is the renamed continuation of MLS Toolkit.
- LABGOTO is a professional laboratory toolkit.
- The previous quote/lead scope is superseded.
- Documentation must be phase-controlled.
- File trees must be approved before phase writing.
- Every document must identify dependencies, definitions, effects, and implementation impact.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- Any existing README.md
- Any existing project charter
- Any existing MLS Toolkit documentation

If no files exist, record that the documentation structure is being initialized.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/01_Project_Charter.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

If a documentation index already exists, add a reference to:

01_Product_Foundation_and_Master_Architecture/01_Project_Charter.md

Do not modify application code.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets.
- Do not include API keys.
- Do not include database credentials.
- Do not include hosting credentials.
- Do not include personal data.
- Do not expose private infrastructure details beyond what is necessary.

## Accessibility Requirements

The Markdown document should use:

- Clear heading hierarchy.
- Readable list structure.
- No decorative-only content.
- No ambiguous link text.

## SEO Requirements

No public SEO impact.

This is an internal/documentation file.

Do not make admin or private documentation indexable if later served by the application.

## Analytics Requirements

No analytics events are required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the required dependency section.
- The file contains the required completion check.
- The file contains the implementation prompt.
- The file does not accidentally treat the previous quote/lead scope as active.

If the repository supports Markdown linting, run it.

## Integration Requirements

This document must be referenced by future Phase 01 documents where relevant.

It must also be referenced in the Phase 01 implementation map after all Phase 01 documents are completed.

## Definition of Done

This implementation is complete when:

- The Project Charter file exists.
- The file matches the approved Phase 01 file tree.
- The file records the new LABGOTO directive as authoritative.
- The file records the previous quote/lead scope as superseded.
- The file records the Hostinger PHP 8.4 constraint.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
- No application code was changed.
- No secrets were committed.