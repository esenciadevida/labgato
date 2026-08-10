labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           └── HealthController.php
│   │   ├── Middleware/
│   │   │   └── ApiRateLimit.php
│   │   └── Resources/
│   │       └── .gitkeep
│   ├── Services/
│   │   └── AuditLogService.php
│   └── Support/
│       └── ApiResponse.php
├── bootstrap/
│   └── app.php
├── config/
│   └── logging.php
├── routes/
│   └── api.php
├── tests/
│   ├── Feature/
│   │   └── Api/
│   │       ├── ApiHealthTest.php
│   │       ├── ApiResponseFormatTest.php
│   │       └── ApiExceptionHandlingTest.php
│   └── Unit/
│       └── Services/
│           └── AuditLogServiceTest.php
└── storage/
    └── logs/
        └── .gitignore

IMPLEMENTATION PROMPT ID: LABGATO-SLICE1-BUILD7

CONTEXT:
LabGato is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Build 1 through Build 6 are complete. The Laravel foundation, database schema, authentication, RBAC, Admin Portal foundation, and Public Site foundation are implemented.

We are now implementing Build 7: Base API, Logging & Error Handling.

This build establishes the API foundation, standardized JSON responses, exception handling, logging configuration, and enhanced audit logging.

This build does NOT implement resource-specific API endpoints. Resource APIs will be built in later vertical slices.

OBJECTIVE:
Implement the API route structure, standardized JSON response helper, API rate limiting, global exception handler customization, production-safe error handling, daily log rotation, and enhanced audit logging service.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- API routes use /api/v1 prefix.
- JSON responses follow a consistent structure.
- Public API endpoints must be rate-limited.
- Admin API endpoints must use RBAC.
- Token authentication is deferred.
- URL versioning is approved.
- Application logs use daily channel in production.
- Audit logs are stored in the database and are immutable.
- Sensitive fields must be stripped before audit logging.
- Production errors must not expose stack traces.
- API errors must return standardized JSON.
- No Redis.

FILES TO INSPECT:
- routes/api.php
- bootstrap/app.php
- config/logging.php
- config/labgato.php
- app/Services/AuditLogService.php
- app/Exceptions/ directory if it exists
- app/Http/Middleware/
- tests/Feature/

FILES TO CREATE:
- app/Support/ApiResponse.php
- app/Http/Middleware/ApiRateLimit.php
- app/Http/Controllers/Api/V1/HealthController.php
- tests/Feature/Api/ApiHealthTest.php
- tests/Feature/Api/ApiResponseFormatTest.php
- tests/Feature/Api/ApiExceptionHandlingTest.php
- tests/Unit/Services/AuditLogServiceTest.php

FILES TO MODIFY:
- routes/api.php
- bootstrap/app.php
- config/logging.php
- app/Services/AuditLogService.php

API ROUTE REQUIREMENTS:

Modify routes/api.php.

API routes must be prefixed with /api/v1.

Required route for Build 7:

GET /api/v1/health
- Name: api.v1.health
- Controller: Api\V1\HealthController@index
- Purpose: Verify API is operational.
- Authentication: Not required.
- Rate limiting: Apply API rate limit.

Do not create resource-specific API endpoints yet.

API route group structure:

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::get('/health', [HealthController::class, 'index'])->name('health');
});

Ensure routes/api.php is registered in bootstrap/app.php if not already registered.

STANDARDIZED JSON RESPONSE REQUIREMENTS:

Create app/Support/ApiResponse.php.

ApiResponse should provide static helper methods for consistent JSON responses.

Required methods:

1. success(mixed $data = null, string $message = null, int $status = 200): JsonResponse
   Returns:
   {
       "success": true,
       "data": ...,
       "message": ...
   }

2. created(mixed $data = null, string $message = null): JsonResponse
   Returns 201 status.

3. noContent(): JsonResponse
   Returns 204 status.

4. error(string $message, int $status = 400, mixed $errors = null): JsonResponse
   Returns:
   {
       "success": false,
       "message": ...,
       "errors": ...
   }

5. validationError(mixed $errors, string $message = "Validation failed."): JsonResponse
   Returns 422 status.

6. notFound(string $message = "Resource not found."): JsonResponse
   Returns 404 status.

7. unauthorized(string $message = "Unauthorized."): JsonResponse
   Returns 401 status.

8. forbidden(string $message = "Forbidden."): JsonResponse
   Returns 403 status.

