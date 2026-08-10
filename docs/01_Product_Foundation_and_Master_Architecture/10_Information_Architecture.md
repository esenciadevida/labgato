# 10 — Information Architecture

## Depends On

This document depends on:

- The approved LabGato Master Software Project directive.
- The approved Project Charter.
- The approved Product Scope and Non-Goals document.
- The approved Target Users and Journeys document.
- The approved Core Product Areas document.
- The approved Technology Stack document.
- The approved Deployment Constraints document.

## Defines

This document defines:

- The high-level structure of the public website.
- The high-level structure of the Admin Portal.
- The URL hierarchy and routing strategy.
- The primary navigation structure.
- The footer navigation structure.
- The search integration points.
- The error page structure.
- The legal page structure.
- The access model for public and administrative areas.

## Affects

This document affects:

- The Frontend Architecture (future phases).
- The Backend Routing Architecture (future phases).
- The SEO Architecture (future phases).
- The UI/UX Design System (future phases).
- The Admin Portal Specification (future phases).
- The Sitemap generation strategy.
- The breadcrumb navigation strategy.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the page hierarchy and route structure for the entire platform.
- Prevents orphan pages and disconnected navigation.
- Ensures a logical flow for laboratory professionals seeking resources.
- Defines the administrative workspace structure.
- Provides the foundation for SEO and sitemap generation.

---

# 1. Purpose

This document establishes the Information Architecture (IA) for LabGato.

The IA defines how content, tools, and administrative features are organized, labeled, and navigated.

A strong IA ensures that laboratory professionals can find calculators, reference ranges, and SOPs quickly, while administrators can manage the platform efficiently.

---

# 2. Access Model

LabGato is divided into two distinct access zones:

1.  **Public Zone:** Accessible to visitors without authentication. Contains approved published resources.
2.  **Admin Zone:** Accessible only to authenticated administrators. Contains management tools, drafts, and system settings.

## 2.1 Public Zone Access

- Public resources are visible to all visitors unless a specific resource is marked as restricted in a future phase.
- Public pages must be indexable by search engines unless explicitly marked `noindex`.
- Public pages must not expose administrative links or sensitive data.

## 2.2 Admin Zone Access

- The Admin Zone is protected by authentication.
- All routes under the Admin Zone must enforce server-side authorization.
- The Admin Zone must not be indexable by search engines.
- The Admin Zone must use a distinct layout from the public site.

---

# 3. Public Site Structure

The public site is organized around the core product areas defined in Document 05.

## 3.1 Home Page

**URL:** `/`

**Purpose:**
- Introduce LabGato as a professional laboratory toolkit.
- Provide quick access to primary resource categories.
- Highlight featured or recently updated resources.
- Provide a prominent search bar.

**Key Sections:**
- Hero section with value proposition.
- Search bar.
- Resource category grid (Tools, Calculators, References, SOPs, Knowledge).
- Featured resources.
- Footer with legal links.

## 3.2 Laboratory Tools

**URL Prefix:** `/tools`

**Listing Page:** `/tools`
- Displays all published Laboratory Tools.
- Supports filtering by category.
- Supports search.

**Detail Page:** `/tools/{slug}`
- Displays the interactive tool.
- Shows description, instructions, and references.
- Provides input forms and result display.
- Shows related tools or calculators.

## 3.3 Calculators

**URL Prefix:** `/calculators`

**Listing Page:** `/calculators`
- Displays all published Calculators.
- Supports filtering by category (e.g., Hematology, Chemistry).
- Supports search.

**Detail Page:** `/calculators/{slug}`
- Displays the calculator interface.
- Shows formula, inputs, and outputs.
- Shows reference ranges and interpretation notes.
- Shows scientific references.
- Shows related calculators.

## 3.4 Reference Ranges

**URL Prefix:** `/reference-ranges`

**Listing Page:** `/reference-ranges`
- Displays all published Reference Ranges.
- Supports search by analyte/test name.
- Supports filtering by specimen type or category.

**Detail Page:** `/reference-ranges/{slug}`
- Displays the reference range table.
- Shows population, age, sex, and unit variations.
- Shows source and effective date.
- Shows related interpretation resources.

## 3.5 SOP Resources

**URL Prefix:** `/sops`

**Listing Page:** `/sops`
- Displays all published SOP Resources.
- Supports filtering by department or category.
- Supports search.

**Detail Page:** `/sops/{slug}`
- Displays the SOP content.
- Shows document control metadata (Version, Author, Review Date).
- Shows status (Published/Archived).
- Shows attachments if approved.

## 3.6 Result Interpretation

**URL Prefix:** `/interpretation`

**Listing Page:** `/interpretation`
- Displays all published Result Interpretation resources.
- Supports filtering by category or test type.
- Supports search.

**Detail Page:** `/interpretation/{slug}`
- Displays the educational interpretation content.
- Shows clear disclaimers.
- Shows related reference ranges and calculators.

