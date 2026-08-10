# 04 — Authentication and Session Security

## Depends On

This document depends on:

- The approved Phase 01 documentation (specifically User Roles and RBAC, Product Scope).
- The approved Laravel Application Structure document.
- The approved Environment and Configuration document.
- The approved Database Foundation document.
- The approved Hostinger PHP 8.4 shared hosting constraint.

## Defines

This document defines:

- The authentication scope for LabGato.
- The administrator login flow and security rules.
- The session management and protection strategy.
- The password reset flow and security rules.
- The logout and session invalidation rules.
- The re-authentication requirements for sensitive actions.
- The middleware strategy for protecting administrative routes.

## Affects

This document affects:

- The RBAC Implementation document.
- The Admin Portal Foundation document.
- The Logging, Audit, and Error Handling document.
- The Testing Foundation document.
- All future administrative controllers and middleware.
- All future authentication views and forms.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the strict security rules for accessing the Admin Portal.
- Prevents unauthorized access to administrative functions.
- Ensures sessions are protected against hijacking and fixation.
- Defines how authentication integrates with the database-driven session table.
- Explicitly excludes public user authentication from the current scope.

---

# 1. Purpose

This document establishes the Authentication and Session Security architecture for LabGato.

Because LabGato includes a powerful Admin Portal capable of managing scientific content, system settings, and user roles, authentication must be treated as a critical security boundary.

This document ensures that only authorized administrators can access the system, that their sessions are securely managed, and that authentication mechanisms respect the constraints of the Hostinger shared hosting environment.

---

# 2. Authentication Scope

## 2.1 Administrator Authentication

Approved and required.

The system must provide secure authentication for administrative users to access the Admin Portal.

## 2.2 Public User Authentication

Excluded from current scope.

LabGato does not currently include public user accounts, patient portals, or student login areas.

Therefore:

- No public registration flow will be built.
- No public password reset flow will be built.
- No public user session management will be built.
- Authentication routes and logic must be strictly isolated to the administrative domain.

If public user authentication is approved in the future, it must be introduced via a formal Change Request and designed as a separate authentication guard or module to prevent privilege escalation risks.

---

# 3. Login Flow and Security

## 3.1 Login Route

The administrative login page must be located at a standard but protected route, e.g., `/auth/login` or `/admin/login`.

The login route must be:

- Accessible without authentication.
- Protected by CSRF middleware.
- Protected by login throttling middleware.
- Excluded from search engine indexing (`noindex`).

## 3.2 Credential Validation

The login form must accept:

- Email address
- Password

Rules:

- Email must be validated for format.
- Password must not be echoed or logged.
- The system must not reveal whether the email exists or the password was incorrect (e.g., use a generic "These credentials do not match our records" message).

## 3.3 Login Throttling

To prevent brute-force attacks, login attempts must be rate-limited.

Approved default limits:

- Maximum 5 attempts per email/IP combination.
- Lockout duration of 15 minutes after exceeding the limit.

Throttling must be managed via Laravel's built-in throttle mechanisms, backed by the cache or database (depending on configured cache driver).

## 3.4 Audit Logging

Every login attempt must be evaluated for audit logging:

- Successful logins must be logged with user ID, IP address, and user agent.
- Failed logins should be logged carefully to avoid database spam during a brute-force attack, but persistent failures from a single IP should be logged or flagged.

## 3.5 Multi-Factor Authentication (MFA)

MFA is not required for the initial MVP.

However, the authentication architecture must not preclude the addition of MFA (e.g., TOTP or email codes) for Super Administrators in a future phase.

---

# 4. Session Security

Because LabGato is deployed on Hostinger shared hosting, Redis is unavailable. Sessions must be stored in the database.

## 4.1 Session Driver

Approved driver:

Database (`sessions` table).

## 4.2 Session Cookie Configuration

The session cookie must be configured securely in production.

Required cookie attributes:

- `HttpOnly`: True (prevents JavaScript access).
- `Secure`: True in production (requires HTTPS).
- `SameSite`: Lax or Strict (prevents CSRF).
- `Domain`: Restricted to the application domain.
- `Path`: Restricted to `/` or the admin prefix.

## 4.3 Session Lifetime and Timeout

- `SESSION_LIFETIME`: Configured to a reasonable administrative duration (e.g., 120 to 480 minutes).
- `SESSION_EXPIRE_ON_BROWSER_CLOSE`: False (allows "Remember Me" functionality if approved, though "Remember Me" should be used cautiously for admin accounts).

## 4.4 Session Regeneration

To prevent session fixation attacks:

- The session ID must be regenerated immediately upon successful login.
- The session ID should be regenerated periodically during long-lived administrative sessions if supported safely by the framework.

## 4.5 Concurrent Sessions

For the MVP, administrators may have concurrent sessions (e.g., logged in on a desktop and a tablet).

If stricter security is required later, the system can use the database session table to invalidate older sessions for the same user ID upon a new login.

---

# 5. Password Reset Flow

Administrators may forget their passwords. A secure reset flow is required.

## 5.1 Requesting a Reset

- The administrator enters their email address.
- The system generates a secure, hashed token.
- The token is stored in the `password_reset_tokens` table.
- An email is sent to the administrator with a time-limited link.
- The system must not reveal if the email address exists in the database (always show "If an account exists, an email has been sent").

## 5.2 Token Security

- Tokens must be cryptographically secure.
- Tokens must expire after a short duration (e.g., 60 minutes).
- Tokens must be invalidated immediately after a successful password change.
- Tokens must be hashed in the database, not stored in plain text.

## 5.3 Password Requirements

When setting or resetting a password:

- Minimum length: 12 characters (recommended for admin accounts).
- Must not be a commonly breached password (if a validation package is used).
- Must be confirmed via a secondary input field.
- Must be hashed using Laravel's default bcrypt/argon2id hasher before storage.

---

# 6. Logout and Session Invalidation

## 6.1 Logout Action

The logout action must be performed via a POST request to prevent CSRF-based logout attacks.

## 6.2 Invalidation Steps

Upon logout, the system must:

1. Invalidate the current session.
2. Delete the session record from the database `sessions` table.
3. Regenerate the CSRF token.
4. Clear the session cookie from the browser.
5. Log the logout event in the audit log.

## 6.3 Account Deactivation

If a Super Administrator deactivates another administrator's account:

- The deactivated user must be immediately logged out.
- This can be achieved via middleware that checks the `is_active` flag on the user model during every authenticated request, or by explicitly deleting their records from the `sessions` table.

---

# 7. Re-authentication for Sensitive Actions

Certain administrative actions carry high risk and should require recent authentication.

## 7.1 Sensitive Actions

Examples of sensitive actions:

- Changing another user's role.
- Modifying global system settings.
- Viewing audit logs (optional, based on RBAC).
- Deleting critical resources.

## 7.2 Re-authentication Mechanism

Laravel's `password.confirm` middleware should be used.

- When a user attempts a sensitive action, they are prompted to re-enter their password.
- Upon successful confirmation, a timestamp is stored in the session.
- Subsequent sensitive actions within a configured timeout period (e.g., 30 minutes) do not require re-authentication.

---

# 8. Authentication Middleware

## 8.1 Admin Route Protection

All routes defined in `routes/admin.php` must be wrapped in the `auth` middleware.

## 8.2 Active User Check

The authentication middleware must also verify that the authenticated user's `is_active` status is true.

If an administrator is deactivated while logged in, their next request must result in an immediate logout and redirect to the login page with an appropriate message.

## 8.3 Guest Middleware

The login and password reset routes must be wrapped in the `guest` middleware to prevent already-authenticated administrators from accessing the login page.

---

# 9. Shared Hosting Constraints

## 9.1 Database Load

Because sessions are stored in the database, every authenticated request triggers a database read/write for the session.

