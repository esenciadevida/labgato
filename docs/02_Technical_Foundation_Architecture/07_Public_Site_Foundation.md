# 07 — Public Site Foundation

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Information Architecture, Target Users and Journeys, Core Product Areas).
- The approved Laravel Application Structure document.
- The approved Environment and Configuration document.
- The approved Technology Stack document.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The public website layout architecture.
- The public header and navigation structure.
- The public footer structure.
- The home page shell requirements.
- The legal page foundation.
- The public error page foundation.
- The public resource layout foundation.
- The accessibility and responsiveness requirements for the public site.

## Affects

This document affects:

- The SEO and Accessibility Foundation document.
- The Testing Foundation document.
- All future vertical-slice public pages.
- All future public Blade views.
- All future public Livewire components.
- The sitemap and robots.txt generation.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the visual and functional foundation of the public website.
- Ensures a professional, accessible, and SEO-friendly experience for laboratory professionals.
- Prevents disconnected or orphaned public pages.
- Provides reusable UI patterns for all future resource pages.

---

# 1. Purpose

This document establishes the Public Site Foundation for LabGato.

The public website is the primary interface for laboratory professionals, students, and technicians to access tools, calculators, reference ranges, and educational resources.

This foundation ensures that the public site is fast, accessible, trustworthy, and easy to navigate, while respecting the constraints of the Hostinger shared hosting environment.

---

# 2. Public Layout Architecture

The public site must use a dedicated layout that is distinct from the Admin Portal.

## 2.1 Layout File

The primary public layout must be located at:

resources/views/layouts/public.blade.php

## 2.2 Layout Structure

The layout must include:

- A responsive header with primary navigation.
- A main content area with appropriate padding and max-width constraints.
- A footer with legal links and secondary navigation.
- A skip-to-content link for accessibility.

## 2.3 Responsive Behavior

The public site must be fully responsive.

- Desktop: Full navigation visible.
- Tablet: Navigation may collapse or adapt.
- Mobile: Off-canvas or hamburger navigation.

Laboratory users may access the site on mobile devices, tablets, or desktop workstations.

## 2.4 Color and Typography

The public interface should use a clean, scientific, and professional design system.

- Avoid excessive gradients, glassmorphism, or decorative clutter.
- Use high-contrast text for readability.
- Use clear visual hierarchy for headings, labels, and content.
- Use Tailwind CSS for styling.
- Ensure fast loading times by minimizing unnecessary JavaScript.

---

# 3. Header and Navigation

The public header must provide clear access to the core product areas.

## 3.1 Header Elements

The header must include:

- LabGato logo or site name.
- Primary navigation menu.
- Search access (icon or input).
- Mobile navigation toggle.

## 3.2 Primary Navigation Items

The primary navigation must include links to:

- Home
- Tools
- Calculators
- Reference Ranges
- SOPs
- Knowledge
- Search

Navigation items must only appear if the corresponding module is implemented and approved.

## 3.3 Active State

The current page or section must be clearly highlighted in the navigation.

## 3.4 No Dead Links

Navigation items must not link to unimplemented pages unless those pages display a clear "Coming Soon" or "Not Found" state.

---

# 4. Footer

The public footer must provide access to legal and secondary information.

## 4.1 Footer Elements

The footer must include:

- Site name and brief description.
- Legal links (Privacy Policy, Terms of Service, Disclaimer, Accessibility Statement).
- Copyright notice.
- Subtle Admin Login link (optional, can be hidden or placed in a utility menu).

## 4.2 Legal Links

Legal links must point to valid, published legal pages.

## 4.3 No Fake Content

The footer must not display fake social media links, fake contact information, or unapproved statistics.

---

# 5. Home Page Shell

The home page is the entry point for most visitors.

## 5.1 Home Route

Route: /
Permission: Public

## 5.2 Home Content

The home page must introduce LabGato and provide quick access to resources.

Initial home page sections may include:

- Hero section with value proposition.
- Search bar.
- Resource category grid (Tools, Calculators, References, SOPs, Knowledge).
- Featured or recently updated resources (if data exists).
- Brief explanation of LabGato's purpose.

## 5.3 Empty States

If no resources exist yet, the home page must display a clear empty state or placeholder content that is honest and professional.

Fake statistics or fake testimonials are prohibited.

## 5.4 Performance

The home page must load quickly.

- Avoid heavy images or scripts.
- Optimize critical rendering path.
- Use lazy loading for non-critical images.

---

# 6. Legal Pages

Legal pages are required for compliance and trust.

## 6.1 Legal Routes

Routes:

- /legal/privacy-policy
- /legal/terms-of-service
- /legal/disclaimer
- /legal/accessibility-statement
- /legal/cookie-policy

## 6.2 Legal Content

Legal content must be stored in a way that allows administrators to update it.

Options:

- Static Blade views (for MVP).
- Database-driven pages (if admin editing is required).

For the foundation, static Blade views are acceptable, but the architecture must allow future migration to dynamic content.

## 6.3 Legal Disclaimers

Legal pages must include clear disclaimers that LabGato is not a diagnostic tool and does not replace professional clinical judgment.

