LABGOTO/
├── app/
│   ├── Console/
│   │   └── Commands/
│   │       └── GenerateSitemap.php
│   ├── Http/
│   │   └── Controllers/
│   │       └── PublicSite/
│   │           ├── CalculatorController.php
│   │           ├── ToolController.php
│   │           ├── ReferenceRangeController.php
│   │           ├── SopController.php
│   │           ├── InterpretationController.php
│   │           └── KnowledgeController.php
│   ├── Models/
│   │   ├── Calculator.php
│   │   ├── LaboratoryTool.php
│   │   ├── ReferenceRange.php
│   │   ├── SopResource.php
│   │   ├── InterpretationResource.php
│   │   └── KnowledgeResource.php
│   └── Services/
│       └── SearchService.php
├── database/
│   └── migrations/
│       └── (verify all resource table indexes)
├── public/
│   ├── robots.txt
│   └── sitemap.xml
├── resources/
│   └── views/
│       ├── components/
│       │   └── public/
│       │       ├── meta.blade.php
│       │       └── breadcrumbs.blade.php
│       └── public/
│           ├── calculators/
│           ├── tools/
│           ├── reference-ranges/
│           ├── sops/
│           ├── interpretation/
│           └── knowledge/
├── vite.config.js
├── tailwind.config.js
└── tests/
    └── Feature/
        ├── Performance/
        │   ├── QueryOptimizationTest.php
        │   └── SearchPerformanceTest.php
        └── Seo/
            ├── SitemapContentTest.php
            ├── MetaTagVerificationTest.php
            ├── StructuredDataTest.php
            └── IndexingRuleTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-HARDENING-SPRINT2

CONTEXT:
LABGOTO MVP feature development and security hardening are complete. We are now in Sprint 2 of the MVP Hardening Phase, focusing on Performance and SEO.

Because LABGOTO is deployed on Hostinger shared hosting, performance optimization is critical. The application must remain fast and responsive without requiring Redis, external CDNs, or heavy infrastructure.

SEO is equally critical because LABGOTO's public resources must be discoverable by laboratory professionals via search engines.

OBJECTIVE:
Audit and optimize database queries, asset loading, caching, and search performance. Verify and enforce all SEO requirements including sitemaps, meta tags, canonical URLs, structured data, and indexing rules.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Hostinger PHP 8.4 shared hosting constraints.
- MySQL/MariaDB database with Full-Text indexes.
- No Redis. Caching uses database driver.
- No external search engines.
- Search uses optimized LIKE or Full-Text queries.
- All public resources must have meta tags, canonical URLs, and structured data.
- Admin, auth, and API routes must be noindex and excluded from sitemap.
- Filtered listing pages must use canonical tags or noindex.

FILES TO INSPECT:
- app/Http/Controllers/PublicSite/ (all public controllers)
- app/Services/SearchService.php
- app/Models/ (all 6 resource models)
- app/Console/Commands/GenerateSitemap.php
- public/robots.txt
- public/sitemap.xml
- resources/views/components/public/meta.blade.php
- resources/views/components/public/breadcrumbs.blade.php
- resources/views/public/ (all public views)
- vite.config.js
- tailwind.config.js
- database/migrations/ (all resource table migrations)

FILES TO CREATE:
- tests/Feature/Performance/QueryOptimizationTest.php
- tests/Feature/Performance/SearchPerformanceTest.php
- tests/Feature/Seo/SitemapContentTest.php
- tests/Feature/Seo/MetaTagVerificationTest.php
- tests/Feature/Seo/StructuredDataTest.php
- tests/Feature/Seo/IndexingRuleTest.php

FILES TO MODIFY:
- app/Http/Controllers/PublicSite/ (all public controllers) - add eager loading
- app/Services/SearchService.php - optimize queries
- app/Console/Commands/GenerateSitemap.php - verify content
- public/robots.txt - verify rules
- resources/views/components/public/meta.blade.php - verify meta tags
- resources/views/public/ (all public views) - verify structured data
- vite.config.js - verify build optimization

