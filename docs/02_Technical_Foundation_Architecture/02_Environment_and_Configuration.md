# 02 — Environment and Configuration

## Depends On

This document depends on:

- The approved Laravel Application Structure document.
- The approved Technology Stack document.
- The approved Deployment Constraints document.
- The approved Database Foundation direction.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The environment strategy for LABGOTO.
- The required environment variables.
- The configuration file structure.
- The separation between static configuration and dynamic settings.
- The secret management rules.
- The environment-specific behavior rules.
- The shared hosting configuration constraints.

## Affects

This document affects:

- The Database Foundation document.
- The Authentication and Session Security document.
- The Logging, Audit, and Error Handling document.
- The Deployment and Release Process document.
- The Testing Foundation document.
- All future configuration-driven features.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes how environment variables are used and protected.
- Prevents secrets from being exposed or committed.
- Defines how configuration behaves across local, staging, and production.
- Ensures the application remains compatible with Hostinger shared hosting.
- Provides the foundation for secure deployment.

---

# 1. Purpose

This document establishes the environment and configuration architecture for LABGOTO.

A secure and maintainable application requires strict separation between code and configuration. Environment variables must be used for sensitive or environment-specific values, while configuration files must provide safe defaults and structured access.

This document ensures that LABGOTO can be developed locally, tested safely, and deployed securely to Hostinger without exposing secrets or relying on unsupported infrastructure.

---

# 2. Environment Strategy

LABGOTO must support the following environments:

## 2.1 Local Development

Purpose:

- Development and experimentation.
- Running tests.
- Building frontend assets.

Rules:

- Debug mode may be enabled.
- Error messages may be detailed.
- Local database may be used.
- AI provider keys may use test credentials or be disabled.
- Mail may use log or array driver.
- Queue may use sync or database driver.

## 2.2 Staging

Purpose:

- Pre-production verification.
- Deployment rehearsal.
- Final review before production.

Rules:

- Debug mode must be disabled.
- Error messages must be safe.
- Staging database must be separate from production.
- Staging must use production-like configuration where possible.
- Staging must not use production secrets.
- Staging must be protected from search indexing.

If Hostinger does not support a staging environment, the deployment process must include a manual pre-production verification checklist.

## 2.3 Production

Purpose:

- Live LABGOTO platform.

Rules:

- Debug mode must be disabled.
- Error messages must be user-safe.
- HTTPS must be enforced.
- Secrets must be protected.
- Queue and scheduler must respect shared hosting constraints.
- Logs must be protected.
- Search indexing must be intentional.

---

# 3. Required Environment Variables

The following environment variables must be supported.

## 3.1 Application Variables

APP_NAME
- The name of the application.
- Example: LABGOTO

APP_ENV
- The environment name.
- Allowed values: local, staging, production

APP_KEY
- Laravel encryption key.
- Must be generated securely.
- Must never be committed.

APP_DEBUG
- Whether detailed errors are shown.
- Must be false in production.

APP_URL
- The canonical application URL.
- Must use HTTPS in production.

APP_TIMEZONE
- The application timezone.
- Must be configured according to laboratory location or UTC.

APP_LOCALE
- The default locale.
- Initial value: en

APP_FALLBACK_LOCALE
- Fallback locale.
- Initial value: en

## 3.2 Database Variables

DB_CONNECTION
- Database driver.
- Initial value: mysql

DB_HOST
- Database host.
- Usually localhost on Hostinger.

DB_PORT
- Database port.
- Usually 3306.

DB_DATABASE
- Database name.

DB_USERNAME
- Database username.

DB_PASSWORD
- Database password.
- Must never be committed.

## 3.3 Session, Cache, and Queue Variables

SESSION_DRIVER
- Session storage driver.
- Approved value: database

SESSION_LIFETIME
- Session lifetime in minutes.
- Must be reasonable for admin security.

SESSION_SECURE_COOKIE
- Whether session cookie requires HTTPS.
- Must be true in production.

SESSION_SAME_SITE
- SameSite cookie setting.
- Recommended value: lax

CACHE_STORE
- Cache storage driver.
- Approved value: database

QUEUE_CONNECTION
- Queue storage driver.
- Approved value: database
- Fallback value: sync

## 3.4 Logging Variables

LOG_CHANNEL
- Logging channel.
- Recommended value: stack

LOG_LEVEL
- Minimum log level.
- Production recommendation: warning or error
- Local recommendation: debug

