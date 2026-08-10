# 06 — Glossary

## Depends On

This document depends on:

- The approved LabGato Master Software Project directive.
- The approved Project Charter.
- The approved Product Scope and Non-Goals document.
- The approved Target Users and Journeys document.
- The approved Core Product Areas document.
- The approved Phase 01 file tree.

## Defines

This document defines:

- Official LabGato terminology.
- Approved resource vocabulary.
- Governance vocabulary.
- Security vocabulary.
- Accessibility vocabulary.
- SEO vocabulary.
- Documentation vocabulary.
- Implementation vocabulary.
- Deprecated or excluded terms that must not be confused with current scope.

## Affects

This document affects:

- All future LabGato documentation.
- All future naming conventions.
- Database naming decisions.
- API naming decisions.
- UI copy decisions.
- Admin portal labels.
- Code naming conventions.
- Implementation prompts.
- Communication about project scope.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Prevents ambiguity across documentation, code, and UI.
- Establishes consistent naming for future database tables, API resources, routes, and components.
- Reduces accidental scope creep caused by misunderstood terms.
- Provides a shared vocabulary for future implementation prompts.

---

# 1. Purpose

This glossary establishes the official vocabulary for LabGato.

All future documentation, implementation prompts, database names, API routes, UI labels, and architectural discussions should use these terms consistently.

If a new term becomes necessary, it must be added through documentation update or an approved change request.

If a term conflicts with an older project term, the latest approved LabGato terminology takes precedence.

---

# 2. Project Identity Terms

## LabGato

The official current project name.

LabGato is a professional digital toolkit/workspace for laboratory professionals and related learners.

## MLS Toolkit

The previous project name.

LabGato is the renamed continuation of MLS Toolkit.

Previously approved MLS Toolkit decisions remain valid unless explicitly changed.

## Professional Laboratory Toolkit

The approved product category for LabGato.

It means the platform provides practical laboratory tools, calculators, reference ranges, SOP resources, interpretation resources, educational resources, search, and controlled AI assistance where approved.

## Laboratory Professional

A general term for approved professional users of LabGato.

This includes:

- Medical Laboratory Scientists
- Medical Laboratory Science Students
- Laboratory Technicians
- Other relevant laboratory professionals

## Laboratory Resource

Any approved content or utility type inside LabGato.

Examples include:

- Laboratory tools
- Calculators
- Reference ranges
- SOP resources
- Result interpretation resources
- Knowledge and education resources

---

# 3. Core Product Area Terms

## Laboratory Tool

An interactive laboratory utility designed to support practical laboratory tasks.

Examples include:

- Unit converters
- Dilution helpers
- Reagent preparation utilities
- Centrifugation converters
- Other approved practical utilities

A Laboratory Tool is not necessarily a clinical formula calculator.

## Calculator

A validated laboratory calculation utility.

A Calculator has:

- Defined inputs
- Defined units
- Defined formula or logic
- Defined outputs
- Validation rules
- Expected results
- Reference information
- Versioning where appropriate

Calculators must be tested against known expected outputs.

Calculators must not execute arbitrary administrator-provided code.

## Reference Range

A structured laboratory reference record.

A Reference Range may include:

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

Reference ranges are reference data, not diagnostic commands.

## SOP Resource

A Standard Operating Procedure resource.

An SOP Resource is a structured procedural reference document.

It may include:

- Title
- Document ID
- Version
- Category
- Status
- Author
- Reviewer
- Effective date
- Review date
- Procedure content
- Safety information
- Quality control information
- References

SOP Resources are not clinical workflow enforcement tools.

## Result Interpretation Resource

An educational reference resource that explains how laboratory results may be interpreted.

Result Interpretation Resources must be clearly educational.

They must not act as diagnostic decision engines.

They must not replace professional clinical judgment.

## Knowledge Resource

An educational or informational laboratory resource.

Examples include:

- Study guides
- Laboratory science explanations
- Topic references
- Procedural explanations
- Educational articles

## Education Resource

A resource intended to support learning by laboratory professionals and students.

Education Resources are part of the broader Knowledge and Education product area.

## Search

The platform feature that allows users to discover approved LabGato resources.

Search may cover:

- Tools
- Calculators
- Reference ranges
- SOP resources
- Result interpretation resources
- Knowledge and education resources

Search architecture must be documented separately.

## AI Assistance

A future controlled feature that helps users discover or understand approved LabGato information.

