# 01 — Public User Accounts and Authentication

## Depends On

This document depends on:

- The approved Phase 02 documentation (specifically Database Foundation, Authentication & Session Security, RBAC Implementation).
- The completed Slice 1 Application Foundation implementation.
- The completed Build 24 Admin System Features.
- The approved custom direction for public user accounts and community contributions.

## Defines

This document defines:

- The public user account architecture.
- The single-users-table strategy with user_type separation.
- The member registration and authentication flow.
- The auth guard and middleware separation between admin and member areas.
- The member-specific permissions model.
- The SQLite to MySQL migration procedure.
- The AI activation configuration procedure.
- The security requirements for public authentication.

## Affects

This document affects:

- The Member Layout and Dashboard document.
- The Contribution Submission and Approval Workflow document.
- The users database table schema.
- The authentication controllers and routes.
- The middleware configuration.
- The environment configuration.

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Establishes the foundation for public user accounts without disrupting the existing admin system.
- Prevents privilege escalation between members and admins.
- Ensures local development matches production (MySQL).
- Provides the activation procedure for AI features.

---

# 1. Purpose

This document establishes the Public User Accounts and Authentication architecture for LabGato.

Until now, the `users` table has served administrators only. With the introduction of community contributions, LabGato needs public user accounts for laboratory professionals who want to contribute SOPs, Knowledge articles, and Interpretation resources.

This document defines how public members are stored, authenticated, and separated from administrators, while preserving the existing RBAC system for admins.

---

# 2. Single Users Table Strategy

## 2.1 Decision

LabGato will use a **single `users` table** for both administrators and public members, distinguished by a `user_type` column.

Reasons:
- Avoids duplicate authentication systems.
- Simplifies relationships (e.g., `contributed_by` foreign keys).
- Preserves the existing admin RBAC without modification.
- Allows a user to be promoted from member to admin if needed.

## 2.2 Schema Changes

Add the following columns to the `users` table:

user_type
- Type: string
- Default: 'member'
- Allowed values: 'admin', 'member'
- Indexed

display_name
- Type: string
- Nullable: true
- Description: Public-facing name shown in contributor credits. Defaults to name if not set.

bio
- Type: text
- Nullable: true
- Description: Optional member biography for contributor profile.

is_active
- Type: boolean
- Default: true
- Description: Allows disabling a member account without deleting it.

email_verified_at
- Type: timestamp
- Nullable: true
- Description: Already exists in Laravel default; ensure it is used.

last_login_at
- Type: timestamp
- Nullable: true
- Description: Tracks last successful login.

## 2.3 User Type Rules

- New registrations are always `user_type = 'member'`.
- Admins are created only via the admin portal (Build 24) or seeder.
- A user cannot change their own `user_type`.
- Only Super Admin can promote a member to admin via the admin portal.

---

# 3. Member Registration Flow

## 3.1 Registration Page

Route: `GET /register`

Form fields:
- display_name (required, 2-50 characters)
- email (required, unique, valid email)
- password (required, minimum 8 characters, must contain letter and number)
- password_confirmation (required, must match)
- terms_agreement (required checkbox)

Rules:
- Email must be unique in the users table.
- Password must meet strength requirements.
- Terms agreement is mandatory.
- On successful registration, send email verification (if mail is configured) or auto-verify (if mail is not configured for MVP).

## 3.2 Email Verification

For the MVP, email verification behavior depends on mail configuration:

- If MAIL_MAILER is configured: require email verification before contribution.
- If MAIL_MAILER is not configured: auto-verify and allow immediate use.

Rationale: Hostinger shared hosting may not have reliable mail initially. Do not block users on unconfigured mail.

## 3.3 Post-Registration Redirect

After successful registration:
- Redirect to `/account/dashboard`.
- Display a welcome message.

---

# 4. Authentication Separation

## 4.1 Auth Entry Points

LabGato will have two distinct auth entry points:

Admin Login:
- Route: `GET /admin/login`
- Existing behavior (Build 3).
- Only users with `user_type = 'admin'` can log in here.
- If a member attempts admin login, reject with a generic error.

