labgato/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── GenerateSitemap.php
│   └── Support/
│       └── SitemapGenerator.php
├── public/
│   ├── robots.txt
│   └── sitemap.xml
├── resources/
│   └── views/
│       ├── components/
│       │   └── public/
│       │       └── meta.blade.php
│       ├── layouts/
│       │   ├── public.blade.php
│       │   ├── admin.blade.php
│       │   └── auth.blade.php
│       └── errors/
├── routes/
│   └── console.php
└── tests/
    ├── Feature/
    │   ├── Admin/
    │   ├── Api/
    │   ├── Auth/
    │   ├── Database/
    │   └── PublicSite/
    └── Unit/
        ├── Models/
        └── Services/

IMPLEMENTATION PROMPT ID: LABGATO-SLICE1-BUILD8

CONTEXT:
LabGato is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Build 1 through Build 7 are complete. The Laravel foundation, database schema, authentication, RBAC, Admin Portal foundation, Public Site foundation, API foundation, logging, and error handling are implemented.

We are now implementing Build 8: Testing, SEO & Accessibility Verification.

This is a verification build. The goal is to ensure the foundation meets all testing, SEO, accessibility, and quality standards before deployment.

OBJECTIVE:
Run the full test suite, verify SEO configuration, verify accessibility compliance, generate the sitemap, verify robots.txt, audit for fake content and dead links, and fix any issues found.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Target accessibility standard is WCAG 2.2 AA.
- Public pages must include meta tags, canonical URLs, and Open Graph tags.
- Admin and auth pages must be noindex.
- Sitemap must include only published, public, indexable URLs.
- Robots.txt must disallow /admin, /auth, and /api.
- Fake statistics and fake content are prohibited.
- Dead links and dead UI are prohibited.
- Loading, empty, error, success, and validation states are mandatory.
- Lighthouse or manual accessibility checks are required.
- Keyboard navigation must work on all critical flows.

FILES TO INSPECT:
- tests/ directory (all test files)
- public/robots.txt
- resources/views/components/public/meta.blade.php
- resources/views/layouts/public.blade.php
- resources/views/layouts/admin.blade.php
- resources/views/layouts/auth.blade.php
- resources/views/public/home.blade.php
- resources/views/legal/
- resources/views/errors/
- routes/web.php
- routes/admin.php
- routes/auth.php
- routes/api.php
- config/app.php
- config/labgato.php

FILES TO CREATE:
- app/Console/Commands/GenerateSitemap.php
- app/Support/SitemapGenerator.php
- tests/Feature/Seo/SitemapTest.php
- tests/Feature/Seo/RobotsTxtTest.php
- tests/Feature/Seo/MetaTagsTest.php

FILES TO MODIFY:
- routes/console.php
- public/robots.txt if corrections are needed
- resources/views/components/public/meta.blade.php if corrections are needed
- resources/views/layouts/public.blade.php if accessibility fixes are needed
- resources/views/layouts/admin.blade.php if accessibility fixes are needed
- resources/views/layouts/auth.blade.php if accessibility fixes are needed
- Any test files that are failing

TASK 1: FULL TEST SUITE VERIFICATION

Run the full test suite:

php artisan test

Requirements:
- All existing tests from Build 1 through Build 7 must pass.
- If any tests fail, fix the underlying issue.
- Do not delete failing tests to make the suite pass.
- Do not weaken test assertions to make tests pass.
- Document any tests that are intentionally skipped.

Expected test areas:
- Database foundation tests
- Authentication tests
- RBAC tests
- Admin layout tests
- Admin navigation tests
- Admin dashboard tests
- Public home page tests
- Legal pages tests
- Error pages tests
- API health tests
- API response format tests
- API exception handling tests
- Audit log service tests

TASK 2: SITEMAP GENERATION

Create app/Console/Commands/GenerateSitemap.php.

Command signature:

php artisan sitemap:generate