AI Assistance must be:

- Knowledge-bound
- Secure
- Rate-limited
- Provider-independent where practical
- Explicitly limited in authority
- Logged where appropriate

AI Assistance must not invent information.

AI Assistance must not act as an authorization layer.

## Admin Portal

The secure internal area used to manage LabGato.

The Admin Portal is a serious internal application.

It is not a decorative dashboard.

It may manage:

- Resources
- Users
- Administrators
- Roles
- Permissions
- Analytics
- Audit logs
- System settings
- AI settings where approved
- SEO settings where appropriate

---

# 4. User and Role Terms

## Visitor

A user browsing public LabGato pages without authentication.

Visitor access depends on the approved public/private access model for each resource.

## Public User

A user with a public account.

Public User accounts are not confirmed in the current scope.

They must not be implemented unless explicitly approved.

## Administrator

An authenticated internal user who can access the Admin Portal.

Administrators must be protected by secure authentication and authorization.

## Super Administrator

A proposed high-privilege administrative role.

This role is not finalized until documented in the User Roles and RBAC document.

## Content Manager

A proposed administrative role focused on content creation and maintenance.

This role is not finalized until documented in the User Roles and RBAC document.

## Technical Administrator

A proposed administrative role focused on system, security, and technical configuration.

This role is not finalized until documented in the User Roles and RBAC document.

## Role

A named collection of permissions assigned to an administrative user.

Roles must follow the principle of least privilege.

## Permission

A specific allowed action.

Examples include:

- View resource
- Create resource
- Edit resource
- Publish resource
- Archive resource
- Manage users
- View audit logs
- Manage settings

Permissions must be enforced server-side.

## RBAC

Role-Based Access Control.

RBAC is the approved authorization model for LabGato.

It ensures users receive only the permissions necessary for their role.

## Principle of Least Privilege

The security rule that users should receive only the minimum permissions required to perform approved tasks.

---

# 5. Governance Terms

## Source of Truth

The approved authoritative information used to resolve conflicts.

For LabGato, the source-of-truth hierarchy is:

1. Latest approved architectural decision.
2. Current master architecture.
3. Latest approved file tree.
4. Latest feature-specific documentation.
5. Earlier documentation.
6. AI assumptions.

AI assumptions are the lowest level and must never override approved decisions.

## Approved Decision

A decision explicitly accepted by the project owner or recorded as approved in the official documentation.

## Decision Log

A record of approved project decisions.

The Decision Log must preserve decision history and prevent silent architectural changes.

## Change Request

A formal proposal to modify approved architecture, scope, naming, database design, API design, security policy, or implementation direction.

A Change Request must identify:

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

## Change ID

A unique identifier for a Change Request.

Example:

LABGATO-CR-0001

## Stop-on-Conflict

The mandatory rule that implementation must stop when a new requirement materially conflicts with existing approved architecture.

When a conflict occurs, the assistant must:

1. Identify the conflict.
2. Reference the existing decision.
3. Explain the new requirement.
4. Explain the technical conflict.
5. Provide possible solutions.
6. Recommend the best option.
7. Identify affected documentation/files.
8. Wait for approval.

## Architecture Decision Record

A document that records a significant architectural decision, its context, consequences, and approval status.

## Documentation Phase

A grouped set of approved documentation files.

Each phase must have:

- Phase overview
- File tree
- File purposes
- File count
- Document content
- Completion review
- Implementation map
- Build order where applicable

## File Tree

The approved list of folders and files for a documentation phase.

A documentation phase must not begin until its file tree is presented and approved.

## File Count

The total number of approved files in a documentation phase.

The file count must be stated explicitly.

## Implementation Prompt

A structured prompt derived from approved documentation that tells the coding assistant how to implement a feature or documentation action.

An Implementation Prompt must not invent architecture.

## Vertical Slice

A complete feature implementation across all required layers.

A Vertical Slice may include:

- UI
- Backend
- Database
- Validation
- Permissions
- SEO
- Accessibility
- Analytics
- Testing
- Documentation

## Definition of Done

The complete checklist that must be satisfied before a feature can be marked complete.

For LabGato, this includes:

- Architecture
- Documentation
- UI/UX
- Functionality
- Database
- API
- Validation
- Authorization
- Security
- Accessibility
- SEO
- Responsive behavior
- Analytics
- Testing
- Error states
- Loading states
- Empty states
- Integration
- Regression

