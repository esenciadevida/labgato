# 09 — Logging, Audit, and Error Handling

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically Governance and Change Control, Core Content Model).
- The approved Laravel Application Structure document.
- The approved Environment and Configuration document.
- The approved Database Foundation document.
- The approved Authentication and Session Security document.
- The approved RBAC Implementation document.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The application logging strategy and channels.
- The rules for what must and must not be logged.
- The audit logging implementation strategy.
- The global exception and error handling architecture.
- The custom error page requirements.
- The privacy and security rules for log data.
- The shared hosting constraints for log management.

## Affects

This document affects:

- The Testing Foundation document.
- The Deployment and Release Process document.
- All future services, controllers, and middleware.
- All future background jobs and scheduled tasks.
- The Admin Portal audit log viewer.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes how the application records its internal state and failures.
- Ensures that administrative and security actions are traceable.
- Prevents the accidental logging of sensitive data, secrets, or PII.
- Ensures that production errors are handled safely without exposing system internals.
- Keeps log management compatible with Hostinger shared hosting limits.

---

# 1. Purpose

This document establishes the Logging, Audit, and Error Handling architecture for LABGOTO.

Reliable logging is essential for debugging, security monitoring, and administrative accountability. Because LABGOTO handles scientific resources and administrative controls, it must maintain a clear audit trail while strictly protecting sensitive data from being written to log files.

---

# 2. Application Logging Strategy

## 2.1 Logging Channels

LABGOTO will use Laravel's built-in logging system.

Approved primary channel:

`stack` (combining `single` or `daily` depending on environment).

Recommended configuration:

- Local: `single` or `daily` (debug level).
- Production: `daily` (warning or error level).

## 2.2 Log Levels

- `debug`: Detailed debugging information (local only).
- `info`: Informational messages (e.g., scheduled tasks running).
- `notice`: Normal but significant events.
- `warning`: Exceptional occurrences that are not errors.
- `error`: Runtime errors that do not require immediate action but should be logged.
- `critical`: Critical conditions (e.g., AI provider completely down).
- `alert` / `emergency`: System is unusable.

## 2.3 What Must Be Logged

- Application boot failures.
- Database connection failures.
- Mail delivery failures.
- Queue job failures.
- AI provider timeouts or API errors.
- Scheduled task execution results.
- Unhandled exceptions.

## 2.4 What Must NOT Be Logged

- Passwords or password hashes.
- API keys or third-party secrets.
- Database credentials.
- Session tokens or cookies.
- Full credit card or payment information (if ever introduced).
- Unnecessary patient identifiable information (PII).
- Large request payloads that could fill disk space.

---

# 3. Audit Logging Strategy

Audit logging tracks deliberate administrative and security actions. It uses the `audit_logs` database table defined in the Database Foundation document.

## 3.1 Audit Service

All audit logging must be routed through a dedicated `AuditLogService`.

Controllers and Livewire components must not write directly to the `AuditLog` model. They must call the service.

## 3.2 Trigger Points

Audit logs must be created for:

- Successful logins.
- Failed login attempts (after throttling threshold or persistent failures).
- Logouts.
- User creation, updates, and deactivation.
- Role and permission assignments.
- Content creation, updates, publication, and deletion.
- System setting modifications.

## 3.3 Audit Log Payload

An audit log entry must capture:

- `user_id`: The ID of the acting administrator (null for system/unauthenticated events).
- `action`: A standardized string (e.g., `login.success`, `calculator.publish`).
- `resource_type`: The model class or entity name.
- `resource_id`: The ID of the affected record.
- `changes`: A JSON object containing only the changed non-sensitive attributes (old and new values).
- `ip_address`: The user's IP address.
- `user_agent`: The user's browser/client string.

## 3.4 Data Sanitization in Audit Logs

The `AuditLogService` must automatically strip sensitive fields before saving the `changes` JSON.

Fields to always strip:

- `password`
- `password_confirmation`
- `remember_token`
- `api_key`
- `secret`

## 3.5 Immutability

Audit logs are immutable.

- There must be no `update` or `delete` methods available for audit logs in the application code.
- The Admin Portal UI must provide a read-only view for audit logs.
- Deletion of old audit logs must only occur via a secure, documented, and authorized data retention cleanup script.

---

# 4. Error Handling Strategy

## 4.1 Global Exception Handler

Laravel's global exception handler must be customized to handle errors safely based on the environment.

## 4.2 Local Environment

- Detailed stack traces may be shown.
- Ignition or default Laravel error pages may be used.
- SQL queries and environment variables may be visible to the developer.

## 4.3 Production Environment

- Detailed stack traces must NEVER be shown to the user.
- Generic, branded error pages must be displayed.
- The actual exception must be logged to the daily log file.
- Sensitive context must be stripped from the log payload.

## 4.4 HTTP Exceptions

- `403 Forbidden`: Display a branded "Access Denied" page.
- `404 Not Found`: Display a branded "Page Not Found" page with a search bar or link home.
- `419 Page Expired`: Display a branded "Session Expired" page prompting the user to refresh and log in.
- `429 Too Many Requests`: Display a branded "Rate Limit Exceeded" page.
- `500 Server Error`: Display a branded "Something went wrong" page.
- `503 Service Unavailable`: Display a branded "Maintenance" page.

