LABGOTO/
├── app/
│   └── Http/
│       └── Controllers/
│           └── PublicSite/
│               ├── HomeController.php
│               └── LegalController.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── public.blade.php
│       ├── components/
│       │   └── public/
│       │       ├── header.blade.php
│       │       ├── footer.blade.php
│       │       ├── navigation.blade.php
│       │       ├── meta.blade.php
│       │       └── breadcrumbs.blade.php
│       ├── public/
│       │   └── home.blade.php
│       ├── legal/
│       │   ├── privacy-policy.blade.php
│       │   ├── terms-of-service.blade.php
│       │   ├── disclaimer.blade.php
│       │   ├── accessibility-statement.blade.php
│       │   └── cookie-policy.blade.php
│       └── errors/
│           ├── 403.blade.php
│           ├── 404.blade.php
│           ├── 419.blade.php
│           ├── 429.blade.php
│           ├── 500.blade.php
│           └── 503.blade.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── PublicSite/
            ├── HomePageTest.php
            ├── LegalPagesTest.php
            └── ErrorPagesTest.php


IMPLEMENTATION PROMPT ID: LABGOTO-SLICE1-BUILD6

CONTEXT:
LABGOTO is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Build 1 through Build 5 are complete. The Laravel foundation, database schema, authentication, RBAC, and Admin Portal foundation are implemented.

We are now implementing Build 6: Public Site Foundation.

This build creates the public website layout, navigation, home page shell, legal pages, and error pages.

This build does NOT implement resource listing pages or resource detail pages. Resource pages will be built in later vertical slices.

OBJECTIVE:
Create the public site layout, header, footer, home page shell, legal pages, error pages, and public meta/breadcrumbs components.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Public site uses a dedicated layout distinct from the Admin Portal.
- Public navigation focuses on core resource areas.
- Legal pages are required.
- Error pages must be safe and branded.
- Resource pages must use breadcrumbs and related resources in future slices.
- Public UI must be accessible and responsive.
- Fake content is prohibited.
- Public pages should be SEO-friendly.
- Admin and auth pages must not be linked prominently from public navigation.

FILES TO INSPECT:
- resources/views/layouts/admin.blade.php
- resources/views/components/ui/
- routes/web.php
- config/app.php
- config/LABGOTO.php
- public/robots.txt
- tests/Feature/

FILES TO CREATE:
- app/Http/Controllers/PublicSite/HomeController.php
- app/Http/Controllers/PublicSite/LegalController.php
- resources/views/layouts/public.blade.php
- resources/views/components/public/header.blade.php
- resources/views/components/public/footer.blade.php
- resources/views/components/public/navigation.blade.php
- resources/views/components/public/meta.blade.php
- resources/views/components/public/breadcrumbs.blade.php
- resources/views/public/home.blade.php
- resources/views/legal/privacy-policy.blade.php
- resources/views/legal/terms-of-service.blade.php
- resources/views/legal/disclaimer.blade.php
- resources/views/legal/accessibility-statement.blade.php
- resources/views/legal/cookie-policy.blade.php
- resources/views/errors/403.blade.php
- resources/views/errors/404.blade.php
- resources/views/errors/419.blade.php
- resources/views/errors/429.blade.php
- resources/views/errors/500.blade.php
- resources/views/errors/503.blade.php
- tests/Feature/PublicSite/HomePageTest.php
- tests/Feature/PublicSite/LegalPagesTest.php
- tests/Feature/PublicSite/ErrorPagesTest.php

FILES TO MODIFY:
- routes/web.php
- resources/css/app.css if public-specific styles are needed
- resources/js/app.js if Alpine components are needed for mobile navigation

PUBLIC LAYOUT REQUIREMENTS:

Create resources/views/layouts/public.blade.php.