Member Login:
- Route: `GET /login`
- New behavior.
- Any active user (member or admin) can log in here.
- After login, redirect based on user_type:
  - member → `/account/dashboard`
  - admin → `/admin/dashboard` (or intended URL)

## 4.2 Login Form (Member)

Route: `GET /login`

Form fields:
- email
- password
- remember (checkbox)

Rules:
- Rate limit: 5 attempts per minute per email.
- Generic error message on failure ("These credentials do not match our records.").
- Do not reveal whether the email exists.

## 4.3 Password Reset

Route: `GET /forgot-password`, `POST /forgot-password`, `GET /reset-password/{token}`, `POST /reset-password`

Rules:
- Use Laravel's built-in password reset flow.
- Works for both members and admins.
- Rate limit: 3 requests per minute per email.

## 4.4 Logout

Route: `POST /logout`
- Destroy the session.
- Invalidate the remember token.
- Redirect to home page.

---

# 5. Auth Guards and Middleware

## 5.1 Guard Strategy

Use Laravel's default `web` guard for both admins and members. Do not create a separate guard.

Rationale: A single guard with `user_type` checks is simpler and avoids session/cookie complexity.

## 5.2 Middleware

Create the following middleware:

EnsureUserIsAdmin
- Location: `app/Http/Middleware/EnsureUserIsAdmin.php`
- Check: `auth()->user()->user_type === 'admin'`
- On failure: redirect to home with error, or 403.

EnsureUserIsMember
- Location: `app/Http/Middleware/EnsureUserIsMember.php`
- Check: `auth()->user()->user_type === 'member'` OR `'admin'` (admins can access member area).
- On failure: redirect to home with error.

EnsureUserIsActive
- Location: `app/Http/Middleware/EnsureUserIsActive.php`
- Check: `auth()->user()->is_active === true`
- On failure: log out and redirect to login with message.

## 5.3 Route Group Middleware

Admin routes (`/admin/*`):
- `auth`, `active`, `EnsureUserIsAdmin`, plus existing permission middleware.

Member routes (`/account/*`):
- `auth`, `EnsureUserIsActive`, `EnsureUserIsMember`.

Public routes (`/`):
- No auth required.

## 5.4 Existing Admin Middleware

The existing `active` middleware (Build 3) should be updated to check `is_active` for all user types.

---

# 6. Member Permissions Model

## 6.1 Decision

Members do **not** use the granular RBAC system (roles/permissions). That system is reserved for admins.

Rationale:
- Members do not need granular permissions.
- Simplifies the permission model.
- Prevents accidental permission assignment to members.

## 6.2 Member Capabilities

Members can:
- View all public resources (same as guests).
- Access the member area (`/account/*`).
- Submit contributions (SOP, Knowledge, Interpretation).
- View their own contribution history and status.
- Edit their own profile.
- Delete their own account (soft delete).

Members cannot:
- Access the admin area.
- Publish resources directly (all contributions require admin approval).
- Edit resources they contributed after submission (to preserve review integrity).
- Manage other users.

## 6.3 Permission Checks

For member actions, use simple `user_type` and ownership checks, not the Permission model.

Example:
- "Can this user submit a contribution?" → `user_type === 'member' && is_active === true`.
- "Can this user edit this contribution?" → `contribution.contributed_by === user.id && status === 'draft'`.

---

# 7. SQLite to MySQL Migration Procedure

## 7.1 Context

Production uses MySQL (Hostinger). Local development may have been using SQLite (Laravel default). This procedure aligns local dev with production.

## 7.2 Steps

1. Create a local MySQL database:
   - Database name: `labgato`
   - User: `root` (or a dedicated user)

2. Update `.env`:
   - DB_CONNECTION=mysql
   - DB_HOST=127.0.0.1
   - DB_PORT=3306
   - DB_DATABASE=labgato
   - DB_USERNAME=root
   - DB_PASSWORD=

3. Update `.env.example` to reflect MySQL as the default.

