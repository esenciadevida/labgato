labgato/
├── app/
│   ├── Contracts/
│   │   └── Searchable.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── PublicSite/
│   │   │       └── SearchController.php
│   │   └── Requests/
│   │       └── PublicSite/
│   │           └── SearchRequest.php
│   ├── Models/
│   │   └── SearchQuery.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   ├── Services/
│   │   └── SearchService.php
│   └── Support/
│       └── SearchResult.php
├── database/
│   ├── factories/
│   │   └── SearchQueryFactory.php
│   └── migrations/
│       └── xxxx_create_search_queries_table.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── public/
│       │       └── search/
│       │           ├── form.blade.php
│       │           └── result-item.blade.php
│       └── public/
│           └── search.blade.php
├── routes/
│   └── web.php
└── tests/
    ├── Feature/
    │   └── PublicSite/
    │       └── SearchPageTest.php
    └── Unit/
        └── Services/
            └── SearchServiceTest.php

IMPLEMENTATION PROMPT ID: LABGATO-PHASE03-BUILD11

CONTEXT:
LabGato is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Slice 1 Application Foundation is complete. Build 10 Taxonomy Admin Module is complete. Phase 03 Resource Architecture is approved.

We are now implementing Build 11: Search Implementation.

This build creates the shared search foundation. Calculators and Tools are not implemented yet. They will register into the search system during Build 12 and Build 13.

OBJECTIVE:
Implement the public search page, search request validation, SearchService foundation, searchable provider contract, search analytics storage, search rate limiting, and search tests.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Search must use MySQL-compatible logic.
- External search engines are excluded.
- Search must cover published resources only.
- Search results pages must be noindex.
- Search queries should be logged for analytics.
- Search must be rate-limited.
- Taxonomy categories and tags exist.
- Calculators and Tools are future searchable resource types.
- No Redis.
- No fake search results.

FILES TO INSPECT:
- routes/web.php
- app/Providers/AppServiceProvider.php
- app/Models/Category.php
- app/Models/Tag.php
- resources/views/layouts/public.blade.php
- resources/views/components/public/
- resources/views/public/home.blade.php
- config/labgato.php
- public/robots.txt
- tests/Feature/PublicSite/

FILES TO CREATE:
- app/Contracts/Searchable.php
- app/Support/SearchResult.php
- app/Services/SearchService.php
- app/Http/Controllers/PublicSite/SearchController.php
- app/Http/Requests/PublicSite/SearchRequest.php
- app/Models/SearchQuery.php
- database/migrations/xxxx_create_search_queries_table.php
- database/factories/SearchQueryFactory.php
- resources/views/public/search.blade.php
- resources/views/components/public/search/form.blade.php
- resources/views/components/public/search/result-item.blade.php
- tests/Feature/PublicSite/SearchPageTest.php
- tests/Unit/Services/SearchServiceTest.php

FILES TO MODIFY:
- routes/web.php
- app/Providers/AppServiceProvider.php
- config/labgato.php
- resources/views/components/public/header.blade.php if a search link or icon is appropriate
- resources/views/public/home.blade.php if the home page search placeholder should link to the search page

SEARCH ROUTE REQUIREMENTS:

Modify routes/web.php.

Create the public search route:

GET /search
- Name: public.search
- Controller: PublicSite\SearchController
- Middleware: throttle:search

The search route must be public and must not require authentication.

SEARCH REQUEST REQUIREMENTS:

Create app/Http/Requests/PublicSite/SearchRequest.php.

Validated query parameters:

q
- Type: string
- Nullable: true
- Max length: 255
- Trim whitespace
- Strip HTML tags if necessary

type
- Type: string
- Nullable: true
- Allowed values: all, calculator, tool
- Default: all

category
- Type: string
- Nullable: true
- Max length: 255
- Should match an active category slug if provided

tag
- Type: string
- Nullable: true
- Max length: 255
- Should match an existing tag slug if provided

page
- Type: integer
- Nullable: true
- Min: 1

Rules:
- If q is empty, do not perform a search query.
- If q is empty, display the search form and optional browse guidance.
- Do not log empty search queries.
- Sanitize q before logging.

SEARCHABLE CONTRACT REQUIREMENTS:

Create app/Contracts/Searchable.php.

This contract defines how future resource models or search providers expose searchable results.

Suggested interface:

interface Searchable
{
    public function search(string $query, array $filters = []): iterable;
}

Alternatively, SearchService may use callable providers instead of requiring models to implement the interface.

Choose one approach and keep it consistent.

SEARCH RESULT OBJECT REQUIREMENTS:

Create app/Support/SearchResult.php.

SearchResult should be a simple data object or array-compatible class.

Required fields:

- type: string, example calculator or tool
- title: string
- url: string
- description: nullable string
- categoryName: nullable string
- tags: array
- publishedAt: nullable datetime

Rules:
- SearchResult must not expose unpublished resources.
- SearchResult must not expose admin URLs.
- SearchResult must be safe for Blade rendering.

SEARCH SERVICE REQUIREMENTS:

Create app/Services/SearchService.php.

SearchService responsibilities:

1. Register searchable providers.
2. Execute search across registered providers.
3. Merge results into a single collection.
4. Apply type filter.
5. Apply category filter where supported by provider.
6. Apply tag filter where supported by provider.
7. Sort results in a stable order.
8. Paginate results.
9. Log search queries.

Provider registration:

SearchService should allow registration of providers by type.

Example:

searchService->registerProvider('calculator', function (string $query, array $filters) {
    return Calculator search results;
});

searchService->registerProvider('tool', function (string $query, array $filters) {
    return Tool search results;
});

For Build 11:
- No calculator provider is registered yet.
- No tool provider is registered yet.
- Search results should be empty unless a test registers a fake provider.

SearchService must not fail when no providers are registered.

SearchService must return an empty result set safely.

SEARCH QUERY LOGGING REQUIREMENTS:

Create database migration for search_queries table.

Required columns:

id
- primary key

query
- string
- required
- max 255

results_count
- unsigned integer
- default 0

user_id
- unsignedBigInteger
- nullable
- foreign key to users.id
- null on delete

ip_address
- string
- nullable
- max 45

user_agent
- text
- nullable

created_at
- timestamp

Rules:
- Do not store passwords or secrets.
- Do not store full user agent if privacy policy prefers omission. For Build 11, user_agent may be nullable and optional.
- IP logging should be controlled by configuration.
- Default IP logging should be disabled unless explicitly enabled.

Add to config/labgato.php:

'search' => [
    'rate_limit_per_minute' => env('SEARCH_RATE_LIMIT_PER_MINUTE', 30),
    'log_queries' => env('SEARCH_QUERY_LOGGING', true),
    'log_ip' => env('SEARCH_QUERY_LOG_IP', false),
    'max_query_length' => env('SEARCH_MAX_QUERY_LENGTH', 255),
],

Create app/Models/SearchQuery.php.

SearchQuery model requirements:
- Fillable: query, results_count, user_id, ip_address, user_agent.
- Disable updated_at if desired.
- Cast created_at to datetime.

SEARCH CONTROLLER REQUIREMENTS:

Create app/Http/Controllers/PublicSite/SearchController.php.

Controller behavior:

1. Validate SearchRequest.
2. If q is empty:
   - Render search page with empty form state.
   - Do not log query.
   - Do not query providers.
3. If q is not empty:
   - Call SearchService.
   - Get paginated results.
   - Log the query and result count if logging is enabled.
   - Render search results.

Controller must not query resource models directly.

SEARCH PAGE REQUIREMENTS:

Create resources/views/public/search.blade.php.

Search page requirements:
- Use public layout.
- Include page title: Search.
- Include meta robots noindex, nofollow.
- Include search form component.
- Display current query in input.
- Display result count if search was performed.
- Display results using result-item component.
- Display pagination if results exist.
- Display empty state if no results.
- Do not display fake results.
- Do not display fake result counts.
- Support keyboard navigation.

Create resources/views/components/public/search/form.blade.php.

Form component requirements:
- GET method to public.search.
- Search input with label.
- Accessible search button.
- Preserve current query.
- Optional type filter select.
- Optional category filter select.
- Optional tag filter select.