Layout structure:
- HTML5 document.
- Meta charset.
- Meta viewport.
- CSRF token.
- Page title slot.
- Meta component for SEO.
- Tailwind CSS compiled assets.
- Alpine.js.
- Skip to main content link for accessibility.
- Public header.
- Main content area.
- Public footer.

Layout behavior:
- Desktop: full navigation visible.
- Tablet: navigation may collapse or adapt.
- Mobile: off-canvas or hamburger navigation.
- Focus states must be visible.
- Main content area must have appropriate padding and max-width.

Layout slots:
- title
- meta_description
- content
- Optional scripts

The layout must not include admin navigation.

PUBLIC META COMPONENT REQUIREMENTS:

Create resources/views/components/public/meta.blade.php.

Component props:
- title: string
- description: string
- canonical: optional string
- ogType: optional string, default website
- ogImage: optional string
- noindex: boolean, default false

Component behavior:
- Render title tag.
- Render meta description.
- Render canonical link.
- Render Open Graph tags.
- Render robots meta tag.
- If noindex is true, render noindex, nofollow.
- Use config defaults where values are not provided.

PUBLIC HEADER REQUIREMENTS:

Create resources/views/components/public/header.blade.php.

Header must include:
- LABGOTO logo or site name.
- Primary navigation menu.
- Mobile navigation toggle.

Header must not include:
- Admin dashboard link prominently.
- Fake notifications.
- Fake user accounts.

A subtle Admin Login link may be placed in the footer only.

PUBLIC NAVIGATION REQUIREMENTS:

Create resources/views/components/public/navigation.blade.php.

Primary navigation items for Build 6:
- Home

Future navigation items to prepare structure for:
- Tools
- Calculators
- Reference Ranges
- SOPs
- Knowledge

For Build 6:
- Home link must point to public.home route.
- Future resource links may be hidden or shown as coming soon placeholders.
- Do not create resource pages yet.
- Do not create dead links.

Navigation behavior:
- Active state must be clearly highlighted.
- Use aria-current="page" for active page.
- Mobile navigation must be keyboard accessible.
- Mobile navigation must support Escape key to close.

PUBLIC FOOTER REQUIREMENTS:

Create resources/views/components/public/footer.blade.php.

Footer must include:
- Site name and brief description.
- Legal links:
  - Privacy Policy
  - Terms of Service
  - Disclaimer
  - Accessibility Statement
  - Cookie Policy
- Copyright notice.
- Subtle Admin Login link.

Footer must not include:
- Fake social media links.
- Fake contact information.
- Fake statistics.
- Fake testimonials.

PUBLIC BREADCRUMBS COMPONENT REQUIREMENTS:

Create resources/views/components/public/breadcrumbs.blade.php.

Component props:
- items: array of breadcrumb items

Each breadcrumb item should support:
- label
- url: optional, last item may have no URL
- current: boolean

Behavior:
- Render semantic nav element with aria-label="Breadcrumb".
- Render ordered list.
- Last item should be marked with aria-current="page".
- Use Schema.org BreadcrumbList JSON-LD where practical.

For Build 6, breadcrumbs are only required on legal pages if desired. They will be used heavily in future resource pages.

HOME PAGE REQUIREMENTS:

Create app/Http/Controllers/PublicSite/HomeController.php.

HomeController should:
- Return the public home view.
- Provide page title and meta description.

Create resources/views/public/home.blade.php.

Home page requirements:
- Use public layout.
- Display hero section with LABGOTO value proposition.
- Display search placeholder area if practical.
- Display resource category grid with placeholder cards for future modules.
- Display brief explanation of LABGOTO purpose.
- Do not display fake statistics.
- Do not display fake testimonials.
- Do not display fake resource counts.
- Do not link to unimplemented resource pages unless they show a safe coming-soon state.

Hero section should include:
- Clear headline.
- Short description.
- Primary call-to-action, such as "Explore Resources" or "Learn More".
- If the call-to-action points to a future module, use a safe placeholder or scroll to category section.