## 3.5 Mail Variables

MAIL_MAILER
- Mail driver.
- Approved values: smtp, log, array

MAIL_HOST
MAIL_PORT
MAIL_USERNAME
MAIL_PASSWORD
MAIL_ENCRYPTION
MAIL_FROM_ADDRESS
MAIL_FROM_NAME

Rules:

- Production must use a real mail provider.
- Development may use log or array driver.
- Mail credentials must never be committed.

## 3.6 AI Variables

AI_PROVIDER
- The active AI provider.
- Initial value: groq
- Must be disabled or null if not configured.

GROQ_API_KEY
- Groq API key.
- Must be server-side only.
- Must never be exposed to the browser.
- Must never be committed.

AI_TIMEOUT_SECONDS
- Timeout for AI provider requests.
- Recommended initial value: 15

AI_MAX_TOKENS
- Maximum token limit for AI requests.
- Must be configured according to provider limits and cost controls.

AI_RATE_LIMIT_PER_MINUTE
- Rate limit for AI endpoints.
- Must be conservative on shared hosting.

AI_ENABLED
- Global toggle for AI features.
- Must be false until AI architecture is approved.

## 3.7 Filesystem Variables

FILESYSTEM_DISK
- Default filesystem disk.
- Initial value: local

MEDIA_MAX_UPLOAD_KB
- Maximum upload size in kilobytes.
- Recommended initial value: 2048 or 5120

ALLOWED_IMAGE_EXTENSIONS
- Allowed image extensions.
- Recommended initial values: jpg, jpeg, png, webp

ALLOWED_DOCUMENT_EXTENSIONS
- Allowed document extensions where approved.
- Recommended initial values: pdf, doc, docx
- Must be reviewed for security before enabling.

## 3.8 Security Variables

LOGIN_RATE_LIMIT_MAX_ATTEMPTS
- Maximum login attempts before throttling.
- Recommended initial value: 5

LOGIN_RATE_LIMIT_DECAY_MINUTES
- Minutes to lock out after exceeding attempts.
- Recommended initial value: 15

PASSWORD_RESET_EXPIRY_MINUTES
- Password reset link expiry.
- Recommended initial value: 60

REAUTHORIZE_TIMEOUT_MINUTES
- Timeout before sensitive admin actions require re-authentication.
- Recommended initial value: 30

## 3.9 SEO and Analytics Variables

SEO_DEFAULT_TITLE
SEO_DEFAULT_DESCRIPTION
SEO_CANONICAL_DOMAIN
ANALYTICS_ENABLED
ANALYTICS_PROVIDER

Rules:

- Analytics must be privacy-conscious.
- Analytics must not collect unnecessary sensitive information.
- Analytics provider details must not be exposed in documentation.

---

# 4. Configuration File Structure

Laravel configuration files must be used as the safe interface between environment variables and application code.

## 4.1 Approved Configuration Files

The following standard Laravel configuration files must be used:

- config/app.php
- config/auth.php
- config/cache.php
- config/database.php
- config/filesystems.php
- config/logging.php
- config/mail.php
- config/queue.php
- config/session.php
- config/services.php

## 4.2 Custom Configuration File

A custom configuration file must be added for LABGOTO-specific settings:

config/LABGOTO.php

This file may contain:

- AI default settings
- Upload limits
- Allowed file extensions
- Feature flags
- Default SEO settings
- Rate limit defaults
- Module toggles

Example structure:

return [
    'ai' => [
        'enabled' => env('AI_ENABLED', false),
        'provider' => env('AI_PROVIDER', null),
        'timeout_seconds' => env('AI_TIMEOUT_SECONDS', 15),
    ],
    'uploads' => [
        'max_kb' => env('MEDIA_MAX_UPLOAD_KB', 2048),
        'allowed_image_extensions' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
    'security' => [
        'login_max_attempts' => env('LOGIN_RATE_LIMIT_MAX_ATTEMPTS', 5),
        'login_decay_minutes' => env('LOGIN_RATE_LIMIT_DECAY_MINUTES', 15),
    ],
];

## 4.3 No Direct env() Usage

Application code must not call env() directly.

Application code must use:

config('LABGOTO.ai.enabled')
config('database.default')
config('session.driver')

This ensures that configuration caching works correctly in production.

---

# 5. Static Configuration vs Dynamic Settings

LABGOTO must distinguish between static configuration and dynamic settings.

## 5.1 Static Configuration

Static configuration is managed by developers and deployment processes.

Examples:

- Database credentials
- Mail credentials
- AI provider keys
- Session driver
- Cache driver
- Queue driver
- App environment
- App URL
- Debug mode

Static configuration must live in environment variables and config files.

## 5.2 Dynamic Settings

Dynamic settings are managed by administrators through the Admin Portal.

Examples:

- Site name
- Default meta title
- Default meta description
- Logo
- Favicon
- Footer text
- AI feature toggle where approved
- Analytics toggle where approved

Dynamic settings must live in the database settings table.

## 5.3 Priority Rules

If a setting exists in both static configuration and dynamic settings:

- Security-critical settings must always come from static configuration.
- Administrator-managed content settings may override static defaults where explicitly designed.
- Secrets must never be stored in dynamic settings.
- Dynamic settings must never override database credentials, API keys, or session security settings.

---

# 6. Secret Management Rules

## 6.1 Secrets Must Not Be Committed

The following must never be committed to Git:

- .env
- .env.production
- .env.staging
- API keys
- Database passwords
- Mail passwords
- Private certificates
- Hosting credentials

## 6.2 .env.example

The repository must include a .env.example file.

The .env.example file must contain:

- All required variable names.
- Safe default values.
- Placeholder values for secrets.
- Comments explaining important variables.

The .env.example file must not contain real secrets.

## 6.3 Secret Access

Secrets must be accessible only to:

- The server-side application.
- Authorized deployment processes.
- Authorized administrators with infrastructure access.

Secrets must never be accessible to:

- Browser JavaScript
- Public API responses
- Logs
- Error pages
- Admin UI unless explicitly masked and authorized

## 6.4 Secret Rotation

If a secret is exposed:

1. Revoke or rotate the secret immediately.
2. Update the environment configuration.
3. Clear configuration cache.
4. Review logs for suspicious activity.
5. Record the incident according to approved security process.

---

# 7. Environment-Specific Behavior Rules

## 7.1 Debug Mode

Debug mode must be:

- Enabled only in local development.
- Disabled in staging.
- Disabled in production.

When debug mode is disabled, users must see safe error pages.

## 7.2 Error Reporting

Error reporting must be:

- Detailed in local logs.
- Safe in production responses.
- Free of secrets.
- Free of database credentials.
- Free of stack traces in public pages.

## 7.3 Asset Handling

Development:

- Vite development server may be used locally.

Production:

- Compiled assets must be used.
- Vite development server must not be required.
- Node.js must not be required on the server.

## 7.4 Mail Handling

Development:

- Mail may be logged or discarded safely.

Production:

- Mail must be sent through an approved provider.
- Failures must be logged.
- Users must see safe failure messages.

## 7.5 Queue Handling

Development:

- Queue may use sync or database.

Production:

- Queue must use database or sync.
- Cron-based queue processing must be used where available.
- Long-running workers must not be required.

## 7.6 Search Indexing

Development and staging:

- Search engines must be blocked from indexing.

Production:

- Search engines may index approved public pages.
- Admin and auth routes must remain blocked.

---

# 8. Shared Hosting Configuration Constraints

The following Hostinger constraints must be respected.

## 8.1 PHP Version

The application must target PHP 8.4.

The hosting environment must be verified to use PHP 8.4.

## 8.2 Database

The application must use MySQL or MariaDB.

Database configuration must use standard credentials provided by Hostinger.

## 8.3 Storage Permissions

The following directories must be writable:

- storage/
- bootstrap/cache/

Deployment documentation must include permission verification steps.

## 8.4 Configuration Caching

Production deployment should use configuration caching where possible.

Recommended production optimization commands:

php artisan config:cache
php artisan route:cache
php artisan view:cache

These commands must be used carefully and must not break dynamic route or view behavior.

## 8.5 Cron Availability

If cron is available:

- Scheduler must be triggered every minute where possible.
- Queue worker must be run with --stop-when-empty.

If cron is not available:

- Queue must fall back to sync.
- Scheduled tasks must be reviewed for manual or request-based alternatives.

---

# 9. Feature Flags

Feature flags must be used for optional or unfinished functionality.

## 9.1 Approved Initial Feature Flags

AI_ENABLED
- Controls whether AI features are available.
- Must be false until AI architecture is approved.

ANALYTICS_ENABLED
- Controls whether analytics collection is active.
- Must respect privacy rules.

SEARCH_ANALYTICS_ENABLED
- Controls whether search queries are recorded.
- Must respect privacy rules.

MAINTENANCE_MODE_ENABLED
- Optional flag for maintenance behavior.
- Must not replace Laravel's native maintenance mode.

## 9.2 Feature Flag Rules

Feature flags must:

- Be defined in configuration or database settings.
- Have safe default values.
- Be documented.
- Be testable.
- Not hide security vulnerabilities.
- Not expose unfinished UI to production users.

---

# 10. Configuration Testing Requirements

Configuration behavior must be tested.

Required tests include:

- Application boots with .env.example values.
- Required configuration values are present.
- Production debug mode is disabled.
- Session driver is database.
- Cache driver is database.
- Queue driver is database or sync.
- AI is disabled by default.
- Secrets are not exposed in config responses.
- Error pages render safely when debug is false.

---

# Document Completion Check

## Requirements Covered

- Environment strategy defined.
- Required environment variables defined.
- Configuration file structure defined.
- Static vs dynamic settings defined.
- Secret management rules defined.
- Environment-specific behavior rules defined.
- Shared hosting constraints defined.
- Feature flags defined.
- Configuration testing requirements defined.

## Decisions Established

- LABGOTO supports local, staging, and production environments.
- Secrets must never be committed or exposed.
- Static configuration uses environment variables and config files.
- Dynamic settings use the database settings table.
- Session, cache, and queue drivers are database-driven where practical.
- AI is disabled by default.
- Production must use safe error handling and compiled assets.
- Hostinger shared hosting constraints must be respected.

## Dependencies

- Approved Technology Stack.
- Approved Deployment Constraints.
- Approved Laravel Application Structure.
- Approved Database Foundation direction.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/02_Environment_and_Configuration.md

It affects future configuration files, environment templates, deployment procedures, and security documentation.

## Open Questions

1. What is the exact Hostinger database host and port?
2. Will staging be available on the current Hostinger plan?
3. Which mail provider will be used in production?
4. Should analytics be enabled in MVP?
5. Should search analytics be enabled in MVP?

## TBD Decisions

- TBD: Final mail provider.
- TBD: Final staging strategy.
- TBD: Final analytics provider.
- TBD: Final production timezone.
- TBD: Final allowed document upload extensions.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE02-DOC02

## Context

LABGOTO requires a secure environment and configuration architecture to support local development, staging verification, and Hostinger production deployment. This document defines environment variables, configuration rules, secret handling, and shared hosting constraints.

This prompt implements the documentation outcome of the Environment and Configuration document.

## Objective

Create the official LABGOTO Environment and Configuration document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository should contain the approved configuration foundation for future implementation.

## Existing Architecture

Relevant approved decisions:

- LABGOTO uses Laravel.
- LABGOTO uses Hostinger PHP 8.4 shared hosting.
- Session, cache, and queue drivers are database-driven where practical.
- AI is disabled by default.
- Secrets must never be committed or exposed.
- Production must use safe error handling.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- 01_Laravel_Application_Structure.md
- Any existing .env.example
- Any existing config files
- Any existing deployment documentation

If no files exist, record that the technical documentation structure is being initialized.

## Files to Create

Create:

02_Technical_Foundation_Architecture/02_Environment_and_Configuration.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

If a documentation index already exists, add a reference to:

02_Technical_Foundation_Architecture/02_Environment_and_Configuration.md

Do not modify application code during this documentation step.

## Database

No database changes.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include real secrets.
- Do not include database credentials.
- Do not include API keys.
- Do not include hosting credentials.
- Do not include personal data.

## Accessibility Requirements

The Markdown document should use:

- Clear heading hierarchy.
- Readable list structure.
- Consistent variable naming.

## SEO Requirements

No public SEO impact.

This is an internal/documentation file.

## Analytics Requirements

No analytics events are required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the required dependency section.
- The file contains the required completion check.
- The file contains the implementation prompt.
- The file defines environment variables safely.
- The file defines secret protection rules.
- The file respects Hostinger constraints.

If the repository supports Markdown linting, run it.

## Integration Requirements

This document must be referenced by future deployment, security, logging, and testing documents.

No future configuration decision may conflict with this document without an approved change request.

## Definition of Done

This implementation is complete when:

- The Environment and Configuration file exists.
- The file matches the approved Phase 02 file tree.
- The file defines environment strategy.
- The file defines secret management rules.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.
- No application code was changed.
- No secrets were committed.