For Build 11:
- Type filter may include All, Calculators, Tools.
- If Calculators and Tools are not yet implemented, type filter may still exist but will return empty results until providers are registered.
- Category filter should use active categories.
- Tag filter should use existing tags.

Create resources/views/components/public/search/result-item.blade.php.

Result item requirements:
- Display title linked to resource URL.
- Display type badge.
- Display description.
- Display category if available.
- Display tags if available.
- Do not expose admin URLs.

RATE LIMITING REQUIREMENTS:

Register a named rate limiter for search.

Limiter name:

search

Recommended limit:

30 requests per minute per IP address.

Use the cache-backed rate limiter.

Do not use Redis.

If rate limit is exceeded:
- Return 429 for API-like requests if applicable.
- For the public web search page, show a safe too-many-requests error page or message.

TESTING REQUIREMENTS:

Create tests/Feature/PublicSite/SearchPageTest.php.

Required tests:

1. test_search_page_renders_successfully
   - Visit /search.
   - Assert response is successful.
   - Assert search input is visible.

2. test_search_page_is_noindex
   - Visit /search.
   - Assert response contains noindex.

3. test_empty_query_does_not_log_search_query
   - Visit /search without q.
   - Assert search_queries table is empty.

4. test_non_empty_query_logs_search_query
   - Visit /search?q=hematology.
   - Assert search_queries table contains query hematology.

5. test_search_page_displays_empty_state_when_no_results_exist
   - Visit /search?q=nonexistentterm.
   - Assert empty state message is visible.

6. test_search_query_length_is_limited
   - Submit a query longer than 255 characters.
   - Assert validation error or safe truncation behavior.

7. test_search_rate_limiter_is_applied
   - If practical, make repeated requests and assert 429 or safe throttle response.
   - If full rate-limit testing is unstable, test that the throttle middleware is applied to the route.

Create tests/Unit/Services/SearchServiceTest.php.

Required tests:

1. test_search_service_returns_empty_collection_when_no_providers_registered
   - Call SearchService with query.
   - Assert result is empty.

2. test_search_service_can_register_provider_and_return_results
   - Register a fake provider returning one SearchResult.
   - Call SearchService with query.
   - Assert result contains the fake result.

3. test_search_service_applies_type_filter
   - Register fake calculator and tool providers.
   - Search with type=calculator.
   - Assert only calculator results are returned.

4. test_search_service_does_not_expose_unpublished_results
   - Register a fake provider that returns only published results.
   - Assert service does not independently expose unpublished data.

5. test_search_service_logs_query_when_logging_enabled
   - Enable search logging.
   - Call SearchService with query.
   - Assert SearchQuery record exists.

ACCESSIBILITY REQUIREMENTS:

Search form must:
- Use a visible or programmatically associated label.
- Use appropriate input type.
- Support keyboard navigation.
- Display validation errors clearly.
- Use accessible result count text.

Search results must:
- Use semantic list markup.
- Use clear link text.
- Avoid color-only indicators.

SEO REQUIREMENTS:

Search page must include:

<meta name="robots" content="noindex, nofollow">

Do not add search pages to sitemap.

Ensure public/robots.txt remains configured appropriately.

If /search is not disallowed in robots.txt, the noindex meta tag is still required.

SECURITY REQUIREMENTS:

- Sanitize search query before rendering.
- Sanitize search query before logging.
- Do not expose stack traces.
- Do not expose unpublished resources.
- Do not expose admin URLs in search results.
- Do not store passwords or secrets in search analytics.
- Rate limit the search endpoint.

DATABASE REQUIREMENTS:

Create search_queries table.

Do not create calculators table in this build.

Do not create laboratory_tools table in this build.

Full-text indexes for calculators and laboratory_tools will be added during Build 12 and Build 13.

DEFINITION OF DONE:

- Public search route exists.
- Search page renders successfully.
- Search page is noindex.
- Search request validates query parameters.
- SearchService supports provider registration.
- SearchService returns empty results safely when no providers exist.
- Search query logging works.
- Empty queries are not logged.
- Search rate limiter is registered.
- Search form is accessible.
- Search empty state is honest.
- Feature tests pass.
- Unit tests pass.
- No calculator or tool tables are created.
- No fake search results are displayed.
- No secrets are committed.