---

# 6. Development Status Terms

## Not Started

Work has not begun.

## Architecture

Architectural planning is in progress.

## Documentation

Documentation is being written.

## Ready for Build

Documentation is complete and approved, and implementation may begin.

## In Development

Implementation is actively being built.

## Integration

The feature is being connected to the wider system.

## Testing

The feature is being tested.

## Security Review

The feature is undergoing security review.

## Accessibility Review

The feature is undergoing accessibility review.

## SEO Review

The feature is undergoing SEO review.

## Complete

The feature satisfies the full Definition of Done.

## Blocked

The feature cannot proceed because of a missing decision, conflict, dependency, or technical issue.

## Greenfield

A project state where no existing implementation or documentation has been provided and verified.

LabGato is currently proceeding as a documentation-controlled reset unless existing MLS Toolkit assets are provided.

---

# 7. User Interface Terms

## Page

A distinct URL-accessible view.

Examples include:

- Home page
- Tool listing page
- Calculator detail page
- Reference range page
- SOP detail page
- Search results page
- Admin dashboard page

## Component

A reusable UI element.

Examples include:

- Header
- Footer
- Navigation menu
- Search bar
- Resource card
- Table
- Form field
- Alert
- Pagination control

## Resource Card

A UI component used to summarize a resource in a listing.

A Resource Card may show:

- Title
- Resource type
- Short description
- Category
- Metadata
- Access link

## Empty State

The UI displayed when no data exists.

Empty States must be honest and useful.

They must not display fake data.

## Loading State

The UI displayed while data or an action is processing.

Loading States must communicate progress without misleading the user.

## Error State

The UI displayed when something fails.

Error States must be safe, clear, and accessible.

They must not expose sensitive technical information.

## Success State

The UI displayed after an action completes successfully.

## Validation State

The UI displayed when user input fails validation.

Validation messages must clearly explain what must be corrected.

## Dead UI

A button, link, tab, form, modal, filter, or control that does not work.

Dead UI is prohibited.

## Fake Functionality

A UI element that appears functional but does not perform a real action.

Fake Functionality is prohibited.

---

# 8. Content Lifecycle Terms

## Draft

A resource state where content is being prepared and is not publicly available.

## Under Review

A resource state where content is awaiting approval or quality review.

## Published

A resource state where content is approved and available according to the access model.

## Archived

A resource state where content is no longer active but may be preserved for historical or audit purposes.

## Obsolete

A resource state indicating content should no longer be used.

This is especially relevant for SOP Resources.

## Version

A distinct iteration of a resource.

Versioning is important for:

- SOP Resources
- Reference Ranges
- Calculators where formula or reference changes
- Other resources where historical accuracy matters

## Version History

The preserved record of previous versions of a resource.

## Effective Date

The date when a resource version becomes valid or active.

## Review Date

The date when a resource should be reviewed for accuracy and continued relevance.

---

# 9. Security Terms

## Authentication

The process of verifying who a user is.

For LabGato, authentication is required for administrators.

Public user authentication is TBD unless approved.

## Authorization

The process of verifying what an authenticated user is allowed to do.

Authorization must be enforced server-side.

## Session

The authenticated state maintained for a user after login.

Sessions must be protected using secure cookie practices.

## Session Invalidation

The process of ending a session.

Session invalidation must occur on logout, account deactivation, password reset where appropriate, and security-sensitive events.

## Login Throttling

A protection mechanism that limits repeated login attempts.

## Brute-Force Protection

Protection against repeated credential guessing.

## MFA

Multi-Factor Authentication.

MFA may be required for administrators where supported and approved.

## Audit Log

A protected record of important administrative and security actions.

Examples include:

- Login
- Failed login
- Logout
- Role change
- Permission change
- Content change
- Account deactivation
- Security-setting change

## Threat Model

A structured analysis of possible threats, attacks, impacts, and mitigations.

## Data Minimization

The privacy principle of collecting only the data required for the approved purpose.

## PII

Personally Identifiable Information.

LabGato must avoid unnecessary collection of PII.

LabGato must not collect patient PII.

## Sensitive Data

Data that must be protected from unauthorized access or disclosure.

Examples include:

- Passwords
- API keys
- Session tokens
- Audit logs
- Administrative credentials
- Personal data

## Secrets

Confidential configuration values.

Examples include:

- API keys
- Database passwords
- Mail credentials
- Encryption keys