9. tooManyRequests(string $message = "Too many requests."): JsonResponse
   Returns 429 status.

10. serverError(string $message = "An unexpected error occurred."): JsonResponse
    Returns 500 status.

Rules:
- All API responses must use ApiResponse helpers.
- Do not return raw JSON responses outside ApiResponse in API controllers.
- Do not expose stack traces in API error responses.
- Do not expose environment variables in API error responses.

API RATE LIMITING REQUIREMENTS:

Create app/Http/Middleware/ApiRateLimit.php.

Alternatively, use Laravel's built-in throttle middleware with a named rate limiter.

Rate limiting rules:
- Public API endpoints: 60 requests per minute per IP address.
- Authentication endpoints: 10 requests per minute per IP address.
- Use database or cache-backed rate limiting.
- Do not use Redis.

Register the rate limiter in AppServiceProvider or bootstrap/app.php.

Apply rate limiting to API routes:

Route::middleware(['throttle:api-public'])->group(...)

Ensure rate limit headers are included in responses:
- X-RateLimit-Limit
- X-RateLimit-Remaining

HEALTH CHECK ENDPOINT REQUIREMENTS:

Create app/Http/Controllers/Api/V1/HealthController.php.

HealthController@index should return:

{
    "success": true,
    "data": {
        "status": "ok",
        "timestamp": "2026-08-11T00:00:00Z",
        "version": "1.0.0"
    },
    "message": "API is operational."
}

Rules:
- Health endpoint must not require authentication.
- Health endpoint must not expose sensitive system information.
- Health endpoint must not expose database credentials.
- Health endpoint may optionally check database connectivity, but must fail gracefully.

For Build 7, keep the health check simple. Do not add complex system diagnostics.

EXCEPTION HANDLING REQUIREMENTS:

Modify bootstrap/app.php to customize exception handling.

In Laravel 11, exception handling is configured using the withExceptions method.

Requirements:

1. Detect JSON requests.
   - If the request expects JSON, return ApiResponse error format.
   - Use $request->expectsJson() or Accept header check.

2. Handle validation exceptions.
   - Return 422 with ApiResponse::validationError.
   - Include field-specific errors.

3. Handle authentication exceptions.
   - Return 401 for unauthenticated API requests.
   - Redirect to auth.login for web requests.

4. Handle authorization exceptions.
   - Return 403 for unauthorized API requests.
   - Return branded 403 page for web requests.

5. Handle model not found exceptions.
   - Return 404 with ApiResponse::notFound for API requests.
   - Return branded 404 page for web requests.

6. Handle rate limit exceptions.
   - Return 429 with ApiResponse::tooManyRequests for API requests.

7. Handle generic exceptions.
   - Return 500 with ApiResponse::serverError for API requests.
   - Return branded 500 page for web requests.
   - Log the full exception.
   - Do not expose stack traces in production.

Rules:
- In production, error responses must not expose stack traces.
- In production, error responses must not expose environment variables.
- In production, error responses must not expose database errors.
- In local development, detailed errors may be shown.
- All exceptions must be logged.

LOGGING CONFIGURATION REQUIREMENTS:

Modify config/logging.php if needed.

Ensure the following:

1. Default log channel uses stack.
2. Stack channel includes daily channel.
3. Daily channel uses daily rotation.
4. Daily channel retains logs for 14 days.
5. Log level in production is warning or error.
6. Log level in local is debug.

Example daily channel configuration:

'daily' => [
    'driver' => 'daily',
    'path' => storage_path('logs/laravel.log'),
    'level' => env('LOG_LEVEL', 'debug'),
    'days' => env('LOG_RETENTION_DAYS', 14),
    'replace_placeholders' => true,
],

Rules:
- Do not log secrets.
- Do not log passwords.
- Do not log API keys.
- Do not log session tokens.
- Do not log unnecessary sensitive data.

AUDIT LOG SERVICE ENHANCEMENT REQUIREMENTS:

Modify app/Services/AuditLogService.php.

Ensure the service supports:

1. Creating audit log entries.
2. Capturing user_id when available.
3. Capturing action.
4. Capturing resource_type and resource_id.
5. Capturing changes.
6. Capturing IP address.
7. Capturing user agent.
8. Sanitizing sensitive fields.

Sensitive fields to strip from changes:
- password
- password_confirmation
- current_password
- new_password
- remember_token
- token
- api_key
- secret
- authorization
- cookie