4. Delete any existing SQLite database file (`database/database.sqlite`) if present.

5. Run migrations fresh:
   - `php artisan migrate:fresh --seed`

6. Verify all tables use MySQL (InnoDB engine, utf8mb4 charset).

7. Run the test suite to ensure no SQLite-specific behavior.

## 7.3 Verification

- All existing migrations must run without errors on MySQL.
- Full-text indexes must be created (InnoDB supports them).
- JSON columns must work correctly.
- Foreign keys must be enforced.

---

# 8. AI Activation Procedure

## 8.1 Steps

1. Obtain a Groq API key from `console.groq.com`.

2. Update `.env`:
   - AI_ENABLED=true
   - AI_PROVIDER=groq
   - GROQ_API_KEY=gsk_your_actual_key_here
   - GROQ_MODEL=llama-3.1-70b-versatile
   - FEATURE_AI=true

3. Update `.env.example` to show the structure (with blank key).

4. Clear config cache:
   - `php artisan config:clear`

5. Verify AI works:
   - Log in as Super Admin.
   - Go to Admin → Knowledge → Create.
   - Click "Draft with AI".
   - Confirm a response is generated.

## 8.2 AI Access Rules

- AI remains admin-only.
- Members do not get AI drafting access.
- AI usage is tracked per admin user in `ai_usage`.

## 8.3 Security

- Never commit the real API key.
- Never log the API key.
- The key is stored only in `.env` (which is gitignored).

---

# 9. Security Requirements

## 9.1 Password Security

- Passwords hashed with bcrypt (Laravel default).
- Minimum 8 characters, must contain letter and number.
- Rate limit login attempts.

## 9.2 Session Security

- Secure, HTTP-only cookies.
- Session regeneration on login.
- CSRF protection on all forms.

## 9.3 Privilege Escalation Prevention

- Members cannot access `/admin/*`.
- Members cannot set `user_type` via any form.
- Members cannot grant themselves permissions.
- Admin promotion is Super Admin only.

## 9.4 Account Security

- Disable account (is_active=false) instead of hard delete.
- Log all authentication events (login, logout, failed attempts).
- Mask email addresses in logs.

---

# Document Completion Check

## Requirements Covered

- Single users table strategy defined.
- Member registration flow defined.
- Authentication separation (admin vs member) defined.
- Auth guards and middleware defined.
- Member permissions model defined.
- SQLite to MySQL migration procedure defined.
- AI activation procedure defined.
- Security requirements defined.

## Decisions Established

- Single `users` table with `user_type` column ('admin' or 'member').
- Default `web` guard for both; no separate guard.
- Members do not use granular RBAC.
- Two auth entry points: /admin/login and /login.
- Email verification is conditional on mail configuration.
- AI remains admin-only after activation.

## Dependencies

- Approved Phase 02 Authentication & Session Security.
- Approved Phase 02 RBAC Implementation.
- Completed Build 24 Admin System Features.

## Files Affected

This document creates:

06_Public_Users_and_Community_Contributions/01_Public_User_Accounts_and_Authentication.md

It dictates schema changes to the users table, new middleware, new auth routes, and environment configuration updates.

## Open Questions

1. Should members be allowed to delete their own account (soft delete) in the MVP, or defer?
2. Should email verification be mandatory before contribution, or only before publishing?

## TBD Decisions

- TBD: Final decision on member account self-deletion.
- TBD: Final decision on email verification timing.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE06-DOC01

## Context

LabGato is adding public user accounts to enable community contributions. This document defines the account architecture, authentication separation, MySQL migration, and AI activation.

This prompt implements the documentation outcome of the Public User Accounts and Authentication document.

## Objective

Create the official LabGato Public User Accounts and Authentication document inside the approved Phase 06 documentation structure.

## Files to Create

Create:

06_Public_Users_and_Community_Contributions/01_Public_User_Accounts_and_Authentication.md

Use the approved document content from this documentation output.

## Definition of Done

This implementation is complete when:

- The Public User Accounts and Authentication file exists.
- The file matches the approved Phase 06 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.