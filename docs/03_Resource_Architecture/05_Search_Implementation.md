# 05 — Search Implementation

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Core Product Areas, Information Architecture).
- The approved Phase 02 documentation (specifically Database Foundation, Base API Architecture, Deployment Constraints).
- The approved Phase 03 Resource Foundation document.
- The approved Phase 03 Taxonomy Implementation document.
- The approved Phase 03 Calculators Architecture document.
- The approved Phase 03 Laboratory Tools Architecture document.

## Defines

This document defines:

- The search strategy for LabGato resources.
- The MySQL-based search indexing and querying approach.
- The search query parameters and filtering rules.
- The search results page architecture.
- The search analytics strategy.
- The performance and shared hosting constraints.
- The testing requirements for search functionality.

## Affects

This document affects:

- All future resource database migrations (Full-Text indexes).
- The SearchService implementation.
- The Search Controller and routes.
- The Public Search Results page.
- The Admin Analytics dashboard (search analytics).
- The Resource Public Page Patterns document.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a performant, shared-hosting-compatible search strategy.
- Prevents the introduction of external search engines that are incompatible with Hostinger.
- Ensures users can discover resources efficiently.
- Provides the blueprint for the search results page and search analytics.

---

# 1. Purpose

This document establishes the Search Implementation architecture for LabGato.

Search is the primary discovery mechanism for laboratory professionals seeking specific calculators, tools, or reference information. Because LabGato is deployed on Hostinger shared hosting, the search strategy must be efficient, database-driven, and free of external dependencies like Elasticsearch or Meilisearch.

---

# 2. Search Strategy

## 2.1 MySQL-Based Search

LabGato will use **MySQL Full-Text Search** or optimized `LIKE` queries for the MVP.

- **Full-Text Search**: Preferred for better relevance ranking and performance on large text fields.
- **LIKE Queries**: Acceptable fallback for simple searches or if Full-Text indexes are not supported.

External search engines (e.g., Meilisearch, Elasticsearch, Algolia) are excluded from the MVP due to shared hosting constraints.

## 2.2 Search Scope

Search must cover all **published** resources.

Resource types to include in MVP search:
- Calculators
- Laboratory Tools

Future resource types (Reference Ranges, SOPs, Interpretation, Knowledge) will be added to the search scope when they are implemented.

## 2.3 Search Fields

Search should query the following fields for each resource type:
- `name`
- `short_description`
- `slug` (optional)
- `tags.name` (via relationship)
- `categories.name` (via relationship)

---

# 3. Search Indexing & Querying

## 3.1 Full-Text Indexes

Add Full-Text indexes to resource tables where practical.

Example migration for `calculators`:

$table->fullText(['name', 'short_description']);

Example migration for `laboratory_tools`:

$table->fullText(['name', 'short_description']);

## 3.2 SearchService

Create a dedicated `SearchService` to handle search queries.

Location:

app/Services/SearchService.php

Responsibilities:
- Accept a search query and optional filters.
- Query each resource type's table.
- Merge and sort results by relevance or date.
- Paginate results.
- Return a unified result set.

## 3.3 Query Strategy

For MVP, use a union or collection merge approach:

1. Query `Calculator` model with search conditions.
2. Query `LaboratoryTool` model with search conditions.
3. Merge results into a single collection.
4. Sort by relevance (if using Full-Text) or by name.
5. Paginate the merged collection.

Rules:
- Only include published resources.
- Apply taxonomy filters (category, tag) if provided.
- Limit results per resource type to prevent one type from dominating the results.

---

# 4. Search Query Parameters

The search endpoint and public search page must support the following query parameters.

## 4.1 Required Parameters

q
- Type: string
- Description: The search query.
- Example: `?q=anion gap`

## 4.2 Optional Parameters

type
- Type: string
- Description: Filter by resource type.
- Allowed values: `calculator`, `tool`, `all` (default).
- Example: `?type=calculator`

category
- Type: string
- Description: Filter by category slug.
- Example: `?category=hematology`

tag
- Type: string
- Description: Filter by tag slug.
- Example: `?tag=anemia`

page
- Type: integer
- Description: Pagination page number.
- Example: `?page=2`

## 4.3 URL Structure

Public search route:

/search

Example URLs:
- `/search?q=anion gap`
- `/search?q=dilution&type=tool`
- `/search?q=hematology&category=hematology`

---

# 5. Search Results Page

## 5.1 Page Layout

The search results page must include:
- Search input bar (pre-filled with the current query).
- Filter controls (resource type, category, tag).
- Result list.
- Pagination controls.
- Empty state if no results are found.

## 5.2 Result Item Display

Each result item must display:
- Resource name.
- Resource type badge (e.g., "Calculator", "Tool").
- Short description.
- Categories and tags (if available).
- Link to the resource detail page.

## 5.3 Grouping vs. Unified List

For MVP, use a **unified list** with resource type badges.
Grouping by resource type may be implemented in a future phase if needed.

## 5.4 Empty State