---

# 7. Error Pages

Error pages must be safe, branded, and helpful.

## 7.1 Error Routes

Routes:

- /404
- /500
- /403
- /419
- /429
- /503

## 7.2 Error Content

Error pages must include:

- A clear error message.
- A link back to the home page.
- A link to search or support resources.

## 7.3 Production Safety

In production, error pages must not expose:

- Stack traces.
- Database credentials.
- Environment variables.
- Sensitive application data.

---

# 8. Public Resource Layout Foundation

Future resource pages (Calculators, Tools, Reference Ranges, etc.) must follow a consistent layout.

## 8.1 Listing Page Layout

Purpose: Display a list of resources.

Required elements:

- Page title.
- Search or filter input.
- Resource grid or table.
- Pagination controls.
- Empty state if no resources exist.

## 8.2 Detail Page Layout

Purpose: Display a single resource.

Required elements:

- Resource title.
- Breadcrumb navigation.
- Resource description or content.
- Related resources section.
- SEO metadata.
- Structured data where appropriate.

## 8.3 Breadcrumbs

Breadcrumbs must be used on all resource detail pages.

Example:

Home > Calculators > Hematology > Absolute Neutrophil Count

Breadcrumbs must be structured data (Schema.org BreadcrumbList).

## 8.4 Related Resources

Resource pages should link to related resources to improve navigation and SEO.

Examples:

- A Calculator page may link to related Reference Ranges.
- A Reference Range page may link to related Interpretation resources.

---

# 9. Accessibility and Responsiveness (Public)

## 9.1 Keyboard Navigation

All public pages must be usable via keyboard.

- Focus states must be visible.
- Tab order must be logical.
- Interactive elements must be accessible.

## 9.2 Screen Reader Support

- Form inputs must have associated labels.
- Buttons must have accessible text.
- Images must have alt text.
- Status messages should use aria-live regions.

## 9.3 Touch Targets

Interactive elements must be large enough for touch interaction.

## 9.4 Contrast

Text and background contrast must meet WCAG 2.2 AA standards.

## 9.5 Reduced Motion

Animations must respect the user's reduced motion preference.

---

# 10. Shared Hosting Constraints

## 10.1 Asset Loading

Public assets must be compiled and optimized.

- Do not load unnecessary JavaScript on public pages.
- Use Vite's code splitting where appropriate.
- Compress images.

## 10.2 Database Load

Public queries must be optimized.

- Use pagination for listing pages.
- Cache expensive queries where appropriate.
- Avoid N+1 queries.

## 10.3 No Server-Side Node

The public site must not rely on Node.js for rendering.

All rendering must be done via Laravel Blade or client-side JavaScript.

---

# Document Completion Check

## Requirements Covered

- Public layout architecture defined.
- Header and navigation structure defined.
- Footer structure defined.
- Home page shell requirements defined.
- Legal page foundation defined.
- Error page foundation defined.
- Public resource layout foundation defined.
- Accessibility and responsiveness requirements defined.
- Shared hosting constraints addressed.

## Decisions Established

- Public site uses a dedicated layout.
- Navigation focuses on core resource areas.
- Legal pages are required.
- Error pages must be safe and branded.
- Resource pages must use breadcrumbs and related resources.
- Public UI must be accessible and responsive.
- Fake content is prohibited.

## Dependencies

- Approved Phase 01 Information Architecture.
- Approved Laravel Application Structure.
- Approved Technology Stack.
- Approved Deployment Constraints.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/07_Public_Site_Foundation.md

It directly dictates the structure of all future public Blade views and components.

## Open Questions

1. Should the home page feature dynamic content or static content in the MVP?
2. Should legal pages be database-driven or static Blade views?
3. What is the default pagination size for public listing pages?

## TBD Decisions

- TBD: Final decision on home page content strategy.
- TBD: Final decision on legal page storage.
- TBD: Final default pagination size for public pages.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE02-DOC07

## Context

LabGato requires a professional, accessible, and SEO-friendly public website. This document defines the layout, navigation, home page, legal pages, error pages, and resource layout rules that will serve as the foundation for all future public pages.

This prompt implements the documentation outcome of the Public Site Foundation document.

## Objective

Create the official LabGato Public Site Foundation document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have a binding UI/UX specification for the public website.

## Existing Architecture

Relevant approved decisions:

- LabGato uses Laravel, Blade, Tailwind, and Livewire.
- Public site is accessible to all visitors.
- Navigation focuses on core resource areas.
- Accessibility and SEO are mandatory.
- Fake content is prohibited.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 through 06.
- Any existing public views or components.

## Files to Create

Create:

02_Technical_Foundation_Architecture/07_Public_Site_Foundation.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI requirements.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that error pages do not expose sensitive data.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

This document defines the public site structure. Ensure that breadcrumbs and related resources are documented for SEO.

## Analytics Requirements

No analytics events required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the public layout and navigation.
- The file defines the mandatory UI states.

## Integration Requirements

This document must be used as the primary reference when creating the public layout, header, footer, and base components.

## Definition of Done

This implementation is complete when:

- The Public Site Foundation file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.