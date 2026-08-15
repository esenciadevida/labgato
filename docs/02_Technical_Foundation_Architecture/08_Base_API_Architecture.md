# 08 — Base API Architecture

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Information Architecture, Core Product Areas).
- The approved Laravel Application Structure document.
- The approved Environment and Configuration document.
- The approved Database Foundation document.
- The approved Authentication and Session Security document.
- The approved RBAC Implementation document.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The API scope and strategy for LABGOTO.
- The API URL and routing conventions.
- The standard JSON response structure.
- The validation and error handling standards.
- The authentication and authorization strategy for APIs.
- The rate limiting strategy.
- The API versioning strategy.
- The API testing requirements.

## Affects

This document affects:

- The Logging, Audit, and Error Handling document.
- The Testing Foundation document.
- All future API controllers and routes.
- All future Livewire components that consume APIs.
- All future frontend JavaScript that consumes APIs.
- All future integrations with external services.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes a consistent, secure, and maintainable API foundation.
- Prevents inconsistent or ambiguous API responses.
- Ensures that APIs respect shared hosting constraints.
- Provides a clear contract for future frontend and backend development.

---

# 1. Purpose

This document establishes the Base API Architecture for LABGOTO.

While LABGOTO is primarily a server-rendered application using Blade and Livewire, a well-defined API layer is required for:

- Dynamic search functionality.
- Interactive calculators and tools that require server-side validation.
- Future integrations with external services or mobile applications.
- Structured data exchange between the frontend and backend.

This document ensures that all API endpoints are consistent, secure, and performant.

---

# 2. API Scope and Strategy

## 2.1 API Usage

The API should be used for:

- JSON data exchange.
- Dynamic search queries.
- Calculator and tool execution.
- Form submissions that require structured responses.
- Future integrations.

## 2.2 API Non-Goals

The API should not be used for:

- Serving HTML pages.
- Replacing server-side rendering for static content.
- Exposing sensitive administrative functions without strict authorization.
- Creating a public API for third-party developers unless explicitly approved.

## 2.3 API Style

LABGOTO will use a RESTful-style API where appropriate.

- Use standard HTTP methods (GET, POST, PUT, PATCH, DELETE).
- Use resource-oriented URLs.
- Use standard HTTP status codes.

---

# 3. URL and Routing Conventions

## 3.1 API Prefix

All API routes must be prefixed with `/api`.

## 3.2 Versioning

API routes should be versioned to allow future changes without breaking existing integrations.

Recommended prefix:

/api/v1

## 3.3 Route Naming

API route names must use the `api.` prefix.

Examples:

- api.v1.calculators.index
- api.v1.calculators.show
- api.v1.search
- api.v1.tools.execute

## 3.4 Resource URLs

Use plural nouns for resource collections.

Examples:

- /api/v1/calculators
- /api/v1/tools
- /api/v1/reference-ranges
- /api/v1/search

Use singular nouns or specific actions for non-resource endpoints.

Examples:

- /api/v1/tools/{id}/execute
- /api/v1/auth/login

---

# 4. JSON Response Standards

All API responses must follow a consistent structure.

## 4.1 Success Response

A successful response should include:

- `success`: Boolean indicating success.
- `data`: The requested data or result.
- `message`: Optional human-readable message.

Example:

{
  "success": true,
  "data": {
    "id": 1,
    "name": "Anion Gap Calculator",
    "slug": "anion-gap-calculator"
  },
  "message": "Calculator retrieved successfully."
}

## 4.2 List Response

A list response should include:

- `success`: Boolean indicating success.
- `data`: Array of resources.
- `meta`: Pagination metadata (if applicable).

Example:

{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Anion Gap Calculator"
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}

## 4.3 Error Response

An error response should include:

- `success`: Boolean indicating failure (false).
- `message`: Human-readable error message.
- `errors`: Object containing validation errors (if applicable).

Example:

{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}

## 4.4 HTTP Status Codes

Use standard HTTP status codes:

- 200 OK
- 201 Created
- 204 No Content
- 400 Bad Request
- 401 Unauthorized
- 403 Forbidden
- 404 Not Found
- 422 Unprocessable Entity
- 429 Too Many Requests
- 500 Internal Server Error

---

# 5. Validation and Error Handling

## 5.1 Validation

All API inputs must be validated server-side.

- Use Laravel Form Requests for validation.
- Return 422 Unprocessable Entity for validation failures.
- Include field-specific errors in the response.

## 5.2 Exception Handling

API exceptions must be handled gracefully.

- Do not expose stack traces or sensitive data in production.
- Return a generic 500 error message for unexpected exceptions.
- Log the full exception details for administrators.

## 5.3 Model Binding

If route model binding is used:

- Return 404 Not Found if the resource does not exist.
- Ensure the user is authorized to access the resource.

---

# 6. Authentication and Authorization

## 6.1 Public Endpoints