## 3.7 Knowledge & Education

**URL Prefix:** `/knowledge`

**Listing Page:** `/knowledge`
- Displays all published Knowledge and Education resources.
- Supports filtering by category or tag.
- Supports search.

**Detail Page:** `/knowledge/{slug}`
- Displays the article or study guide.
- Shows author, date, and reading time.
- Shows related resources.

## 3.8 Search

**URL:** `/search`

**Purpose:**
- Provides a unified search results page.
- Accepts query parameters (e.g., `?q=hemoglobin&type=reference`).
- Displays results grouped by resource type or in a unified list.
- Shows empty state if no results are found.

## 3.9 Legal Pages

**URL Prefix:** `/legal`

**Pages:**
- `/legal/privacy-policy`
- `/legal/terms-of-service`
- `/legal/disclaimer`
- `/legal/accessibility-statement`
- `/legal/cookie-policy`

**Purpose:**
- Provide required legal and compliance information.
- Must be accessible from the footer.

## 3.10 Authentication Pages

**URL Prefix:** `/auth`

**Pages:**
- `/auth/login` (Admin login)
- `/auth/forgot-password`
- `/auth/reset-password`

**Purpose:**
- Provide secure entry to the Admin Portal.
- Must not be indexable.
- Must be rate-limited.

## 3.11 Error Pages

**URLs:**
- `/404` (Not Found)
- `/500` (Server Error)
- `/403` (Forbidden)

**Purpose:**
- Provide clear, branded error messages.
- Offer navigation back to the home page or search.
- Must not expose stack traces or sensitive data.

---

# 4. Admin Portal Structure

The Admin Portal is located under the `/admin` prefix.

**Base URL:** `/admin`

## 4.1 Dashboard

**URL:** `/admin/dashboard`

**Purpose:**
- Provide a high-level overview of platform activity.
- Show recent content updates.
- Show quick links to management modules.
- Show honest empty states if no data exists.

## 4.2 Resource Management Modules

Each core product area has a dedicated management module.

**Tools:**
- `/admin/tools` (Listing)
- `/admin/tools/create`
- `/admin/tools/{id}/edit`

**Calculators:**
- `/admin/calculators` (Listing)
- `/admin/calculators/create`
- `/admin/calculators/{id}/edit`

**Reference Ranges:**
- `/admin/reference-ranges` (Listing)
- `/admin/reference-ranges/create`
- `/admin/reference-ranges/{id}/edit`

**SOPs:**
- `/admin/sops` (Listing)
- `/admin/sops/create`
- `/admin/sops/{id}/edit`

**Interpretation:**
- `/admin/interpretation` (Listing)
- `/admin/interpretation/create`
- `/admin/interpretation/{id}/edit`

**Knowledge:**
- `/admin/knowledge` (Listing)
- `/admin/knowledge/create`
- `/admin/knowledge/{id}/edit`

## 4.3 Taxonomy Management

**URL Prefix:** `/admin/taxonomy`

**Pages:**
- `/admin/taxonomy/categories`
- `/admin/taxonomy/tags`

**Purpose:**
- Manage categories and tags used across resources.
- Ensure consistent classification.

## 4.4 User and Role Management

**URL Prefix:** `/admin/users`

**Pages:**
- `/admin/users` (Listing)
- `/admin/users/create`
- `/admin/users/{id}/edit`
- `/admin/roles` (Role listing and permission matrix)

**Purpose:**
- Manage administrators.
- Assign roles and permissions.
- Enforce least privilege.

## 4.5 System Settings

**URL Prefix:** `/admin/settings`

**Pages:**
- `/admin/settings/general` (Site name, logo, metadata)
- `/admin/settings/seo` (Default meta titles, robots)
- `/admin/settings/analytics` (Analytics configuration)
- `/admin/settings/ai` (AI provider configuration, future)

**Purpose:**
- Manage global platform configuration.
- Must be restricted to authorized administrators.

## 4.6 Audit Logs

**URL:** `/admin/audit-logs`

**Purpose:**
- View administrative and security actions.
- Filter by user, action, or date.
- Must be read-only for most administrators.

## 4.7 Analytics

**URL:** `/admin/analytics`

**Purpose:**
- View privacy-conscious usage metrics.
- Tool usage, search queries, resource views.
- Must not display fake data.

---

# 5. URL Strategy and SEO

## 5.1 URL Conventions

- Use lowercase letters.
- Use hyphens to separate words.
- Avoid underscores.
- Avoid trailing slashes.
- Use descriptive, keyword-rich slugs.
- Avoid numeric IDs in public URLs where possible (use slugs).

## 5.2 Canonical URLs

- Every public page must have a canonical URL.
- Search result pages may use `noindex` or canonicalization to avoid duplicate content issues, depending on SEO strategy.

## 5.3 Sitemap

