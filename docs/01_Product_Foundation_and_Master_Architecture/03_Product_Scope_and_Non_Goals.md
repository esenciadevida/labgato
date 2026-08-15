# 03 — Product Scope and Non-Goals

## Depends On

This document depends on:

- The approved LABGOTO Master Software Project directive.
- The approved Project Charter.
- The approved Current State Assessment.
- The decision that LABGOTO is the renamed continuation of MLS Toolkit.
- The decision to supersede the previous laboratory business/quote/lead-generation brief.
- The Hostinger PHP 8.4 hosting constraint.

## Defines

This document defines:

- The approved product scope for LABGOTO.
- The current scope boundaries.
- The approved resource categories.
- The approved functional boundaries.
- The explicit non-goals.
- The excluded systems and features.
- The rules for future scope changes.
- The relationship between current scope and future scope.

## Affects

This document affects:

- All future requirements documents.
- All future information architecture documents.
- All future content model documents.
- All future database architecture documents.
- All future API architecture documents.
- All future admin portal documents.
- All future AI documents.
- All future implementation prompts.
- All future feature requests.
- All future change requests.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes what may be built.
- Establishes what must not be built without approval.
- Prevents scope creep.
- Guides vertical-slice selection.
- Protects the project from unrelated healthcare system features.

---

# 1. Purpose

This document defines the official scope of LABGOTO.

It exists to ensure that future documentation, design, development, testing, and deployment remain aligned with the approved product definition.

LABGOTO must be built as a controlled professional laboratory toolkit.

It must not expand into unrelated healthcare, clinical, business, or patient-management systems without an approved architectural change request.

---

# 2. Approved Product Scope

LABGOTO is approved as a digital toolkit/workspace for laboratory professionals and related users.

The approved scope includes resources and utilities that support:

- Laboratory work
- Laboratory learning
- Laboratory reference lookup
- Laboratory calculations
- Laboratory procedure reference
- Laboratory result interpretation education
- Laboratory knowledge discovery
- Controlled administrative management
- Controlled AI assistance where approved

The platform should make laboratory work and learning:

- Faster
- Easier
- More organized
- More accessible
- More practical

---

# 3. Approved User Scope

The primary approved users are:

- Medical Laboratory Scientists
- Medical Laboratory Science Students
- Laboratory Technicians
- Other relevant laboratory professionals

Additional user categories may be approved later.

Public user accounts are not confirmed by default.

Administrator accounts are expected because the platform requires an Admin Portal.

The final user account model must be documented in later architecture documents.

---

# 4. Approved Resource Scope

LABGOTO may include the following approved resource types.

Each resource type must be documented separately before implementation.

## 4.1 Laboratory Tools

Approved as part of LABGOTO.

Laboratory tools are interactive utilities that support practical laboratory tasks.

The exact tools must be defined and approved before implementation.

## 4.2 Calculators

Approved as part of LABGOTO.

Calculators are validated laboratory calculation utilities.

Calculators must have:

- Defined inputs
- Defined units
- Defined logic or formula
- Defined outputs
- Validation rules
- Reference information
- Testing against expected results
- Versioning where appropriate

Calculators must not execute arbitrary code.

## 4.3 Reference Ranges

Approved as part of LABGOTO.

Reference ranges are structured laboratory reference records.

They may include:

- Test or analyte
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

The final reference range architecture must be documented before implementation.

## 4.4 SOP Resources

Approved as part of LABGOTO.

SOP resources are laboratory procedure and reference resources.

They may support:

- Drafting
- Review
- Publishing
- Archiving
- Versioning
- Categorization
- Search
- Metadata
- Access control

The final SOP architecture must be documented before implementation.

## 4.5 Result Interpretation Resources

Approved as part of LABGOTO.

Result interpretation resources are educational and reference-oriented materials.

They must not be presented as replacements for professional clinical judgment.

They must not function as diagnostic decision engines.

## 4.6 Knowledge and Education Resources

Approved as part of LABGOTO.

