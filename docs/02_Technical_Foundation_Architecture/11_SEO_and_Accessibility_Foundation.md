# 11 — SEO and Accessibility Foundation

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Information Architecture, Target Users and Journeys).
- The approved Laravel Application Structure document.
- The approved Public Site Foundation document.
- The approved Admin Portal Foundation document.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The baseline Search Engine Optimization (SEO) strategy for public pages.
- The metadata, structured data, and indexing rules.
- The sitemap and robots.txt generation strategy.
- The baseline Accessibility (a11y) standards targeting WCAG 2.2 AA.
- The semantic HTML, keyboard navigation, and form accessibility rules.
- The specific accessibility requirements for Livewire components and the Admin Portal.
- The testing and verification strategy for SEO and accessibility.

## Affects

This document affects:

- The Public Site Foundation implementation.
- The Admin Portal Foundation implementation.
- All future public-facing Blade views and Livewire components.
- All future administrative forms and modals.
- The Deployment and Release Process (e.g., sitemap generation).
- The Testing Foundation (Lighthouse/Axe integration).

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the mandatory SEO and accessibility baseline that must be met before any public or admin page is marked "Complete".
- Ensures laboratory professionals can discover resources via search engines.
- Ensures the platform is usable by individuals relying on assistive technologies.
- Prevents the creation of inaccessible Livewire components or SEO-hostile SPA patterns.

---

# 1. Purpose

This document establishes the SEO and Accessibility Foundation for LABGOTO.

As a professional toolkit for laboratory scientists and students, LABGOTO must be easily discoverable by search engines and fully accessible to all users, including those using screen readers or keyboard-only navigation. 

Because LABGOTO uses server-side rendering (Blade) with progressive enhancement (Alpine.js/Livewire), it is uniquely positioned to achieve excellent SEO and accessibility out of the box, provided strict foundational rules are followed.

---

# 2. SEO Foundation Strategy

## 2.1 Meta Tags and Head Section

Every public page must include a standardized `<head>` section managed by a dedicated Blade component (e.g., `<x-public.meta />`).

Required meta tags:

- `<title>`: Unique, descriptive, and under 60 characters where possible. Format: `[Page Title] | LABGOTO`.
- `<meta name="description">`: Unique, compelling summary under 160 characters.
- `<link rel="canonical">`: Absolute URL to prevent duplicate content issues.
- `<meta name="robots">`: `index, follow` for public resources; `noindex, nofollow` for admin, auth, and search result pages.

## 2.2 Open Graph and Social Sharing

Public resource pages must include Open Graph (OG) tags to ensure proper rendering when shared on social platforms or messaging apps.

Required OG tags:

- `og:title`
- `og:description`
- `og:url`
- `og:type` (e.g., `website`, `article`)
- `og:image` (Fallback to a default LABGOTO branding image if no specific resource image exists).

## 2.3 Heading Hierarchy

- Every page must have exactly one `<h1>` tag that describes the primary content of the page.
- Subsequent headings must follow a logical order (`<h2>`, `<h3>`, etc.) without skipping levels.
- Headings must not be used purely for visual styling (e.g., using `<h4>` just to make text smaller).

## 2.4 Structured Data (JSON-LD)

Public pages must include JSON-LD structured data where appropriate to enhance search engine understanding.

Initial approved schemas:

- `WebSite`: On the home page, including `SearchAction` for the sitelinks search box.
- `BreadcrumbList`: On all resource detail pages.
- `Article` or `TechArticle`: On Knowledge and Interpretation resources.
- `SoftwareApplication`: On the home page or about page to describe the toolkit itself.

## 2.5 URL Structure and Canonicalization

- URLs must be lowercase, use hyphens for word separation, and avoid trailing slashes.
- Query parameters used for filtering or sorting (e.g., `?sort=latest`) should either be blocked via `robots.txt` or use canonical tags pointing to the base URL to prevent duplicate content penalties.