Public API endpoints do not require authentication.

Examples:

- GET /api/v1/calculators
- GET /api/v1/calculators/{slug}
- POST /api/v1/tools/{slug}/execute

Public endpoints must still be rate-limited and validated.

## 6.2 Admin Endpoints

Admin API endpoints require authentication and authorization.

Examples:

- POST /api/v1/admin/calculators
- PUT /api/v1/admin/calculators/{id}
- DELETE /api/v1/admin/calculators/{id}

Admin endpoints must use the same RBAC policies as the web interface.

## 6.3 Token Authentication

For the MVP, API authentication will use session-based authentication for admin endpoints accessed via the web interface.

If external API access is required in the future, token-based authentication (e.g., Laravel Sanctum) must be introduced via a formal Change Request.

---

# 7. Rate Limiting

## 7.1 Public Rate Limiting

Public API endpoints must be rate-limited to prevent abuse.

Recommended default:

- 60 requests per minute per IP address.

## 7.2 Admin Rate Limiting

Admin API endpoints should have stricter rate limits for sensitive actions.

Recommended default:

- 10 requests per minute per user for authentication attempts.
- 60 requests per minute per user for general admin actions.

## 7.3 Rate Limit Headers

Rate limit responses should include standard headers:

- X-RateLimit-Limit
- X-RateLimit-Remaining

## 7.4 Shared Hosting Constraints

Rate limiting must use the database or cache driver.

Redis is not available on Hostinger shared hosting.

---

# 8. Versioning Strategy

## 8.1 Versioning Method

LABGOTO will use URL versioning.

Example:

/api/v1/calculators

## 8.2 Version Lifecycle

- v1 is the initial stable version.
- Breaking changes must be introduced in a new version (e.g., v2).
- Old versions should be deprecated with clear communication.

## 8.3 Backward Compatibility

Non-breaking changes (e.g., adding new fields) may be added to the current version.

Breaking changes (e.g., removing fields, changing data types) must not be added to the current version.

---

# 9. API Testing Requirements

## 9.1 Feature Tests

Test:

- Public endpoints return expected data.
- Admin endpoints require authentication.
- Admin endpoints enforce RBAC.
- Validation errors return 422.
- Not found errors return 404.
- Rate limiting returns 429.

## 9.2 Unit Tests

Test:

- API resource transformations.
- Service logic used by API controllers.

## 9.3 Contract Tests

If external integrations are introduced, contract tests should be added to ensure API stability.

---

# Document Completion Check

## Requirements Covered

- API scope and strategy defined.
- URL and routing conventions defined.
- JSON response standards defined.
- Validation and error handling defined.
- Authentication and authorization strategy defined.
- Rate limiting strategy defined.
- Versioning strategy defined.
- API testing requirements defined.

## Decisions Established

- LABGOTO uses a RESTful-style API.
- API routes are prefixed with /api/v1.
- JSON responses follow a consistent structure.
- Public endpoints are rate-limited.
- Admin endpoints use RBAC.
- Token authentication is deferred.
- URL versioning is approved.

## Dependencies

- Approved Phase 01 Information Architecture.
- Approved Laravel Application Structure.
- Approved Environment and Configuration.
- Approved RBAC Implementation.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/08_Base_API_Architecture.md

It directly dictates the structure of all future API routes, controllers, and responses.

## Open Questions

1. Should the API support JSON:API or GraphQL in the future?
2. Should public API endpoints be cached?
3. What is the exact rate limit for search endpoints?

## TBD Decisions

- TBD: Final decision on API caching strategy.
- TBD: Final decision on search rate limits.
- TBD: Future token authentication strategy.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE02-DOC08

## Context

LABGOTO requires a consistent and secure API foundation for dynamic features, search, and future integrations. This document defines the API structure, response standards, validation, authentication, and rate limiting rules.

This prompt implements the documentation outcome of the Base API Architecture document.

## Objective

Create the official LABGOTO Base API Architecture document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have a binding API specification for future development.

## Existing Architecture

Relevant approved decisions:

- LABGOTO uses Laravel.
- API routes are prefixed with /api/v1.
- JSON responses must be consistent.
- Public endpoints must be rate-limited.
- Admin endpoints must use RBAC.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 through 07.
- Any existing API routes or controllers.

## Files to Create

Create:

02_Technical_Foundation_Architecture/08_Base_API_Architecture.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes.

## API

No API changes at this time. This document defines the API architecture.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include secrets or infrastructure details.
- Ensure that rate limiting and RBAC rules are clearly documented.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

No public SEO impact. API endpoints should generally be noindex unless they serve public content.

## Analytics Requirements

No analytics events required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the JSON response structure.
- The file defines rate limiting and authentication rules.

## Integration Requirements

This document must be used as the primary reference when creating API routes, controllers, and resources.

## Definition of Done

This implementation is complete when:

- The Base API Architecture file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.