Knowledge and education resources may include:

- Laboratory science explanations
- Study resources
- Topic references
- Procedural explanations
- Educational articles
- Other approved laboratory learning content

The final content model must be documented before implementation.

## 4.7 Search

Approved as part of LABGOTO.

Search must provide controlled discovery across approved LABGOTO resources.

Search may include:

- Resource search
- Category browsing
- Filtering where appropriate
- Search result pages
- Search analytics where approved

The final search architecture must be documented before implementation.

## 4.8 AI Assistance

Approved as a future controlled feature.

AI must not be built before the underlying resource architecture is sufficiently mature.

AI must be:

- Controlled
- Knowledge-bound
- Provider-independent where practical
- Secure
- Rate-limited
- Logged where appropriate
- Explicitly limited in authority

AI must not invent information.

AI must not act as the authorization layer.

AI must not override backend security rules.

## 4.9 Admin Portal

Approved as part of LABGOTO.

The Admin Portal must be a serious internal application.

It must support management of approved platform resources.

The exact admin modules must follow later approved documentation.

---

# 5. Functional Scope

The following functional areas are within the general scope of LABGOTO, subject to detailed architecture.

## 5.1 Public Resource Discovery

LABGOTO may provide public pages for approved resources.

These may include:

- Home page
- Resource listing pages
- Resource detail pages
- Category pages
- Search pages
- Legal pages
- Error pages

All public pages must be intentional and connected to the information architecture.

## 5.2 Resource Management

Administrators may manage approved resources through the Admin Portal.

Resource management may include:

- Create
- Read
- Update
- Delete where allowed
- Publish
- Archive
- Version control where required
- Categorization
- Status management
- SEO metadata where appropriate

All management actions must be authorized server-side.

## 5.3 User and Administrator Management

Administrator management is within scope.

Public user account management is TBD unless approved later.

If public user accounts are approved, the architecture must define:

- Registration
- Login
- Account verification
- Password reset
- Profile management
- Account deactivation
- Privacy controls
- Session controls
- Data retention rules

No public user account feature should be implemented until approved.

## 5.4 Authentication and Authorization

Authentication and authorization are within scope.

The system must implement:

- Secure login for administrators
- Secure session handling
- Role-based access control
- Permission enforcement
- Server-side authorization
- Audit logging for sensitive actions

Frontend-only restriction is not sufficient.

## 5.5 Audit Logging

Audit logging is within scope.

Audit logs may record:

- Login events
- Failed login events
- Logout events
- Administrator creation
- Role changes
- Permission changes
- Content changes
- Security-setting changes
- Account deactivation
- Sensitive administrative operations

Audit logs must be protected.

## 5.6 Analytics

Privacy-conscious analytics are within scope.

Analytics may track:

- Tool opened
- Tool completed
- Calculator used
- Resource viewed
- Search performed
- Category viewed
- AI interaction where approved
- Account creation where approved
- Account deactivation where approved

Analytics must not collect unnecessary sensitive information.

## 5.7 SEO

SEO is within scope for public resources.

SEO must include:

- Metadata
- Canonical URLs
- Heading structure
- Open Graph data
- Structured data where appropriate
- Sitemap generation
- Robots configuration
- Intentional indexing rules

Admin and private areas must not be indexable.

## 5.8 Accessibility

Accessibility is within scope.

LABGOTO should target WCAG 2.2 AA where practical and applicable.

Accessibility must be considered for:

- Navigation
- Forms
- Calculators
- Tables
- Dialogs
- Search
- Admin workflows
- Error messages
- Keyboard interaction
- Screen reader support
- Touch targets
- Reduced motion

## 5.9 Security

Security is within scope at all layers.

Security must include:

- Authentication security
- Authorization security
- Input validation
- Injection protection
- Session security
- API security
- File upload security
- Environment security
- Dependency security
- Threat modeling
- Secure logging
- AI security where AI is implemented

## 5.10 Testing

Testing is within scope.

Testing may include:

- Unit tests
- Integration tests
- Feature tests
- E2E tests
- Security tests
- Accessibility tests
- Regression tests
- Calculator validation tests

No feature is complete without appropriate testing.

## 5.11 Deployment

Deployment is within scope.

Deployment must respect the Hostinger PHP 8.4 shared-hosting constraint.

Deployment documentation must define:

- Environments
- Configuration
- Build process
- Migration process
- Backup process
- Rollback considerations
- Security checks
- Verification steps

---

# 6. Current Scope Boundaries

The current scope is limited to the approved laboratory toolkit direction.

The current scope does not assume the existence of:

- Existing code
- Existing documentation
- Existing database
- Existing content
- Existing analytics
- Existing AI configuration
- Existing user base
- Existing MLS Toolkit implementation

These may be added or reused only if provided and verified.

---

# 7. MVP Direction

The LABGOTO MVP should be small, complete, and useful.

The MVP should not attempt to deliver every approved resource type at once.

The MVP should preferably deliver:

- Application foundation
- Secure admin authentication
- Basic admin portal
- One complete resource type or tool category
- Public listing page
- Public detail page
- Search or category navigation where appropriate
- SEO foundation
- Accessibility foundation
- Analytics foundation
- Testing foundation

The exact MVP definition must be documented in a later phase.

The first vertical slice should establish the reusable architecture for future resources.

---

# 8. Future Scope

Future scope may include additional approved resource types and capabilities.

Possible future areas include:

- Additional calculators
- Additional laboratory tools
- Expanded reference range system
- Expanded SOP system
- Expanded knowledge/education system
- Public user accounts
- Saved favorites
- Personal study tools
- Advanced search
- AI assistant
- AI-powered explanation
- Export tools
- Print-friendly resource views
- Additional admin analytics
- Additional role types
- Additional content workflows

Future scope must not be implemented until it is explicitly approved and documented.

---

# 9. Non-Goals

The following are explicit non-goals unless formally approved through a change request.

## 9.1 Laboratory Information System

LABGOTO is not a Laboratory Information System.

It must not include:

- Specimen accessioning
- Specimen tracking
- Laboratory order management
- Result entry and validation
- Instrument interfacing
- Laboratory workflow automation
- Quality control workflow unless separately approved
- Clinical case management

## 9.2 Hospital Management System

LABGOTO is not a hospital management system.

It must not include:

- Patient admission management
- Ward management
- Billing management
- Insurance management
- Bed management
- Hospital staff scheduling
- Hospital-wide clinical records

## 9.3 Patient Management System

LABGOTO is not a patient management system.

It must not include:

- Patient registration
- Patient demographic records
- Patient appointment management
- Patient communication management
- Patient treatment records
- Patient case management

## 9.4 Patient Result Portal

LABGOTO is not a patient result portal unless explicitly added later.

It must not include:

- Patient login for result access
- Result delivery to patients
- Patient-facing report download
- Patient notification for results
- Patient result history

## 9.5 Diagnostic Decision Engine

LABGOTO is not a diagnostic decision engine.

It must not:

- Diagnose conditions
- Recommend definitive clinical actions
- Replace professional clinical judgment
- Present itself as a medical decision-making authority
- Generate unauthorized clinical guidance

Educational interpretation resources may exist, but they must be clearly educational.

## 9.6 Generic Healthcare Website

LABGOTO is not a generic healthcare website.

It should not become:

- A general health blog
- A generic clinic website
- A generic hospital website
- A general medical advice website
- A broad wellness platform

## 9.7 Simply a Blog

LABGOTO may include educational content, but it is not simply a blog.

Content must support the laboratory toolkit purpose.

## 9.8 Simply an Educational Website

LABGOTO may include educational resources, but it is not simply an educational website.

It must also support practical laboratory tools, references, calculators, SOP resources, and professional utilities.

## 9.9 Simply a Calculator Website

LABGOTO may include calculators, but it is not simply a calculator website.

It must support a broader laboratory resource architecture.

---