- An XML sitemap must be generated automatically.
- The sitemap must include only published, public, indexable resources.
- The sitemap must exclude admin routes, auth routes, and legal pages if desired.
- The sitemap must update when content is published or unpublished.

## 5.4 Robots.txt

- The `robots.txt` file must allow crawling of public resources.
- The `robots.txt` file must disallow `/admin/`, `/auth/`, and `/search` if desired.
- The `robots.txt` file must not accidentally block the entire site.

---

# 6. Navigation Structure

## 6.1 Main Navigation (Public)

The main navigation should provide direct access to core resource areas.

**Items:**
- Home
- Tools
- Calculators
- Reference Ranges
- SOPs
- Knowledge
- Search (Icon or dedicated link)

## 6.2 Footer Navigation (Public)

The footer should provide access to legal and secondary information.

**Items:**
- About LabGato (optional, can be a Knowledge page)
- Privacy Policy
- Terms of Service
- Disclaimer
- Accessibility Statement
- Contact (optional, future)
- Admin Login (subtle link)

## 6.3 Admin Navigation

The admin navigation should be sidebar-based for efficiency.

**Items:**
- Dashboard
- Tools
- Calculators
- Reference Ranges
- SOPs
- Interpretation
- Knowledge
- Taxonomy
- Users & Roles
- Audit Logs
- Analytics
- Settings

---

# 7. Breadcrumb Strategy

Breadcrumbs must be used on all resource detail pages to improve navigation and SEO.

**Example Structure:**
Home > Calculators > Hematology > Absolute Neutrophil Count

**Implementation Rules:**
- Breadcrumbs must be structured data (Schema.org BreadcrumbList).
- Breadcrumbs must be clickable.
- Breadcrumbs must reflect the actual hierarchy.

---

# 8. Search Integration Points

Search must be accessible from:

- The home page hero section.
- The main navigation (search icon or input).
- Resource listing pages (filter/search bar).
- The Admin Portal (global search for resources, future).

Search results must clearly indicate the resource type.

---

# Document Completion Check

## Requirements Covered

- Public site structure defined.
- Admin portal structure defined.
- URL strategy defined.
- Navigation structure defined.
- Breadcrumb strategy defined.
- Search integration points defined.
- Access model defined.
- SEO implications defined.

## Decisions Established

- Public site uses slug-based URLs.
- Admin portal uses `/admin` prefix.
- Legal pages are under `/legal`.
- Authentication pages are under `/auth`.
- Main navigation focuses on core resource areas.
- Admin navigation is sidebar-based.
- Breadcrumbs are mandatory for resource detail pages.
- Sitemap must exclude admin and auth routes.

## Dependencies

- Approved LabGato Master Directive.
- Approved Product Scope and Non-Goals.
- Approved Core Product Areas.
- Approved Technology Stack.

## Files Affected

This document creates:

01_Product_Foundation_and_Master_Architecture/10_Information_Architecture.md

It directly dictates the routing structure for the backend and the page hierarchy for the frontend.

## Open Questions

1. Should the "About" page be a standalone page or a Knowledge article?
2. Should the search results page be indexable or `noindex`?
3. Should admin login be accessible from the public footer or a hidden route?
4. What is the exact hierarchy for categories and subcategories?

## TBD Decisions

- TBD: Final decision on search page indexing.
- TBD: Final category hierarchy structure.
- TBD: Final footer link list.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE01-DOC10

## Context

LabGato requires a clear Information Architecture to ensure that all public resources and administrative features are organized logically and are easy to navigate. This document defines the page structure, URL strategy, and navigation hierarchy.

This prompt implements the documentation outcome of the Information Architecture document.

## Objective

Create the official LabGato Information Architecture document inside the approved Phase 01 documentation structure.

After this prompt is completed, the repository will have a binding blueprint for routing, navigation, and page hierarchy.

## Existing Architecture

Relevant approved decisions:

- LabGato is a professional laboratory toolkit.
- Public resources use slug-based URLs.
- Admin portal uses `/admin` prefix.
- SEO and accessibility are mandatory.
- Shared hosting constraints apply.

## Files to Inspect

Inspect, if available:

- docs/
- 01_Product_Foundation_and_Master_Architecture/
- Documents 01 through 09.

## Files to Create

Create:

01_Product_Foundation_and_Master_Architecture/10_Information_Architecture.md

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
- Ensure admin routes are clearly marked as protected.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

This document defines the SEO URL strategy. Ensure that slug conventions and sitemap rules are clearly documented.

## Analytics Requirements

No analytics events required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the public and admin URL structures.
- The file defines the navigation hierarchy.

## Integration Requirements

This document must be used as the primary reference when defining routes in the Backend Architecture and pages in the Frontend Architecture.

## Definition of Done

This implementation is complete when:

- The Information Architecture file exists.
- The file matches the approved Phase 01 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.