Command behavior:
- Generate an XML sitemap at public/sitemap.xml.
- Include only public, indexable URLs.
- Include the home page.
- Include legal pages if they are indexable.
- Exclude /admin routes.
- Exclude /auth routes.
- Exclude /api routes.
- Exclude error pages.
- Exclude noindex pages.

Create app/Support/SitemapGenerator.php.

SitemapGenerator should:
- Build the sitemap XML structure.
- Use the application URL from config.
- Include lastmod dates where practical.
- Include changefreq and priority where practical.
- Write the sitemap to public/sitemap.xml.

Register the command in routes/console.php or app/Console/Kernel.php depending on Laravel version.

For Build 8, the sitemap will be minimal because resource pages do not exist yet.

Expected sitemap entries for Build 8:
- /
- /legal/privacy-policy
- /legal/terms-of-service
- /legal/disclaimer
- /legal/accessibility-statement
- /legal/cookie-policy

TASK 3: ROBOTS.TXT VERIFICATION

Verify public/robots.txt.

Required content:

User-agent: *
Allow: /
Disallow: /admin
Disallow: /admin/
Disallow: /auth
Disallow: /auth/
Disallow: /api
Disallow: /api/

Sitemap: https://yourdomain.com/sitemap.xml

Replace yourdomain.com with the actual APP_URL or a placeholder that can be updated during deployment.

Rules:
- Do not block the entire site.
- Do not expose admin or auth paths.
- Include sitemap reference.

TASK 4: SEO META TAG VERIFICATION

Verify resources/views/components/public/meta.blade.php.

Required meta tags on public pages:
- title tag
- meta description
- canonical URL
- robots meta tag
- Open Graph title
- Open Graph description
- Open Graph URL
- Open Graph type
- Open Graph image if available

Verify the following pages:
- Home page
- Legal pages
- Error pages

Home page requirements:
- Unique title.
- Meta description.
- Canonical URL.
- Open Graph tags.
- Indexable.

Legal pages requirements:
- Unique title.
- Meta description.
- Canonical URL.
- Indexable or noindex based on preference.

Error pages requirements:
- Noindex.
- No sitemap inclusion.

Admin pages requirements:
- Noindex.
- No sitemap inclusion.

Auth pages requirements:
- Noindex.
- No sitemap inclusion.

TASK 5: ACCESSIBILITY VERIFICATION

Verify the following accessibility requirements across public, admin, and auth layouts.

Public layout:
- Skip to main content link exists and works.
- Semantic HTML landmarks are used.
- Visible focus states exist.
- Keyboard navigation works.
- ARIA labels are used where appropriate.
- Heading hierarchy is logical.
- Color contrast meets WCAG 2.2 AA.
- Images have alt text.
- Forms have associated labels.

Admin layout:
- Skip to main content link exists and works.
- Semantic HTML landmarks are used.
- Visible focus states exist.
- Keyboard navigation works.
- Sidebar navigation is keyboard accessible.
- Mobile sidebar supports Escape key to close.
- Focus returns to toggle button when sidebar closes.
- Active navigation uses aria-current="page".

Auth layout:
- Forms have associated labels.
- Validation errors are accessible.
- Error messages use role="alert".
- Keyboard navigation works.
- Visible focus states exist.

Manual keyboard navigation test:
- Tab through all interactive elements on home page.
- Tab through all interactive elements on admin login page.
- Tab through all interactive elements on admin dashboard.
- Verify focus is visible at each step.
- Verify no keyboard traps exist.
- Verify Escape key closes mobile menus.

TASK 6: FAKE CONTENT AND DEAD LINK AUDIT

Audit all public and admin pages for:

Fake content:
- Fake statistics.
- Fake testimonials.
- Fake resource counts.
- Fake social media links.
- Fake contact information.
- Placeholder lorem ipsum text.

Dead links:
- Links to unimplemented pages.
- Links to routes that do not exist.
- Links that return 404.
- Links that return 500.

Dead UI:
- Buttons that do nothing.
- Forms that do not submit.
- Dropdowns that do not open.
- Modals that do not open.