TASK 1: DATABASE QUERY OPTIMIZATION

Review all public controllers and services for N+1 query problems.

Requirements:
1. Eager Loading:
   - All listing pages must eager load relationships (categories, tags, createdBy).
   - All detail pages must eager load relationships.
   - Use `with()` method in queries.
2. Pagination Limits:
   - All listing pages must use pagination.
   - Default pagination should be 12, 15, or 24 items per page.
   - Do not load more than 50 items per page.
3. Selective Column Loading:
   - Where practical, use `select()` to load only necessary columns for listing pages.
   - Do not load `longtext` columns (e.g., `content`, `procedure_content`) on listing pages.
4. Count Queries:
   - Avoid running separate count queries when pagination already provides totals.
   - Use `paginate()` which includes total count.

TASK 2: DATABASE INDEX VERIFICATION

Review all resource table migrations.

Requirements:
1. Verify the following indexes exist on all resource tables:
   - unique index on `slug`
   - index on `status`
   - index on `published_at`
   - fulltext index on `name` and `short_description`
2. Verify pivot tables have:
   - unique composite index on both foreign keys
   - index on each foreign key individually
3. Verify `search_queries` table has:
   - index on `created_at`
   - index on `results_count`
4. Verify `audit_logs` table has:
   - index on `user_id`
   - index on `action`
   - index on `resource_type` and `resource_id`
   - index on `created_at`

If any indexes are missing, create a new migration to add them.

TASK 3: SEARCH QUERY OPTIMIZATION

Review and optimize SearchService.

Requirements:
1. Query Efficiency:
   - Use Full-Text indexes where available.
   - Fall back to optimized LIKE queries with leading wildcard avoidance where practical.
   - Limit results per resource type to prevent one type from dominating.
2. Eager Loading:
   - Eager load categories and tags when building search results.
3. Pagination:
   - Paginate merged search results.
   - Do not load more than 20 results per page.
4. Rate Limiting:
   - Verify the search rate limiter is applied (30 requests per minute).

TASK 4: CACHING VERIFICATION

Verify all caching mechanisms are working correctly.

Requirements:
1. Permission Cache:
   - Verify permission cache is invalidated when roles or permissions change.
   - Verify permission cache uses the database cache driver.
2. Configuration Cache:
   - Document the production optimization commands:
     - `php artisan config:cache`
     - `php artisan route:cache`
     - `php artisan view:cache`
   - These should be run during deployment, not during development.
3. Search Result Caching (Optional):
   - Consider caching popular search queries for a short duration (e.g., 5 minutes).
   - This is optional for MVP and can be deferred.

TASK 5: VITE BUILD OPTIMIZATION

Review vite.config.js.

Requirements:
1. Code Splitting:
   - Verify Vite is configured for code splitting.
2. Minification:
   - Verify CSS and JS are minified in production builds.
3. Asset Hashing:
   - Verify asset filenames include content hashes for cache busting.
4. Lazy Loading:
   - Verify images use `loading="lazy"` attribute where appropriate.
   - Do not lazy load above-the-fold images (e.g., hero images).

TASK 6: SITEMAP CONTENT VERIFICATION

Review and update GenerateSitemap command.

Requirements:
1. Include all published resources:
   - Calculators
   - Laboratory Tools
   - Reference Ranges
   - SOPs
   - Interpretation Resources
   - Knowledge Resources
   - Public pages (home, legal pages)
2. Exclude:
   - Admin routes
   - Auth routes
   - API routes
   - Error pages
   - Search pages
   - Draft, archived, and obsolete resources
3. Include lastmod dates where practical.
4. Verify the sitemap is accessible at /sitemap.xml.

TASK 7: ROBOTS.TXT VERIFICATION

Review public/robots.txt.