## 2.6 Image Optimization and Alt Text

- All `<img>` tags must have an `alt` attribute.
- Decorative images must use `alt=""` and `aria-hidden="true"`.
- Informative images must have descriptive alt text.
- Images must use modern formats (WebP) with fallbacks, and include `width` and `height` attributes to prevent Cumulative Layout Shift (CLS).

## 2.7 Sitemap and Robots.txt

- **Sitemap**: An XML sitemap must be generated automatically (e.g., using a Laravel package like `spatie/laravel-sitemap`). It must only include published, public, indexable URLs.
- **Robots.txt**: Must explicitly allow search engines to crawl public assets while disallowing `/admin`, `/auth`, `/api`, and `/search`.

---

# 3. Accessibility (a11y) Foundation Strategy

LABGOTO targets **WCAG 2.2 Level AA** compliance for all public and administrative interfaces.

## 3.1 Semantic HTML

- Use native HTML elements whenever possible (`<button>`, `<a>`, `<nav>`, `<main>`, `<header>`, `<footer>`, `<aside>`).
- Do not use `<div>` or `<span>` with click handlers as buttons. Use `<button type="button">`.
- Use `<ul>` and `<ol>` for lists, not styled `<div>` containers.

## 3.2 Keyboard Navigation and Focus Management

- All interactive elements must be reachable and operable via keyboard (Tab, Enter, Space, Escape, Arrow keys).
- Focus states must be clearly visible. Do not use `outline: none` without providing a highly visible alternative focus ring.
- **Skip Links**: A "Skip to main content" link must be the first focusable element on public pages.
- **Modals and Dialogs**: When a modal opens, focus must move to the modal. When it closes, focus must return to the trigger element. Focus must be trapped inside the modal while it is open.

## 3.3 Forms and Inputs

- Every `<input>`, `<select>`, and `<textarea>` must have an explicitly associated `<label>` using the `for` and `id` attributes.
- Placeholder text must not replace labels.
- Required fields must be indicated visually and programmatically (e.g., `aria-required="true"` or the `required` attribute).
- Validation errors must be associated with the input using `aria-describedby` and `aria-invalid="true"`.

## 3.4 Color and Contrast

- Text and background contrast must meet WCAG AA ratios (4.5:1 for normal text, 3:1 for large text).
- Color must not be the only means of conveying information (e.g., error states must use icons and text, not just red borders).

## 3.5 Touch Targets and Motion

- Interactive elements on mobile must have a minimum touch target size of 44x44 CSS pixels.
- Animations and transitions must respect the `prefers-reduced-motion` media query. If a user prefers reduced motion, non-essential animations must be disabled.

---

# 4. Livewire and Admin Portal Accessibility

Livewire introduces specific accessibility challenges because it updates the DOM asynchronously.

## 4.1 Livewire Loading States

- When a Livewire component is loading or submitting, the triggering button must be disabled and announce its state to screen readers (e.g., `aria-busy="true"` or changing text to "Saving...").
- Use `wire:loading.attr="disabled"` to prevent double submissions.

## 4.2 Dynamic Content Updates

- When Livewire injects new content into the page (e.g., adding a row to a table, showing a success message), the update must be announced to screen readers using `aria-live="polite"` or `role="status"` regions.

## 4.3 Admin Tables and Data Grids

- Admin data tables must use proper `<table>`, `<thead>`, `<tbody>`, `<th>`, and `<td>` markup.
- `<th>` elements must have `scope="col"` or `scope="row"`.
- Action buttons inside tables must have accessible labels (e.g., `aria-label="Edit Calculator: Anion Gap"` instead of just `aria-label="Edit"`).

---

# 5. Shared Hosting Constraints

## 5.1 Sitemap Generation Load

Generating a sitemap on shared hosting can cause CPU spikes if the database is queried heavily.

- Sitemap generation must be queued as a background job or run via a scheduled CLI command, not triggered by a public web request.
- The generated `sitemap.xml` must be cached to the filesystem.