Resource category grid may include cards for:
- Laboratory Tools
- Calculators
- Reference Ranges
- SOP Resources
- Result Interpretation
- Knowledge & Education

For Build 6:
- Cards may be informational only.
- Cards must not link to dead pages.
- Cards may display "Coming Soon" or link to a future placeholder if approved.
- Do not create fake resource listings.

LEGAL PAGES REQUIREMENTS:

Create app/Http/Controllers/PublicSite/LegalController.php.

LegalController should provide methods for:
- privacyPolicy
- termsOfService
- disclaimer
- accessibilityStatement
- cookiePolicy

Create legal views under resources/views/legal/.

Legal pages must:
- Use public layout.
- Include clear page title.
- Include last updated date placeholder.
- Include honest placeholder legal content.
- Include disclaimer that LABGOTO is not a diagnostic tool.
- Include disclaimer that LABGOTO does not replace professional clinical judgment.
- Be accessible and readable.

Do not fabricate legal compliance claims.

Legal content should state clearly that final legal text must be reviewed before production.

ERROR PAGES REQUIREMENTS:

Create error views under resources/views/errors/.

Required error pages:
- 403.blade.php
- 404.blade.php
- 419.blade.php
- 429.blade.php
- 500.blade.php
- 503.blade.php

Error page requirements:
- Use public layout or a minimal error layout.
- Display clear error title.
- Display safe user-friendly message.
- Include link back to home page.
- Include link to search or main resources where practical.
- Do not expose stack traces.
- Do not expose environment variables.
- Do not expose database errors.
- Do not expose sensitive application data.

Specific error messages:

403:
- Title: Access Denied
- Message: You do not have permission to access this page.

404:
- Title: Page Not Found
- Message: The page you are looking for does not exist or may have been moved.

419:
- Title: Session Expired
- Message: Your session has expired. Please refresh the page and try again.

429:
- Title: Too Many Requests
- Message: You have made too many requests. Please wait a moment and try again.

500:
- Title: Something Went Wrong
- Message: An unexpected error occurred. Please try again later.

503:
- Title: Service Unavailable
- Message: The service is temporarily unavailable. Please try again later.

ROUTES REQUIREMENTS:

Modify routes/web.php.

Required public routes:

GET /
- Name: public.home
- Controller: PublicSite\HomeController@index

GET /legal/privacy-policy
- Name: public.legal.privacy-policy
- Controller: PublicSite\LegalController@privacyPolicy

GET /legal/terms-of-service
- Name: public.legal.terms-of-service
- Controller: PublicSite\LegalController@termsOfService

GET /legal/disclaimer
- Name: public.legal.disclaimer
- Controller: PublicSite\LegalController@disclaimer

GET /legal/accessibility-statement
- Name: public.legal.accessibility-statement
- Controller: PublicSite\LegalController@accessibilityStatement

GET /legal/cookie-policy
- Name: public.legal.cookie-policy
- Controller: PublicSite\LegalController@cookiePolicy

Do not create resource routes yet.

ACCESSIBILITY REQUIREMENTS:

Public layout must:
- Include skip to main content link.
- Use semantic HTML landmarks.
- Use visible focus states.
- Support keyboard navigation.
- Use ARIA labels where appropriate.
- Use aria-current="page" for active navigation.
- Use sufficient color contrast.
- Support reduced motion preferences.

Mobile navigation must:
- Be keyboard accessible.
- Have accessible label.
- Support Escape key to close.
- Return focus to toggle button when closed.

Home page must:
- Use one h1 tag.
- Use logical heading hierarchy.
- Provide alt text for images if images are used.
- Avoid autoplaying media.

Legal pages must:
- Use readable line height.
- Use clear heading structure.
- Be keyboard accessible.

Error pages must:
- Be readable.
- Provide clear navigation back to home.
- Not trap users.

SEO REQUIREMENTS:

Public home page must include:
- Unique title.
- Meta description.
- Canonical URL.
- Open Graph tags.

Legal pages may be indexable or noindex depending on preference. For Build 6, legal pages may be indexable.

Error pages must not be indexable.

Ensure public/robots.txt remains configured to disallow:
- /admin
- /auth
- /api

RESPONSIVE REQUIREMENTS:

Public layout must work on:
- Desktop
- Tablet
- Mobile

Mobile behavior:
- Navigation collapses into mobile menu.
- Hero section remains readable.
- Resource category grid stacks vertically.
- Footer remains usable.
- Touch targets are large enough.

TESTING REQUIREMENTS:

Create tests/Feature/PublicSite/HomePageTest.php.

Required tests:

1. test_home_page_renders_successfully
   - Visit /.
   - Assert response is successful.
   - Assert page title or hero heading is visible.

2. test_home_page_includes_public_navigation
   - Visit /.
   - Assert Home navigation link is visible.

3. test_home_page_includes_footer_legal_links
   - Visit /.
   - Assert Privacy Policy link is visible.
   - Assert Terms of Service link is visible.
   - Assert Disclaimer link is visible.
   - Assert Accessibility Statement link is visible.

4. test_home_page_does_not_display_fake_statistics
   - Visit /.
   - Assert no fake statistic placeholders are present.

Create tests/Feature/PublicSite/LegalPagesTest.php.

Required tests:

1. test_privacy_policy_page_renders_successfully
   - Visit /legal/privacy-policy.
   - Assert response is successful.

2. test_terms_of_service_page_renders_successfully
   - Visit /legal/terms-of-service.
   - Assert response is successful.

3. test_disclaimer_page_renders_successfully
   - Visit /legal/disclaimer.
   - Assert response is successful.

4. test_accessibility_statement_page_renders_successfully
   - Visit /legal/accessibility-statement.
   - Assert response is successful.

5. test_cookie_policy_page_renders_successfully
   - Visit /legal/cookie-policy.
   - Assert response is successful.

6. test_legal_pages_include_disclaimer_about_clinical_judgment
   - Visit legal pages.
   - Assert disclaimer text about professional clinical judgment is present.

Create tests/Feature/PublicSite/ErrorPagesTest.php.

Required tests:

1. test_404_page_renders_for_missing_route
   - Visit /nonexistent-page.
   - Assert response status is 404.
   - Assert safe 404 message is visible.

2. test_404_page_does_not_expose_stack_trace
   - Visit /nonexistent-page.
   - Assert response does not contain stack trace text.

3. test_403_page_exists
   - Trigger a 403 response where practical.
   - Assert safe 403 message is visible.

If triggering 403 directly is difficult in this build, test that the error view exists and renders.

SECURITY REQUIREMENTS:

- Public pages must not expose admin routes unnecessarily.
- Public pages must not expose stack traces in production.
- Public pages must not expose environment variables.
- Error pages must not expose sensitive data.
- CSRF protection must remain active.
- Legal pages must not include secrets.

API:

No API endpoints are required for Build 6.

DATABASE:

No new tables are required.

No database queries are required for the home page in Build 6 unless displaying honest system counts is explicitly approved.

DEFINITION OF DONE:

- Public layout renders correctly.
- Public header renders navigation.
- Public footer renders legal links and admin login link.
- Home page renders hero and resource category placeholders.
- Home page does not display fake statistics.
- Legal pages render successfully.
- Legal pages include clinical judgment disclaimer.
- Error pages render safely.
- Error pages do not expose sensitive data.
- Public meta component renders SEO tags.
- Public breadcrumbs component exists.
- Public layout includes skip link.
- Public layout supports keyboard navigation.
- Public layout is responsive on mobile.
- Public pages are SEO-friendly.
- All Build 6 tests pass.
- No resource CRUD pages are created.
- No dead links exist.
- No secrets are committed.