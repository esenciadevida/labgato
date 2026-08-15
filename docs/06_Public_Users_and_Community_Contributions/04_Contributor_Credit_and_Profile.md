# 04 — Contributor Credit and Profile

## Depends On

This document depends on:

- The approved Phase 03 Resource Architecture (specifically Public Page Patterns).
- The approved Phase 06 Public User Accounts and Authentication document.
- The approved Phase 06 Member Layout and Dashboard document.
- The approved Phase 06 Contribution Submission and Approval Workflow document.

## Defines

This document defines:

- The public contributor profile page architecture.
- The data model additions for public profile URLs.
- The resource page credit attribution rules.
- The privacy and account deactivation impact on public credit.
- The SEO and structured data rules for contributor profiles.
- The testing requirements for public attribution.

## Affects

This document affects:

- The Phase 06 Build Order document.
- The users database table schema.
- The public resource detail views (SOP, Knowledge, Interpretation).
- The public routes and controllers.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a transparent and motivating credit system for community contributors.
- Ensures that published contributions remain properly attributed even if a member leaves the platform.
- Provides a public-facing directory of trusted community experts.
- Defines how profile URLs are generated and preserved.

---

# 1. Purpose

This document establishes the Contributor Credit and Profile architecture for LabGato.

When a laboratory professional contributes an SOP, Knowledge article, or Interpretation guide, they should be publicly credited for their expertise. This document defines how the platform displays contributor attribution on resource pages and how the public contributor profile functions.

---

# 2. Data Model Additions

## 2.1 Schema Addition

Add the following column to the `users` table:

profile_slug
- Type: string
- Nullable: true
- Unique: true
- Description: The URL-safe slug used for the public contributor profile (e.g., `/contributors/john-doe`).

## 2.2 Generation Rules

- Auto-generate `profile_slug` from `display_name` upon registration (e.g., "John Doe" → `john-doe`).
- If the slug already exists, append a random string or number (e.g., `john-doe-8f2a`).
- Members cannot manually edit their `profile_slug` in the MVP to prevent URL breaking and impersonation risks.
- If a member changes their `display_name`, the `profile_slug` remains unchanged to preserve external links.

---

# 3. Public Contributor Profile

## 3.1 Route

`GET /contributors/{profile_slug}` → `PublicSite\ContributorController@show`

## 3.2 Page Layout

The public profile page displays:

1. **Header Section**:
   - Initials-based avatar (matching the member area).
   - Display name (as `<h1>`).
   - Bio (if provided).
   - "Member since {Month, Year}".

2. **Contributions Section**:
   - A list or grid of the member's **published** contributions.
   - Each item shows: Title, Type badge (SOP, Knowledge, Interpretation), Published date, and short description.
   - Pagination if the member has more than 12 published contributions.

## 3.3 Access Rules

- Only active members (`is_active = true`) have a visible public profile.
- If a member is deactivated or deleted, the profile route returns a 404 Not Found.
- Guest users and other members can view public profiles.

---

# 4. Resource Page Credit Attribution

## 4.1 Display Location

On the public detail pages for SOPs, Knowledge, and Interpretation resources, a credit attribution block must be displayed.

Location: Below the resource title and metadata, before the main content.

## 4.2 Visual Design

Use a subtle, professional layout:
- Initials-based avatar (small).
- Text: "Contributed by [Display Name]"
- If the user is active, the name is a link to `/contributors/{profile_slug}`.
- If the user is inactive, the name is plain text (no link).

## 4.3 Attribution Rules

- Only display the credit block if `contributed_by` is not null.
- Admin-created resources (where `contributed_by` is null) do not display this block.
- The credit block must not interfere with the mandatory educational disclaimers (disclaimers should remain at the very top or in a distinct alert box above the credit).

---

# 5. Privacy and Deactivation Impact

## 5.1 Account Deactivation

When a member deactivates their account (sets `is_active = false` via Settings):