## 5.2 Client-Side Rendering

Because LABGOTO avoids heavy client-side JS frameworks (like React/Vue SPAs) in favor of Blade and Livewire, SEO is inherently protected from the "empty DOM" problem. No additional SSR (Server-Side Rendering) infrastructure is required.

---

# 6. Testing and Verification Requirements

## 6.1 Automated Accessibility Testing

- Integrate tools like `axe-core` or Laravel Dusk with accessibility assertions into the CI pipeline or local testing workflow.
- Run Lighthouse CI on key public pages (Home, Calculator Detail, Search) to enforce minimum SEO and Accessibility scores (e.g., > 90).

## 6.2 Manual Testing

- Developers must manually test keyboard navigation for all new Livewire components and modals.
- Screen reader testing (using NVDA, VoiceOver, or JAWS) should be performed on critical user journeys (e.g., executing a calculator, creating a resource).

---

# Document Completion Check

## Requirements Covered

- Meta tags and head section rules defined.
- Open Graph and structured data strategy defined.
- Heading hierarchy and URL rules defined.
- Sitemap and robots.txt strategy defined.
- Semantic HTML and keyboard navigation rules defined.
- Form accessibility rules defined.
- Color contrast and motion rules defined.
- Livewire-specific accessibility rules defined.
- Shared hosting constraints for SEO addressed.
- Testing and verification requirements defined.

## Decisions Established

- Target compliance is WCAG 2.2 AA.
- Blade components will manage global SEO meta tags.
- Sitemap generation must be queued/cached to respect shared hosting limits.
- Livewire updates must use `aria-live` regions for screen reader announcements.
- "Skip to main content" links are mandatory on public layouts.
- Color alone cannot convey state or errors.

## Dependencies

- Approved Phase 01 Information Architecture.
- Approved Public Site Foundation.
- Approved Admin Portal Foundation.
- Approved Hostinger constraint.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/11_SEO_and_Accessibility_Foundation.md

It dictates the HTML structure, meta tag implementation, and accessibility attributes for all future Blade views and Livewire components.

## Open Questions

1. Should the sitemap package be `spatie/laravel-sitemap` or a custom generator?
2. Should we enforce automated Lighthouse CI checks in GitHub Actions for every PR?

## TBD Decisions

- TBD: Final selection of Laravel sitemap package.
- TBD: Final CI/CD integration for automated a11y/SEO scoring.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE02-DOC11

## Context

LABGOTO must be discoverable by search engines and fully accessible to all users, including those using assistive technologies. This document defines the strict SEO and WCAG 2.2 AA baseline rules that govern all public and administrative interfaces.

This prompt implements the documentation outcome of the SEO and Accessibility Foundation document.

## Objective

Create the official LABGOTO SEO and Accessibility Foundation document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have a binding specification for HTML semantics, meta tags, and accessibility behaviors.

## Existing Architecture

Relevant approved decisions:

- LABGOTO uses server-side rendered Blade views.
- Target accessibility standard is WCAG 2.2 AA.
- Sitemap generation must respect shared hosting CPU limits.
- Livewire components must manage focus and announce dynamic updates.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 through 10.
- Any existing layout files or meta components.

## Files to Create

Create:

02_Technical_Foundation_Architecture/11_SEO_and_Accessibility_Foundation.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI/HTML requirements.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.

## Accessibility Requirements

The Markdown document itself should use clear heading hierarchy and readable list structures.

## SEO Requirements

This document defines the SEO strategy. Ensure canonical and robots rules are clearly documented.

## Analytics Requirements

No analytics events required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file explicitly defines WCAG 2.2 AA as the target.
- The file defines Livewire focus and aria-live rules.

## Integration Requirements

This document must be used as the primary reference when creating Blade layouts, meta components, and interactive Livewire forms.

## Definition of Done

This implementation is complete when:

- The SEO and Accessibility Foundation file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.