Secrets must never be committed to the repository.

## Environment Separation

The practice of using separate configuration and data for development, staging, and production.

## Injection Attack

An attack where untrusted input is executed or interpreted by the system.

Examples include:

- SQL injection
- XSS
- Command injection
- Template injection
- Header injection

## XSS

Cross-Site Scripting.

An attack where malicious scripts are injected into web pages.

## CSRF

Cross-Site Request Forgery.

An attack where unauthorized actions are submitted on behalf of an authenticated user.

## IDOR

Insecure Direct Object Reference.

An attack where a user accesses a resource they should not be able to access by manipulating an identifier.

## Rate Limiting

A control that limits how often a user or client can perform an action.

Rate limiting is required for sensitive endpoints and AI endpoints.

## Prompt Injection

An AI attack where user input attempts to override system instructions or expose restricted behavior.

Prompt Injection protection is required for AI features.

## Provider-Independent AI

An AI architecture where the application can switch AI providers without rewriting the core application.

## Knowledge Base

The approved information source used by AI features.

The AI must use the Knowledge Base as its primary source of truth.

## Human Handoff

A future AI behavior where the AI directs the user to an approved human or administrative contact when it cannot safely answer.

---

# 10. Accessibility Terms

## Accessibility

The practice of making the platform usable by people with a wide range of abilities and assistive technologies.

## WCAG

Web Content Accessibility Guidelines.

LabGato should target WCAG 2.2 AA where practical and applicable.

## Keyboard Navigation

The ability to use the interface using only a keyboard.

## Visible Focus

A clear visual indication of which interactive element currently has keyboard focus.

## Screen Reader Support

Compatibility with assistive technology that reads interface content aloud.

## ARIA

Accessible Rich Internet Applications.

ARIA attributes may be used where native HTML semantics are insufficient.

ARIA must not be used as a replacement for proper semantic HTML.

## Contrast

The visual difference between text and background.

Sufficient contrast is required for readability.

## Touch Target

The clickable or tappable area of an interactive element.

Touch targets must be large enough for mobile use.

## Reduced Motion

A user preference or design practice that minimizes unnecessary animation.

---

# 11. SEO Terms

## SEO

Search Engine Optimization.

SEO must be built into the architecture of public pages.

## Meta Title

The page title used in search results and browser tabs.

## Meta Description

A summary of the page intended for search engines.

## Canonical URL

The preferred URL for a page.

Canonical URLs help prevent duplicate content issues.

## Open Graph

Metadata used when pages are shared on social platforms.

## Structured Data

Machine-readable data added to pages to describe content.

Examples may include:

- Article
- FAQ
- Breadcrumb
- WebPage
- SoftwareApplication where appropriate

Structured data must be used accurately and not fabricated.

## Sitemap

An XML file listing important public URLs.

The sitemap must exclude private, admin, and non-indexable pages.

## Robots

Robots directives that guide search engine crawling.

Robots configuration must be intentional.

It must not accidentally block the whole website.

## Indexable

A page that is allowed to appear in search engine results.

## Non-Indexable

A page that should not appear in search engine results.

Admin, login, and private pages must be non-indexable.

---

# 12. Technical Terms

## Backend

The server-side part of LabGato.

The backend is expected to use Laravel, subject to final technology stack approval.

## Frontend

The user-facing part of LabGato.

The frontend approach must be documented and approved.

## API

Application Programming Interface.

APIs may be used for internal or external communication where approved.

## Database

The structured data store for LabGato.

MySQL/MariaDB is preferred, subject to final approval.

## Migration

A version-controlled database schema change.

## Seeder

A script that inserts initial or development data.

## Factory

A helper used to generate test data.

## Queue

A system for handling background jobs.

Shared hosting constraints must be respected when designing queue behavior.

## Cron

A scheduled task runner.

Cron may be used on Hostinger where available.

## Cache

Temporary storage used to improve performance.

## Session Driver

The storage mechanism used for user sessions.

## Queue Driver

The storage mechanism used for queued jobs.

## Environment Variable

A configuration value stored outside code.

Environment variables must be used for secrets and environment-specific settings.

## Shared Hosting

A hosting environment where server resources are shared.

LabGato must be designed to remain compatible with Hostinger shared hosting constraints.

## Hostinger Constraint

The confirmed deployment constraint that LabGato must run on Hostinger PHP/HTML hosting with PHP 8.4.