1. Their public profile (`/contributors/{slug}`) returns 404.
2. Their published contributions **remain published** (because an admin verified and approved them).
3. The credit attribution on those resources remains visible as plain text ("Contributed by {Display Name}"), but the link to the profile is removed.

Rationale: The platform stands behind the approved content, but respects the user's desire to leave the platform and remove their public profile.

## 5.2 Account Deletion (Admin Action)

If an admin hard-deletes a user record:
- The `contributed_by` foreign key is set to null (due to `null on delete` constraint).
- The credit attribution block disappears from the resource pages.
- The resource remains published.

---

# 6. SEO and Structured Data

## 6.1 Indexing Rules

- Public contributor profiles are indexable by search engines.
- Do not add `noindex` to active contributor profiles.
- Do not index profiles with zero published contributions (redirect to home or return 404).

## 6.2 Structured Data

Include JSON-LD `ProfilePage` and `Person` schema on the contributor profile page:

```json
{
  "@context": "https://schema.org",
  "@type": "ProfilePage",
  "mainEntity": {
    "@type": "Person",
    "name": "John Doe",
    "description": "Clinical laboratory scientist specializing in hematology.",
    "url": "https://labgato.com/contributors/john-doe"
  }
}
6.3 Resource Page Schema
Update the JSON-LD on SOP, Knowledge, and Interpretation detail pages to include the author property if contributed_by is set:
json

12345
7. Testing Requirements
7.1 Profile Tests
Test that an active member's profile renders correctly at /contributors/{slug}.
Test that a profile with zero published contributions returns 404 or redirects.
Test that a deactivated member's profile returns 404.
Test that the profile lists only published contributions (not drafts or rejected).
7.2 Attribution Tests
Test that a member-contributed resource displays the credit block.
Test that an admin-created resource does not display the credit block.
Test that the credit block links to the profile for active members.
Test that the credit block is plain text for deactivated members.
7.3 Data Integrity Tests
Test that profile_slug is generated uniquely on registration.
Test that changing display_name does not change profile_slug.
Test that hard-deleting a user sets contributed_by to null on their resources.
Document Completion Check
Requirements Covered
Public contributor profile architecture defined.
Data model additions (profile_slug) defined.
Resource page credit attribution rules defined.
Privacy and deactivation impact defined.
SEO and structured data rules defined.
Testing requirements defined.
Decisions Established
Contributor profiles use a dedicated profile_slug to prevent URL breaking.
Profiles are public and indexable.
Deactivated members lose their profile page, but their approved contributions remain published with plain-text credit.
Admin-created resources do not show a contributor credit block.
JSON-LD Person schema is used for contributor profiles and resource authorship.
Dependencies
Approved Phase 06 Public User Accounts and Authentication.
Approved Phase 06 Contribution Submission and Approval Workflow.
Files Affected
This document creates:
06_Public_Users_and_Community_Contributions/04_Contributor_Credit_and_Profile.md
It dictates the addition of profile_slug to the users table, the creation of the public contributor profile route/views, and updates to public resource detail views.
Open Questions
Should members be able to set a custom profile_slug in a future phase, or keep it auto-generated for simplicity?
Should we add a "Report Contributor" feature for public users to flag inappropriate profiles?
TBD Decisions
TBD: Final decision on custom profile slugs.
TBD: Final decision on profile reporting.
Implementation Prompt
Implementation Prompt ID
LABGATO-PHASE06-DOC04
Context
LabGato needs to publicly credit members who contribute approved content. This document defines the public contributor profile, the attribution UI on resource pages, and the behavior when accounts are deactivated.
This prompt implements the documentation outcome of the Contributor Credit and Profile document.
Objective
Create the official LabGato Contributor Credit and Profile document inside the approved Phase 06 documentation structure.
Files to Create
Create:
06_Public_Users_and_Community_Contributions/04_Contributor_Credit_and_Profile.md
Use the approved document content from this documentation output.
Definition of Done
This implementation is complete when:
The Contributor Credit and Profile file exists.
The file matches the approved Phase 06 file tree.
The file includes the document completion check.
The file includes the implementation prompt.
No unauthorized files were added.