Mitigation:

- Ensure the `sessions` table is properly indexed (e.g., on `id` and `user_id`).
- Keep session payloads small (do not store large Eloquent models in the session; store only IDs).
- Use database connection pooling if supported by the hosting environment, or ensure queries are highly optimized.

## 9.2 No Redis Fallback

If the database becomes a bottleneck, the application cannot fall back to Redis on Hostinger shared hosting.

Instead, the application must rely on efficient session garbage collection and minimal session data storage.

---

# Document Completion Check

## Requirements Covered

- Authentication scope defined (Admins only).
- Login flow and security rules defined.
- Session management and protection defined.
- Password reset flow defined.
- Logout and session invalidation defined.
- Re-authentication rules defined.
- Middleware strategy defined.
- Shared hosting constraints addressed.

## Decisions Established

- Public user authentication is excluded from the current scope.
- Sessions will use the database driver.
- Session cookies must be HttpOnly, Secure, and SameSite in production.
- Login attempts are throttled (5 attempts / 15 minutes).
- Password reset tokens expire in 60 minutes.
- Deactivated users are immediately logged out via middleware.
- Sensitive actions require password confirmation.

## Dependencies

- Approved Phase 01 User Roles and RBAC.
- Approved Database Foundation (sessions, password_reset_tokens tables).
- Approved Environment and Configuration.

## Files Affected

This document creates:

02_Technical_Foundation_Architecture/04_Authentication_and_Session_Security.md

It directly dictates the implementation of authentication controllers, middleware, and session configuration.

## Open Questions

1. Should the "Remember Me" feature be enabled for administrators, or disabled for stricter security?
2. Should password complexity rules (e.g., mixed case, numbers, symbols) be strictly enforced via validation rules?

## TBD Decisions

- TBD: Final decision on "Remember Me" functionality for admin accounts.
- TBD: Final password complexity validation rules.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE02-DOC04

## Context

LabGato requires a secure authentication and session management system to protect the Admin Portal. This document defines the rules for admin login, session security, password resets, and route protection, specifically tailored for a database-driven session environment on shared hosting.

This prompt implements the documentation outcome of the Authentication and Session Security document.

## Objective

Create the official LabGato Authentication and Session Security document inside the approved Phase 02 documentation structure.

After this prompt is completed, the repository will have a binding security specification for the authentication system.

## Existing Architecture

Relevant approved decisions:

- LabGato uses Laravel.
- Sessions are stored in the database.
- Public user accounts are out of scope.
- Admin routes must be protected by auth and active-status middleware.
- Login throttling is mandatory.

## Files to Inspect

Inspect, if available:

- docs/
- 02_Technical_Foundation_Architecture/
- Documents 01 through 03.

## Files to Create

Create:

02_Technical_Foundation_Architecture/04_Authentication_and_Session_Security.md

Use the approved document content from this documentation output.

## Files to Modify

No existing files should be modified unless a documentation index already exists.

## Database

No database changes at this time. This document defines the security rules applied to the existing foundation tables.

## API

No API changes.

## UI

No UI changes.

## Security Requirements

For this documentation implementation:

- Do not include real passwords or tokens.
- Ensure that CSRF and session fixation protections are clearly documented.

## Accessibility Requirements

The Markdown document should use clear heading hierarchy and readable list structures.

## SEO Requirements

Authentication routes must be documented as `noindex`.

## Analytics Requirements

No analytics events required for this documentation file.

## Testing Requirements

Verify:

- The file exists at the correct path.
- The file uses the approved file name.
- The file contains the mandatory header and footer sections.
- The file explicitly excludes public user authentication.
- The file defines session cookie security attributes.

## Integration Requirements

This document must be used as the primary reference when implementing Laravel Breeze, Fortify, or custom authentication controllers and middleware.

## Definition of Done

This implementation is complete when:

- The Authentication and Session Security file exists.
- The file matches the approved Phase 02 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.