# 13 — Phase Roadmap and Build Order

## Depends On

This document depends on:

- All preceding Phase 01 documents (01 through 12).
- The approved LabGato Master Software Project directive.
- The approved vertical-slice development strategy.
- The approved documentation-first development philosophy.

## Defines

This document defines:

- The sequence of future documentation phases.
- The definition of the Minimum Viable Product (MVP) for LabGato.
- The vertical-slice implementation strategy.
- The dependency-driven build order for application code.
- The criteria for completing Phase 01 and transitioning to Phase 02.

## Affects

This document affects:

- All future project planning.
- The sequence of implementation prompts.
- The prioritization of development tasks.
- The definition of "done" for the initial release.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the exact sequence in which the application will be built.
- Prevents building features out of dependency order (e.g., building a calculator UI before the database schema exists).
- Ensures that every line of code written serves a specific, documented vertical slice.

---

# 1. Purpose

This document establishes the strategic roadmap and build order for LabGato.

LabGato is a complex system with interconnected resources, administrative controls, and strict hosting constraints. Building it in a random or disconnected manner will result in technical debt, orphaned pages, and architectural violations.

This roadmap ensures that the project progresses logically from foundation to functional vertical slices, culminating in a stable, secure, and deployable MVP.

---

# 2. Documentation Roadmap

Documentation must precede implementation. The following phases are approved to follow Phase 01.

## Phase 02: Technical Foundation Architecture

**Objective:** Define the exact technical implementation details for the core framework.
**Expected Documents:**
- Laravel Application Structure & Configuration
- Database Schema & Migrations (Access, Taxonomy, System)
- Authentication & Session Architecture
- Authorization & RBAC Implementation
- Admin Portal Layout & Base Components
- Error Handling & Logging Architecture
- Deployment & Environment Setup

## Phase 03: Resource Architecture (Tools & Calculators)

**Objective:** Define the specific logic, UI, and data structures for the first core product areas.
**Expected Documents:**
- Laboratory Tools Architecture (Logic schemas, UI states)
- Calculators Architecture (Formula validation, rounding, testing)
- Resource Listing & Detail Page Architecture
- Search Implementation Strategy (MySQL-based)

## Phase 04: Content & Reference Architecture

**Objective:** Define the architecture for text-heavy and structured reference resources.
**Expected Documents:**
- Reference Ranges Architecture (Demographic variations, units)
- SOP Resources Architecture (Versioning, document control)
- Result Interpretation & Knowledge Architecture (Rich text, media)
- Taxonomy & Categorization Implementation

## Phase 05: AI & Advanced Features Architecture (Post-MVP)

**Objective:** Define the controlled AI integration and advanced platform features.
**Expected Documents:**
- AI Knowledge Base Generation
- AI Provider Adapter & Security
- AI UI & Rate Limiting
- Advanced Analytics & Audit Log UI

*Note: Phases 03, 04, and 05 may be adjusted based on MVP prioritization decisions made during Phase 02.*

---

# 3. Minimum Viable Product (MVP) Definition

The LabGato MVP is the first fully functional, secure, and deployable version of the platform.

## 3.1 MVP Inclusions

To be considered MVP-complete, the system must include:

1.  **Application Foundation:** Laravel setup, Hostinger-compatible deployment, secure environment.
2.  **Authentication & RBAC:** Secure admin login, Super Administrator and Content Manager roles, server-side enforcement.
3.  **Admin Portal Base:** Dashboard, sidebar navigation, base layout, audit logging.
4.  **Taxonomy Management:** Ability to create and manage Categories and Tags.
5.  **One Complete Resource Vertical Slice:** Full CRUD, public listing, and public detail page for at least one core resource type (e.g., Calculators or Laboratory Tools).
6.  **Public Site Base:** Home page, public navigation, footer, legal pages, 404/500 error pages.
7.  **Search Base:** Basic MySQL-backed search across the implemented resource type.
8.  **SEO & Accessibility Base:** Meta tags, canonical URLs, semantic HTML, keyboard navigation for core flows.

## 3.2 MVP Exclusions

The following are explicitly excluded from the MVP to ensure a focused, high-quality release:

- AI Assistance (Deferred to post-MVP).
- Public User Accounts (Deferred).
- Advanced Analytics (Deferred).
- Complex SOP Versioning (Basic CRUD only for MVP).
- External Integrations (Payment, LIS, SMS).

---

# 4. Vertical-Slice Implementation Strategy

Implementation will not be done "all database first, then all backend, then all frontend."
Implementation will be done in **Vertical Slices**.

A vertical slice delivers a complete, working feature from the database to the UI.

## Example: The "Calculator" Vertical Slice

1.  **Database:** Create `calculators` and `calculator_category` migrations.
2.  **Backend:** Create `Calculator` model, factories, and seeders.
3.  **Admin UI:** Create Livewire/Blade components for Calculator CRUD.
4.  **Authorization:** Apply policies to restrict Calculator management to Content Managers.
5.  **Public Backend:** Create public routes and controllers for listing and viewing.
6.  **Public UI:** Create public Blade templates for Calculator listing and interactive detail page.
7.  **Logic:** Implement the specific calculation logic (e.g., Anion Gap) and validation.
8.  **Testing:** Write unit tests for the math logic and feature tests for admin/public access.
9.  **SEO/Accessibility:** Add meta tags, schema markup, and ARIA labels to the public pages.
10. **Documentation:** Update the repository README or changelog.