Rules:
- Remove or fix all fake content.
- Remove or fix all dead links.
- Remove or fix all dead UI.
- Replace placeholders with honest empty states or coming-soon messages.

TASK 7: LIGHTHOUSE PERFORMANCE BASELINE

Run Lighthouse or equivalent tool on:
- Home page
- Admin login page
- Legal page

Target scores for Build 8 baseline:
- Performance: 80 or higher.
- Accessibility: 90 or higher.
- Best Practices: 90 or higher.
- SEO: 90 or higher.

If scores are below target:
- Identify the issue.
- Fix the issue if practical.
- Document the issue if it requires a future build.

Common issues to check:
- Missing alt text.
- Missing meta description.
- Low contrast text.
- Missing labels.
- Render-blocking resources.
- Large images.
- Missing viewport meta tag.

TASK 8: SEO AND ACCESSIBILITY TESTS

Create tests/Feature/Seo/SitemapTest.php.

Required tests:

1. test_sitemap_command_generates_file
   - Run php artisan sitemap:generate.
   - Assert public/sitemap.xml exists.

2. test_sitemap_includes_home_page
   - Generate sitemap.
   - Assert sitemap contains home page URL.

3. test_sitemap_excludes_admin_routes
   - Generate sitemap.
   - Assert sitemap does not contain /admin.

4. test_sitemap_excludes_auth_routes
   - Generate sitemap.
   - Assert sitemap does not contain /auth.

5. test_sitemap_excludes_api_routes
   - Generate sitemap.
   - Assert sitemap does not contain /api.

Create tests/Feature/Seo/RobotsTxtTest.php.

Required tests:

1. test_robots_txt_is_accessible
   - GET /robots.txt.
   - Assert response is successful.

2. test_robots_txt_disallows_admin
   - GET /robots.txt.
   - Assert content contains Disallow: /admin.

3. test_robots_txt_disallows_auth
   - GET /robots.txt.
   - Assert content contains Disallow: /auth.

4. test_robots_txt_disallows_api
   - GET /robots.txt.
   - Assert content contains Disallow: /api.

Create tests/Feature/Seo/MetaTagsTest.php.

Required tests:

1. test_home_page_has_title_tag
   - Visit /.
   - Assert response contains title tag.

2. test_home_page_has_meta_description
   - Visit /.
   - Assert response contains meta description.

3. test_home_page_has_canonical_url
   - Visit /.
   - Assert response contains canonical link.

4. test_home_page_has_open_graph_tags
   - Visit /.
   - Assert response contains og:title.
   - Assert response contains og:description.
   - Assert response contains og:url.

5. test_admin_pages_are_noindex
   - Authenticate authorized user.
   - Visit admin dashboard.
   - Assert response contains noindex.

6. test_auth_pages_are_noindex
   - Visit auth login.
   - Assert response contains noindex.

SECURITY REQUIREMENTS:

- Sitemap must not expose admin or auth routes.
- Robots.txt must not expose sensitive paths.
- Meta tags must not expose sensitive data.
- Error pages must not expose stack traces.
- Lighthouse audit must not expose secrets.

ACCESSIBILITY REQUIREMENTS:

This build verifies accessibility compliance.

All accessibility issues found must be fixed or documented for future builds.

SEO REQUIREMENTS:

This build verifies SEO compliance.

All SEO issues found must be fixed or documented for future builds.

DEFINITION OF DONE:

- Full test suite passes.
- Sitemap generation command exists and works.
- Sitemap includes only public indexable pages.
- Sitemap excludes admin, auth, and API routes.
- Robots.txt is correctly configured.
- Public pages have proper meta tags.
- Admin and auth pages are noindex.
- Skip links work on public and admin layouts.
- Keyboard navigation works on critical flows.
- Focus states are visible.
- No fake content exists.
- No dead links exist.
- No dead UI exists.
- Lighthouse baseline is recorded.
- SEO tests pass.
- Accessibility issues are fixed or documented.
- No secrets are committed.