# 10. Excluded Features Under Current Scope

The following features are excluded under current scope unless explicitly approved later.

## 10.1 Quote Wizard

Excluded.

The previous Quote Wizard concept belonged to the superseded laboratory business/lead-generation scope.

## 10.2 Lead Generation

Excluded.

Lead capture, lead management, and sales pipeline features are not part of the current LABGOTO scope.

## 10.3 Business Website Packages

Excluded.

Laboratory business packages, service pricing, and customer quotation features are not part of the current scope.

## 10.4 Payments

Excluded.

Payment processing, subscriptions, invoices, and checkout features are not part of the current scope unless explicitly approved later.

## 10.5 Appointments

Excluded.

Appointment booking, scheduling, home sample collection, and visit management are not part of the current scope.

## 10.6 Patient Accounts

Excluded.

Patient accounts, patient profiles, and patient result access are not part of the current scope.

## 10.7 Doctor or Referral Portals

Excluded.

Doctor accounts, referral portals, and external clinician dashboards are not part of the current scope.

## 10.8 Messaging Systems

Excluded unless explicitly approved later.

This includes:

- Internal messaging
- Patient messaging
- Doctor messaging
- WhatsApp integration
- SMS integration
- Email marketing systems

## 10.9 Clinical Result Entry

Excluded.

LABGOTO must not allow entry, validation, or delivery of clinical patient results unless explicitly approved through a future architectural change.

## 10.10 Multi-Tenant Laboratory Management

Excluded.

Multi-tenant laboratory management is not part of the current scope.

## 10.11 Unrelated AI Features

Excluded.

AI features must remain controlled, knowledge-bound, and aligned with the approved LABGOTO resource architecture.

Unrestricted chatbot behavior is excluded.

---

# 11. Content Boundaries

LABGOTO content must remain appropriate to its purpose.

Allowed content includes:

- Laboratory science education
- Laboratory reference information
- Laboratory procedure references
- Laboratory calculation utilities
- Laboratory tools
- Result interpretation education
- Professional study resources
- Platform documentation where appropriate

Content must not:

- Provide personalized medical diagnosis
- Provide personalized treatment instructions
- Replace professional clinical judgment
- Present unverified reference information as authoritative
- Collect unnecessary sensitive information
- Expose internal administrative content publicly

---

# 12. User Experience Boundaries

LABGOTO must feel like a professional scientific product.

The UI should be:

- Clean
- Intentional
- Accessible
- Fast
- Responsive
- Trustworthy
- Easy to navigate
- Appropriate for laboratory professionals

The UI must avoid:

- Fake statistics
- Dead controls
- Placeholder workflows
- Generic SaaS filler language
- Excessive decorative effects
- Unnecessary animation
- Confusing navigation
- Inconsistent component behavior

---

# 13. Technical Scope Boundaries

The technical scope must remain compatible with the approved hosting constraint.

Current technical constraints include:

- Hostinger PHP/HTML hosting
- PHP 8.4 availability
- Shared hosting limitations
- Pre-built frontend assets
- No guaranteed Redis
- No guaranteed Node.js runtime on server
- No guaranteed long-running workers

The final technology stack must be documented separately.

No technology should be treated as approved unless documented and approved.

---

# 14. Scope Change Rules

Any proposed addition to scope must follow the change-control process.

A scope change request must identify:

- Change ID
- Existing decision
- Proposed change
- Reason
- Impact
- Files affected
- Database impact
- API impact
- Security impact
- SEO impact
- Accessibility impact
- Migration plan
- Approval requirement

Scope changes must not be implemented silently.

---

# 15. Scope Acceptance Criteria

A feature may be considered for implementation only if:

- It aligns with the approved LABGOTO purpose.
- It is included in an approved document.
- It has a clear user or administrative need.
- It does not violate the non-goals.
- It does not require unsupported infrastructure.
- It can be implemented as a complete vertical slice.
- It can be tested.
- It can be secured.
- It can be made accessible.
- It can be documented.
- It can be maintained.