Only when all 10 steps are complete is the "Calculator" slice considered **Done**.

---

# 5. Application Build Order

Once Phase 02 (Technical Foundation Architecture) is documented and approved, the application code will be built in the following strict dependency order.

## Build 1: Environment & Foundation

- Initialize Laravel project (PHP 8.4 compatible).
- Configure Hostinger-compatible environment variables.
- Set up database connection (MySQL).
- Configure Vite, Tailwind CSS, Alpine.js, and Livewire.
- Create base public and admin layouts.
- Implement global error handling (404, 500, 403 pages).

## Build 2: Access & Security Foundation

- Create User, Role, and Permission migrations and models.
- Implement custom RBAC logic (or configure approved package).
- Seed default roles (Super Admin, Content Manager) and permissions.
- Implement Admin Authentication (Login, Logout, Password Reset).
- Implement session security and login throttling.
- Create the Admin Portal base layout (Sidebar, Header, Dashboard shell).

## Build 3: Taxonomy & System Settings

- Create Category and Tag migrations and models.
- Build Admin CRUD for Categories and Tags.
- Create Setting model and Admin UI for global site settings (Site Name, Logo, Default SEO).
- Implement Audit Log model and middleware to record administrative actions.

## Build 4: First Resource Vertical Slice (e.g., Calculators)

- Execute the "Calculator" vertical slice as defined in Section 4.
- This establishes the pattern for all future resources.

## Build 5: Additional Resource Vertical Slices

- Execute vertical slices for Tools, Reference Ranges, SOPs, Interpretation, and Knowledge.
- Reuse the established patterns from Build 4.

## Build 6: Search & Public Discovery

- Implement the unified Search controller and UI.
- Ensure search queries are optimized for MySQL.
- Implement public sitemaps and robots.txt generation.

## Build 7: MVP Hardening & Deployment

- Conduct security review (IDOR, XSS, CSRF, Mass Assignment).
- Conduct accessibility review (WCAG 2.2 AA checks on core flows).
- Optimize assets and queries for Hostinger shared hosting limits.
- Execute the first deployment to the Hostinger staging/production environment.
- Verify Cron jobs (queue/scheduler) in the hosted environment.

---

# 6. Phase 01 Completion Criteria

Phase 01 is officially complete when:

1.  All 13 documents in the `01_Product_Foundation_and_Master_Architecture/` folder are written and approved.
2.  The Project Charter, Scope, Stack, and RBAC models are fully aligned.
3.  No unresolved architectural conflicts exist.
4.  The project owner has reviewed the Phase 01 Implementation Map.
5.  The project owner has approved the transition to Phase 02.

---

# Document Completion Check

## Requirements Covered

- Documentation roadmap defined.
- MVP definition established.
- Vertical-slice strategy defined.
- Application build order defined.
- Phase 01 completion criteria defined.

## Decisions Established

- LabGato will be built in vertical slices, not horizontal layers.
- AI and Public User Accounts are excluded from the MVP.
- The first resource vertical slice will establish the pattern for all subsequent resources.
- Phase 02 will focus strictly on Technical Foundation Architecture.

## Dependencies

- All preceding Phase 01 documents.
- Approved LabGato Master Directive.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/13_Phase_Roadmap_and_Build_Order.md

It serves as the final index and execution plan for the foundation phase.

## Open Questions

1. Which specific resource type (Tool, Calculator, Reference, etc.) should be selected for the very first vertical slice in Build 4?
2. Should the MVP include basic email notifications for system errors?

## TBD Decisions

- TBD: Selection of the first vertical slice resource.
- TBD: Final selection of Phase 03 and 04 documentation order based on MVP resource selection.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE01-DOC13

## Context

Phase 01 of LabGato documentation is concluding. This document establishes the roadmap, MVP definition, and build order that will guide the transition from planning to actual application development.

This prompt implements the documentation outcome of the Phase Roadmap and Build Order document.

## Objective

Create the official LabGato Phase Roadmap and Build Order document inside the approved Phase 01 documentation structure.

After this prompt is completed, Phase 01 documentation is fully drafted and ready for final review and repository commit.

## Existing Architecture

Relevant approved decisions:

- Documentation controls implementation.
- Vertical slices are mandatory.
- MVP excludes AI and patient features.
- Hostinger constraints dictate the deployment build steps.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- Documents 01 through 12.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/13_Phase_Roadmap_and_Build_Order.md

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

No public SEO impact. This is an internal planning document.

## Analytics Requirements

No analytics events required.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the MVP boundaries.
- The file defines the vertical slice strategy.

## Integration Requirements

This document bridges the gap between Phase 01 (Planning) and Phase 02 (Technical Architecture). It must be referenced when initiating Phase 02.

## Definition of Done

This implementation is complete when:

- The Phase Roadmap and Build Order file exists.
- The file matches the approved Phase 01 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.