## Production

The live environment.

## Staging

A pre-production environment used for verification.

Staging availability depends on hosting capabilities.

## Development

The local or development environment.

---

# 13. Deprecated or Excluded Terms

These terms are excluded from the current LabGato scope unless explicitly re-approved.

## Quote Wizard

Excluded.

This belonged to the superseded laboratory business/lead-generation scope.

## Lead

Excluded.

Lead generation is not part of the current LabGato scope.

## Lead Management

Excluded.

Lead management is not part of the current LabGato scope.

## Business Profile

Excluded as a primary product feature.

A business profile may only be relevant if explicitly re-approved for a different scope.

## Service Package

Excluded.

Service packages belonged to the superseded laboratory business scope.

## Customer Quotation

Excluded.

Quotation functionality is not part of the current scope.

## Patient Portal

Excluded.

LabGato is not a patient result portal.

## LIS

Excluded.

LabGato is not a Laboratory Information System.

## Hospital Management System

Excluded.

LabGato is not a hospital management system.

## Diagnostic Engine

Excluded.

LabGato must not function as a diagnostic decision engine.

---

# Document Completion Check

## Requirements Covered

- Official project identity terms defined.
- Core product area terms defined.
- User and role terms defined.
- Governance terms defined.
- Development status terms defined.
- User interface terms defined.
- Content lifecycle terms defined.
- Security terms defined.
- Accessibility terms defined.
- SEO terms defined.
- Technical terms defined.
- Deprecated or excluded terms defined.

## Decisions Established

- LabGato terminology is official for future documentation and implementation.
- Excluded terms must not be reintroduced without approval.
- AI terminology is future-oriented and controlled.
- Public user accounts remain unconfirmed.
- RBAC terminology remains subject to later role documentation.
- Shared hosting terminology is included because of Hostinger constraints.

## Dependencies

- Approved LabGato master directive.
- Approved Project Charter.
- Approved Product Scope and Non-Goals.
- Approved Target Users and Journeys.
- Approved Core Product Areas.
- Approved Phase 01 file tree.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/06_Glossary.md

It affects all future documentation, naming conventions, implementation prompts, database naming, API naming, and UI labels.

## Open Questions

1. Should MLS Toolkit terms be preserved as aliases in code or only in documentation?
2. What naming convention will be used for database tables and API resources?
3. Will resource types use singular or plural route names?
4. Will admin routes use a dedicated subdomain or path prefix?
5. Will AI terminology require expansion in a later AI phase?

## TBD Decisions

- TBD: Final naming convention standard.
- TBD: Final route naming standard.
- TBD: Final database table naming standard.
- TBD: Final component naming standard.
- TBD: Future AI glossary expansion.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE01-DOC06

## Context

LabGato requires a controlled vocabulary to prevent ambiguity across documentation, code, UI, database design, and implementation prompts. This glossary defines the official terms for the project.

This prompt implements the documentation outcome of the Glossary document.

## Objective

Create the official LabGato Glossary document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository should contain the official vocabulary for LabGato.

## Existing Architecture

Relevant approved decisions:

- LabGato is a professional laboratory toolkit.
- The previous quote/lead/business scope is superseded.
- AI is future controlled functionality.
- Public user accounts are not confirmed.
- Shared hosting constraints must be respected.
- Documentation controls implementation.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- 01_Project_Charter.md
- 02_Current_State_Assessment.md
- 03_Product_Scope_and_Non_Goals.md
- 04_Target_Users_and_Journeys.md
- 05_Core_Product_Areas.md
- Any existing glossary or naming convention documents

If no files exist, record that the documentation structure is being initialized.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/06_Glossary.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

If a documentation index already exists, add a reference to:

01_Product_Foundation_and_Master_Architecture/06_Glossary.md

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
- Consistent terminology formatting.
- No ambiguous labels.

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
- The file defines approved terms.
- The file identifies excluded terms.
- The file does not reintroduce superseded quote/lead terminology as active scope.

If the repository supports Markdown linting, run it.

## Integration Requirements

This document must be referenced by future documentation when introducing new terms.

If a future document introduces a new term, that term must either reference this glossary or update it through approved documentation.

## Definition of Done

This implementation is complete when:

- The Glossary file exists.
- The file matches the approved Phase 01 file tree.
- The file defines official LabGato terminology.
- The file identifies excluded terms.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
- No application code was changed.
- No secrets were committed.