Add the following method if not already present:

log(
    string $action,
    ?string $resourceType = null,
    ?int $resourceId = null,
    array $changes = [],
    ?int $userId = null
): void

Behavior:
- If userId is null, use the currently authenticated user ID if available.
- Sanitize changes before saving.
- Capture IP address from request.
- Capture user agent from request.
- Do not throw exceptions if audit logging fails. Log the failure silently to application logs.

Ensure AuditLogService is registered as a singleton in AppServiceProvider if not already registered.

TESTING REQUIREMENTS:

Create tests/Feature/Api/ApiHealthTest.php.

Required tests:

1. test_health_endpoint_returns_successful_response
   - GET /api/v1/health.
   - Assert response is successful.
   - Assert JSON structure contains success true.
   - Assert data.status is ok.

2. test_health_endpoint_does_not_require_authentication
   - GET /api/v1/health as guest.
   - Assert response is successful.

3. test_health_endpoint_returns_json
   - GET /api/v1/health.
   - Assert response Content-Type is application/json.

Create tests/Feature/Api/ApiResponseFormatTest.php.

Required tests:

1. test_success_response_has_correct_structure
   - Use ApiResponse::success with sample data.
   - Assert JSON has success, data, and message keys.

2. test_error_response_has_correct_structure
   - Use ApiResponse::error with sample message.
   - Assert JSON has success false and message keys.

3. test_validation_error_response_has_correct_structure
   - Use ApiResponse::validationError with sample errors.
   - Assert JSON has success false, message, and errors keys.
   - Assert status is 422.

Create tests/Feature/Api/ApiExceptionHandlingTest.php.

Required tests:

1. test_api_not_found_returns_json_404
   - GET /api/v1/nonexistent-endpoint with Accept: application/json header.
   - Assert response status is 404.
   - Assert JSON has success false.
   - Assert stack trace is not exposed.

2. test_api_validation_error_returns_json_422
   - Trigger a validation error on an API endpoint if available.
   - If no validation endpoint exists, test the exception handler directly.
   - Assert response status is 422.
   - Assert JSON has errors key.

3. test_api_unauthorized_returns_json_401
   - Access a protected API endpoint without authentication if available.
   - If no protected endpoint exists, test the exception handler directly.
   - Assert response status is 401.

4. test_api_forbidden_returns_json_403
   - Access a protected API endpoint without permission if available.
   - If no protected endpoint exists, test the exception handler directly.
   - Assert response status is 403.

Create tests/Unit/Services/AuditLogServiceTest.php.

Required tests:

1. test_audit_log_service_creates_entry
   - Call AuditLogService log method.
   - Assert audit_logs table has entry.

2. test_audit_log_service_strips_sensitive_fields
   - Call log method with changes containing password and api_key.
   - Assert stored changes do not contain password or api_key.

3. test_audit_log_service_captures_authenticated_user
   - Authenticate a user.
   - Call log method without explicit userId.
   - Assert audit log user_id matches authenticated user.

4. test_audit_log_service_handles_null_user_gracefully
   - Call log method as guest.
   - Assert audit log user_id is null.
   - Assert no exception is thrown.

SECURITY REQUIREMENTS:

- API responses must not expose stack traces in production.
- API responses must not expose environment variables.
- API responses must not expose database credentials.
- API rate limiting must use database or cache driver.
- Audit logs must not store sensitive fields.
- Application logs must not store secrets.
- Health endpoint must not expose sensitive system information.
- Exception handler must log errors without exposing them publicly.

ACCESSIBILITY REQUIREMENTS:

No UI is required for Build 7.

Accessibility requirements apply to web error pages already created in Build 6.

SEO REQUIREMENTS:

API endpoints should generally not be indexable.

Ensure robots.txt disallows /api.

DEFINITION OF DONE:

- API routes are registered under /api/v1.
- Health endpoint returns standardized JSON.
- ApiResponse helper provides consistent response methods.
- API rate limiting is configured.
- Exception handler returns JSON for API requests.
- Exception handler returns branded error pages for web requests.
- Production error responses do not expose stack traces.
- Daily log rotation is configured with 14-day retention.
- AuditLogService strips sensitive fields.
- AuditLogService captures authenticated user when available.
- All Build 7 tests pass.
- No resource-specific API endpoints are created.
- No secrets are logged.
- No secrets are committed.