If no results are found:
- Display a clear message: "No results found for 'query'."
- Suggest alternative search terms.
- Provide a link to browse all resources.

## 5.5 SEO Considerations

- Search results pages should be `noindex` to prevent duplicate content issues.
- Use canonical tags pointing to the base search page or resource listing pages.
- Do not include search results pages in the sitemap.

---

# 6. Search Analytics

## 6.1 Search Query Logging

Log search queries to understand user intent and identify content gaps.

Storage:
- Use a dedicated `search_analytics` table or the existing `audit_logs` table with a specific action.
- For MVP, use a simple `search_queries` table.

Columns:
- `id`
- `query`: The search query string.
- `results_count`: Number of results returned.
- `user_id`: Nullable (for authenticated admins).
- `ip_address`: Nullable.
- `created_at`

## 6.2 Privacy Rules

- Do not log personally identifiable information (PII) beyond IP address if necessary.
- Do not log sensitive or medical terms that could identify a patient.
- Search analytics should be aggregated for reporting.

## 6.3 Admin Analytics

The Admin Portal should display:
- Top search queries.
- Search queries with zero results (content gaps).
- Search volume over time.

---

# 7. Performance & Shared Hosting Constraints

## 7.1 Query Optimization

- Use Full-Text indexes where possible.
- Limit the number of results per query (e.g., 20 per page).
- Use eager loading for relationships (categories, tags).
- Avoid N+1 queries.

## 7.2 Caching

- Cache popular search queries if necessary.
- Cache category and tag lists for filter dropdowns.

## 7.3 Rate Limiting

- Apply rate limiting to the search endpoint to prevent abuse.
- Recommended: 30 requests per minute per IP.

## 7.4 No External Services

- Do not use external search APIs or services.
- All search logic must run on the Hostinger server.

---

# 8. Testing Requirements

## 8.1 Feature Tests

- Test that the search page renders.
- Test that searching for a valid query returns results.
- Test that searching for an invalid query returns the empty state.
- Test that filtering by resource type works.
- Test that filtering by category works.
- Test that filtering by tag works.
- Test that pagination works.

## 8.2 Performance Tests

- Test that search queries complete within an acceptable time (e.g., < 500ms).
- Test that Full-Text indexes are used where applicable.

## 8.3 Security Tests

- Test that search does not expose unpublished resources.
- Test that search does not expose admin-only resources.
- Test that search input is sanitized to prevent SQL injection.

---

# Document Completion Check

## Requirements Covered

- Search strategy defined (MySQL-based).
- Search indexing and querying approach defined.
- Search query parameters defined.
- Search results page architecture defined.
- Search analytics strategy defined.
- Performance and shared hosting constraints defined.
- Testing requirements defined.

## Decisions Established

- LabGato uses MySQL Full-Text Search or LIKE queries for MVP.
- External search engines are excluded.
- Search covers published resources only.
- Search results pages are noindex.
- Search queries are logged for analytics.
- Rate limiting is applied to search endpoints.

## Dependencies

- Approved Phase 03 Resource Foundation.
- Approved Phase 03 Taxonomy Implementation.
- Approved Phase 02 Deployment Constraints.

## Files Affected

This document creates:

03_Resource_Architecture/05_Search_Implementation.md

It dictates the implementation of the SearchService, search routes, search results page, and search analytics.

## Open Questions

1. Should search results be grouped by resource type in a future phase?
2. Should search analytics be stored in a dedicated table or aggregated via a service?

## TBD Decisions

- TBD: Final decision on search result grouping.
- TBD: Final search analytics storage strategy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE03-DOC05

## Context

LabGato requires a performant, shared-hosting-compatible search strategy. This document defines the MySQL-based search architecture, query parameters, results page, and analytics.

This prompt implements the documentation outcome of the Search Implementation document.

## Objective

Create the official LabGato Search Implementation document inside the approved Phase 03 documentation structure.

After this prompt is completed, the repository will have a binding specification for resource discovery.

## Existing Architecture

Relevant approved decisions:

- MySQL Full-Text Search or LIKE queries are approved for MVP.
- External search engines are excluded.
- Search results pages are noindex.
- Search queries are logged for analytics.

## Files to Inspect

Inspect, if available:

- docs/
- 03_Resource_Architecture/01_Resource_Foundation.md
- 03_Resource_Architecture/02_Taxonomy_Implementation.md
- app/Services/ directory structure.
- database/migrations/ (resource tables).

## Files to Create

Create:

03_Resource_Architecture/05_Search_Implementation.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines the search architecture.

## API

No API changes.

## UI

No UI changes at this time. This document defines the UI requirements for search.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that SQL injection prevention is clearly documented.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

This document defines the SEO strategy for search results pages (noindex rules).

## Analytics Requirements

This document defines the search analytics strategy.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the MySQL-based search strategy.
- The file defines the search query parameters.

## Integration Requirements

This document must be used as the primary reference when building the SearchService, search routes, and search results page.

## Definition of Done

This implementation is complete when:

- The Search Implementation file exists.
- The file matches the approved Phase 03 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.