Requirements:
1. Allow all public pages.
2. Disallow:
   - /admin
   - /admin/
   - /auth
   - /auth/
   - /api
   - /api/
   - /search (optional, but recommended to prevent crawl budget waste)
3. Include sitemap reference.

TASK 8: META TAG AND CANONICAL URL VERIFICATION

Review resources/views/components/public/meta.blade.php.

Requirements:
1. All public pages must include:
   - `<title>` tag
   - `<meta name="description">`
   - `<link rel="canonical">`
   - `<meta name="robots">`
   - Open Graph tags (og:title, og:description, og:url, og:type, og:image)
2. Canonical URLs:
   - Resource detail pages must use their own URL.
   - Filtered listing pages must use the base listing URL OR be marked noindex.
3. Noindex Rules:
   - Admin pages: noindex
   - Auth pages: noindex
   - Search pages: noindex
   - Error pages: noindex
   - Filtered listing pages: noindex OR canonical to base URL

TASK 9: STRUCTURED DATA VERIFICATION

Review all public resource views.

Requirements:
1. All resource detail pages must include JSON-LD structured data.
2. Recommended schema types:
   - Calculators: SoftwareApplication or WebApplication
   - Laboratory Tools: SoftwareApplication or WebApplication
   - Reference Ranges: MedicalWebPage or Article
   - SOPs: Article or TechArticle
   - Interpretation: MedicalWebPage or Article
   - Knowledge: Article or LearningResource
3. Breadcrumbs:
   - All resource detail pages must include BreadcrumbList JSON-LD.
4. Validation:
   - Structured data must be valid JSON.
   - Required fields must be present (name, url, description).

TASK 10: PERFORMANCE AND SEO REGRESSION TESTS

Create the following test files to lock in performance and SEO requirements.

1. tests/Feature/Performance/QueryOptimizationTest.php
   - Test that listing pages execute within acceptable query count (use DB::listen or query log).
   - Test that detail pages eager load relationships.
   - Test that pagination limits are enforced.

2. tests/Feature/Performance/SearchPerformanceTest.php
   - Test that search queries complete within acceptable time.
   - Test that search results are paginated.
   - Test that search rate limiter is applied.

3. tests/Feature/Seo/SitemapContentTest.php
   - Test that sitemap includes all published resources.
   - Test that sitemap excludes admin, auth, API routes.
   - Test that sitemap excludes draft, archived, obsolete resources.

4. tests/Feature/Seo/MetaTagVerificationTest.php
   - Test that all public resource pages include required meta tags.
   - Test that canonical URLs are correct.
   - Test that Open Graph tags are present.

5. tests/Feature/Seo/StructuredDataTest.php
   - Test that all resource detail pages include JSON-LD structured data.
   - Test that structured data is valid JSON.
   - Test that BreadcrumbList is present on detail pages.

6. tests/Feature/Seo/IndexingRuleTest.php
   - Test that admin pages are noindex.
   - Test that auth pages are noindex.
   - Test that search pages are noindex.
   - Test that filtered listing pages use canonical or noindex.

PERFORMANCE REQUIREMENTS:
- All listing pages must load within 500ms on shared hosting.
- All detail pages must load within 500ms on shared hosting.
- Search queries must complete within 500ms.
- No N+1 query problems.
- No unbounded queries.

SEO REQUIREMENTS:
- All published resources must be discoverable via sitemap.
- All public pages must have correct meta tags.
- All resource detail pages must have valid structured data.
- Admin, auth, API, and search pages must be noindex.
- Filtered listing pages must use canonical or noindex.

DEFINITION OF DONE:
- All N+1 query problems are resolved.
- All database indexes are verified.
- Search queries are optimized.
- Caching mechanisms are verified.
- Vite build is optimized.
- Sitemap includes all published resources and excludes protected routes.
- Robots.txt is correctly configured.
- All meta tags and canonical URLs are correct.
- All structured data is valid.
- All indexing rules are enforced.
- All performance and SEO tests pass.
- No existing functionality is broken.