If a feature fails these criteria, it must not be implemented without approval.

---

# 16. Relationship to Previous Scope

The previous laboratory business/quote/lead-generation scope is superseded.

The following previous scope items are not active:

- Public laboratory business website
- Service packages
- Pricing management
- Quote Wizard
- AI Quote Assistant
- Lead capture
- Lead management
- Business contact settings as a primary product feature
- Customer quotation workflows

These may only return if approved through a formal change request.

---

# Document Completion Check

## Requirements Covered

- Approved product scope defined.
- Approved user scope defined.
- Approved resource scope defined.
- Functional scope defined.
- Current scope boundaries defined.
- MVP direction defined.
- Future scope defined.
- Non-goals defined.
- Excluded features defined.
- Content boundaries defined.
- Technical scope boundaries defined.
- Scope change rules defined.

## Decisions Established

- LABGOTO is a professional laboratory toolkit.
- The previous quote/lead/business scope is excluded unless re-approved.
- AI is future controlled functionality, not initial core build.
- Public user accounts are not confirmed by default.
- Administrator accounts are expected.
- The MVP should be small, complete, and vertical.
- Scope changes require a formal change request.

## Dependencies

- Approved LABGOTO master directive.
- Approved Project Charter.
- Approved Current State Assessment.
- Approved Phase 01 file tree.
- Hostinger hosting constraint.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/03_Product_Scope_and_Non_Goals.md

It affects all future requirements, architecture, and implementation documents.

## Open Questions

1. Which resource type should be included in the MVP?
2. Which specific calculator or tool should be built first?
3. Are public user accounts required in the future?
4. Should reference ranges be public or restricted?
5. Should SOP resources be public, protected, or admin-only?
6. What content is available for the first resource slice?
7. What analytics events are approved for MVP?
8. What legal jurisdiction applies to legal pages?
9. What admin roles are required initially?
10. What content review workflow is required initially?

## TBD Decisions

- TBD: MVP resource selection
- TBD: First vertical-slice feature
- TBD: Public user account requirement
- TBD: Reference range access model
- TBD: SOP access model
- TBD: Final analytics event list
- TBD: Final legal page requirements
- TBD: Final admin role requirements

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE01-DOC03

## Context

LABGOTO has adopted the new master directive. The previous quote/lead/business platform scope is superseded. The project requires an official scope document to control future feature development.

This prompt implements the documentation outcome of the Product Scope and Non-Goals document.

## Objective

Create the official LABGOTO Product Scope and Non-Goals document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository should contain a clear scope boundary for all future documentation and implementation work.

## Existing Architecture

Relevant approved decisions:

- LABGOTO is a professional laboratory toolkit.
- LABGOTO is the renamed continuation of MLS Toolkit.
- The previous quote/lead scope is superseded.
- AI is deferred until the resource architecture is sufficiently mature.
- Documentation must control implementation.
- Scope changes require formal approval.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- 01_Project_Charter.md
- 02_Current_State_Assessment.md
- Any existing scope documents
- Any existing feature lists
- Any existing MLS Toolkit documentation

If no files exist, record that the documentation structure is being initialized.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/03_Product_Scope_and_Non_Goals.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

If a documentation index already exists, add a reference to:

01_Product_Foundation_and_Master_Architecture/03_Product_Scope_and_Non_Goals.md

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
- The file clearly defines approved scope.
- The file clearly defines non-goals.
- The file does not treat the previous quote/lead scope as active.
- The file does not introduce unapproved features.

If the repository supports Markdown linting, run it.

## Integration Requirements

This document must be referenced by future Phase 01 documents where relevant.

It must also be referenced in the Phase 01 implementation map after all Phase 01 documents are completed.

## Definition of Done

This implementation is complete when:

- The Product Scope and Non-Goals file exists.
- The file matches the approved Phase 01 file tree.
- The file defines the approved scope.
- The file defines the explicit non-goals.
- The file records the previous scope as superseded.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
- No application code was changed.
- No secrets were committed.