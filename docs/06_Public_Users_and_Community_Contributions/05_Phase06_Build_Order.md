# 05 — Phase 06 Build Order

## Depends On

This document depends on:

- All preceding Phase 06 documents (01 through 04).
- The approved Phase 01 through Phase 05 documentation.
- The completed Builds 1-24 (MVP, AI, Analytics, Admin System).
- The approved custom direction for public user accounts and community contributions.

## Defines

This document defines:

- The exact step-by-step implementation order for the Phase 06 features.
- The environment setup prerequisites (MySQL alignment and AI activation).
- The dependencies between the account, layout, contribution, and credit builds.
- The Definition of Done for each Phase 06 vertical slice.
- The criteria for transitioning to the V2.0 Release.

## Affects

This document affects:

- The immediate development workflow for the community contribution features.
- The sequence of pull requests and commits for Phase 06.
- The readiness assessment for the V2.0 platform release.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Prevents out-of-order development of authentication and contribution features.
- Ensures the database environment is aligned before new schema changes are introduced.
- Provides a clear checklist for completing the community platform.

---

# 1. Purpose

This document establishes the Phase 06 Build Order for LabGato.

With the core platform, AI, and admin systems complete, Phase 06 introduces public user accounts and community contributions. Building these features out of order (e.g., building the contribution forms before the member authentication exists) will result in blocked work and architectural debt.

This build order ensures that the environment is prepared, accounts are established, the member area is built, and the contribution workflow is implemented in a strict, dependency-driven sequence.

---

# 2. Build Order Philosophy

We will continue to use the **Vertical Slice** and **Dependency-Driven** methodology.

Phase 06 introduces a new user type (members) alongside the existing admin type. The authentication and layout foundations must be solid before the contribution workflow is built, and the contribution workflow must be functional before public credit and profiles are exposed.

---

# 3. Step-by-Step Build Order

## Build 25: Environment Setup & Database Alignment (Slice 11 Prerequisite)

**Objective**: Align the local development environment with production and activate the AI features.

**Tasks**:
1. Create a local MySQL database for LabGato.
2. Update `.env` to use MySQL instead of SQLite.
3. Update `.env.example` to reflect MySQL as the default.
4. Run `php artisan migrate:fresh --seed` to verify all existing migrations work on MySQL.
5. Configure the Groq API key in `.env`.
6. Set `AI_ENABLED=true`, `AI_PROVIDER=groq`, and `FEATURE_AI=true`.
7. Verify AI generation works in the admin portal (Knowledge drafting).
8. Run the full test suite to ensure no SQLite-specific behavior remains.

**Dependencies**: Builds 1-24 Complete.

## Build 26: Public User Accounts & Authentication (Slice 11)

**Objective**: Implement the public user account foundation, including registration, member login, and auth separation.

**Tasks**:
1. Add `user_type`, `display_name`, `bio`, `is_active`, and `profile_slug` columns to the `users` table.
2. Create `EnsureUserIsAdmin`, `EnsureUserIsMember`, and `EnsureUserIsActive` middleware.
3. Create the member registration flow (`/register`).
4. Create the member login flow (`/login`).
5. Implement password reset for members.
6. Update the admin login to reject `user_type='member'`.
7. Write Feature tests for registration, login, logout, and auth separation.

**Dependencies**: Build 25.

## Build 27: Member Layout & Dashboard (Slice 12)

**Objective**: Build the member area layout, dashboard, profile, settings, and public header adaptations.

**Tasks**:
1. Create the member layout (`layouts/member.blade.php`).
2. Create the member horizontal tab navigation component.
3. Build the member dashboard (`/account/dashboard`) with quick stats and actions.
4. Build the member profile page (`/account/profile`) with edit functionality.
5. Build the account settings page (`/account/settings`) with password change and deactivation.
6. Update the public header to adapt to guest, member, and admin states.
7. Create the user menu dropdown component.
8. Write Feature tests for member area access, dashboard rendering, and profile updates.

**Dependencies**: Build 26.

## Build 28: Contribution Submission & Approval (Slice 13)

**Objective**: Implement the member contribution workflow and the admin review queue.