## 4.5 API Error Handling

API errors must not return HTML.

- The exception handler must detect JSON requests (`Accept: application/json`).
- It must return the standardized JSON error structure defined in the Base API Architecture document.
- It must return the correct HTTP status code.

---

# 5. Security and Privacy in Logs

## 5.1 Context Masking

If context is passed to the logger (e.g., `Log::error('Failed', $context)`), the logging pipeline must ensure sensitive keys are masked.

## 5.2 Request Logging

If HTTP requests are logged for debugging:

- Headers containing `Authorization` or `Cookie` must be redacted.
- Request bodies containing `password` fields must be redacted.

## 5.3 Log Access

- Log files reside in `storage/logs/`.
- The web server must be configured (via `.htaccess` or server config) to deny public web access to the `storage/` directory.
- Only authorized server administrators should have file-system access to production logs.

---

# 6. Shared Hosting Constraints

## 6.1 Disk Space

Shared hosting environments have strict disk quotas.

- Log files must not grow indefinitely.
- The `daily` log channel must be configured to retain a maximum number of days (e.g., 14 or 30 days).
- Large payloads (e.g., full HTML responses, massive JSON API responses) must not be logged.

## 6.2 File Permissions

- The `storage/logs/` directory must be writable by the PHP process.
- If the application cannot write to the log file, it must fail gracefully without exposing the file path to the public user.

## 6.3 Database Load for Audit Logs

Audit logs are stored in the database.

- High-traffic events (e.g., public search queries) should use a separate analytics event table or cache, not the core `audit_logs` table, to prevent bloating the database and slowing down admin queries.
- The `audit_logs` table must be indexed properly for fast retrieval in the Admin Portal.

---

# 7. Testing Requirements

## 7.1 Logging Tests

- Test that sensitive fields are stripped from audit log `changes`.
- Test that exceptions in production mode do not expose stack traces in the HTTP response.
- Test that API exceptions return valid JSON.

## 7.2 Audit Tests

- Test that creating a resource generates an audit log.
- Test that deleting a resource generates an audit log.
- Test that a deactivated user's actions are still historically preserved in the audit log (via `user_id` with `null on delete` foreign key).

---

# Document Completion Check

## Requirements Covered

- Application logging strategy defined.
- Audit logging strategy defined.
- Error handling strategy defined.
- Security and privacy rules for logs defined.
- Shared hosting constraints addressed.
- Testing requirements defined.

## Decisions Established

- Application logs use the `daily` channel in production with a retention limit.
- Audit logs are stored in the database and are strictly immutable.
- Sensitive fields are automatically stripped before audit logging.
- Production error pages are branded and hide stack traces.
- API errors return standardized JSON.
- Public search queries are not stored in the core audit log table.

## Dependencies

- Approved Database Foundation.
- Approved Environment and Configuration.
- Approved Base API Architecture.
- Approved Hostinger constraint.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/09_Logging_Audit_and_Error_Handling.md

It dictates the implementation of the Exception Handler, the AuditLogService, and the logging configuration.

## Open Questions

1. What is the exact retention period for daily application logs on the Hostinger plan?
2. Should audit logs be exported to an external service in the future?
3. How should the system handle a complete database failure when trying to write an audit log?

## TBD Decisions

- TBD: Final log retention天数 (days).
- TBD: Fallback mechanism if audit log database write fails.

---

# Implementation Prompt

## Implementation Prompt ID

LABGOTO-PHASE02-DOC09

## Context

LABGOTO requires a robust logging and error handling system to ensure security, traceability, and safe production behavior. This document defines how application logs, audit trails, and global exceptions are managed within the constraints of shared hosting.

This prompt implements the documentation outcome of the Logging, Audit, and Error Handling document.

## Objective

Create the official LABGOTO Logging, Audit, and Error Handling document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have a binding specification for error handling and audit trails.

## Existing Architecture

Relevant approved decisions:

- Audit logs are stored in the `audit_logs` database table.
- Application logs use Laravel's `daily` channel in production.
- Secrets and PII must never be logged.
- Production errors must not expose stack traces.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 through 08.
- Any existing `app/Exceptions/Handler.php` or `bootstrap/app.php` (Laravel 11).

## Files to Create

Create:

02_Technical_Foundation_Architecture/09_Logging_Audit_and_Error_Handling.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines how the existing `audit_logs` table is utilized.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include real log dumps or infrastructure paths.
- Ensure that PII masking rules are clearly documented.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

Error pages must be documented as `noindex` where appropriate.

## Analytics Requirements

No analytics events required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file clearly defines the immutability of audit logs.
- The file defines the sensitive data masking rules.

## Integration Requirements

This document must be used as the primary reference when configuring `config/logging.php`, building the `AuditLogService`, and customizing the Laravel Exception Handler.

## Definition of Done

This implementation is complete when:

- The Logging, Audit, and Error Handling file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.