**Tasks**:
1. Add `contributed_by` and `rejection_reason` columns to `sop_resources`, `knowledge_resources`, and `interpretation_resources`.
2. Add the `Rejected` case to the `ResourceStatus` enum.
3. Create the member submission forms for SOP, Knowledge, and Interpretation.
4. Build the member contribution list, detail, edit, withdraw, and delete flows.
5. Create the `contributions.review` permission and assign it to Super Admin and Content Manager.
6. Build the admin review queue (`/admin/contributions`).
7. Build the admin review page with approve/reject actions.
8. Write Feature tests for submission, approval, rejection, and access control.

**Dependencies**: Build 27.

## Build 29: Contributor Credit & Public Profiles (Slice 14)

**Objective**: Implement public contributor profiles and resource page attribution.

**Tasks**:
1. Create the public contributor profile route and view (`/contributors/{profile_slug}`).
2. Add credit attribution blocks to the public detail pages for SOPs, Knowledge, and Interpretation.
3. Implement the deactivated member credit behavior (plain text, no link).
4. Update JSON-LD structured data to include `Person` schema for contributors.
5. Write Feature tests for profile rendering, attribution display, and deactivation behavior.

**Dependencies**: Build 28.

---

# 4. Definition of Done for Phase 06 Slices

A Phase 06 slice is only considered **COMPLETE** when:

- [ ] Database migrations run successfully on MySQL.
- [ ] Members can register, log in, and log out.
- [ ] Members and admins are strictly separated by middleware.
- [ ] The member dashboard displays accurate stats and recent contributions.
- [ ] Members can submit SOP, Knowledge, and Interpretation contributions.
- [ ] Contributions are not publicly visible until approved by an admin.
- [ ] Admins can approve or reject contributions with a reason.
- [ ] Contributors are publicly credited on approved resources.
- [ ] Public contributor profiles render correctly.
- [ ] All Feature tests pass.
- [ ] No privilege escalation is possible between members and admins.

---

# 5. Transition to V2.0 Release

Once Build 25 through Build 29 are marked **COMPLETE**, LabGato will have achieved its **V2.0 (Community Platform) milestone**.

At that point, the project will undergo a final regression test and be prepared for the V2.0 production deployment.

Future phases (Phase 07+) may include:
- Email notifications for contribution status changes.
- Member file/image uploads.
- Public user bookmarks and saved searches.
- Advanced contributor reputation systems.

---

# Document Completion Check

## Requirements Covered

- Build order philosophy defined.
- Environment setup and database alignment (Build 25) defined.
- Public user accounts and authentication (Build 26) defined.
- Member layout and dashboard (Build 27) defined.
- Contribution submission and approval (Build 28) defined.
- Contributor credit and public profiles (Build 29) defined.
- Definition of Done for Phase 06 slices defined.

## Decisions Established

- Environment setup (MySQL + AI activation) precedes all code builds.
- Authentication and layout precede contribution features.
- Contribution workflow precedes public credit and profiles.
- A slice is only complete when backend, database, UI, and tests are all verified.

## Dependencies

- All Phase 06 documents.
- Completed Builds 1-24.

## Files Affected

This document creates:

06_Public_Users_and_Community_Contributions/05_Phase06_Build_Order.md

It serves as the execution plan for the Phase 06 development cycle.

## Open Questions

1. Should the V2.0 release include a dedicated "Community Guidelines" page for contributors?

## TBD Decisions

- TBD: Final decision on Community Guidelines page.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE06-DOC05

## Context

Phase 06 documentation is concluding. This document establishes the exact build order for the environment setup, public accounts, member layout, contribution workflow, and public credit.

This prompt implements the documentation outcome of the Phase 06 Build Order document.

## Objective

Create the official LabGato Phase 06 Build Order document inside the approved Phase 06 documentation structure.

## Existing Architecture

Relevant approved decisions:

- Single users table with user_type separation.
- Members contribute SOPs, Knowledge, Interpretation only.
- Admin approval is required before publication.
- Contributors are publicly credited.

## Files to Create

Create:

06_Public_Users_and_Community_Contributions/05_Phase06_Build_Order.md

Use the approved document content from this documentation output.

## Definition of Done

This implementation is complete when:

- The Phase 06 Build Order file exists.
- The file matches the approved Phase 06 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.