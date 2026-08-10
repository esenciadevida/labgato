labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── DashboardController.php
│   │   │   └── Auth/
│   │   │       ├── LoginController.php
│   │   │       └── PasswordResetController.php
│   │   ├── Middleware/
│   │   │   └── EnsureUserIsActive.php
│   │   └── Requests/
│   │       └── Auth/
│   │           ├── LoginRequest.php
│   │           ├── ForgotPasswordRequest.php
│   │           └── ResetPasswordRequest.php
│   ├── Providers/
│   │   └── AppServiceProvider.php
│   └── Services/
│       └── AuditLogService.php
├── resources/
│   └── views/
│       ├── admin/
│       │   └── dashboard.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── forgot-password.blade.php
│       │   └── reset-password.blade.php
│       └── layouts/
│           └── auth.blade.php
├── routes/
│   ├── admin.php
│   └── auth.php
├── tests/
│   └── Feature/
│       ├── Admin/
│       │   └── ActiveUserAccessTest.php
│       └── Auth/
│           ├── LoginTest.php
│           ├── LogoutTest.php
│           └── PasswordResetTest.php
├── bootstrap/
│   └── app.php
└── config/
    ├── auth.php
    ├── labgato.php
    └── session.php

    IMPLEMENTATION PROMPT ID: LABGATO-SLICE1-BUILD3

CONTEXT:
LabGato is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting and MySQL/MariaDB.

Build 1 is complete. Build 2 is complete. The foundation database schema, models, factories, and seeders exist.

We are now implementing Build 3: Authentication & Session Security.

This build implements administrator authentication only. Public user accounts are out of scope.

OBJECTIVE:
Implement secure administrator authentication, session protection, login throttling, logout behavior, password reset behavior, active-user enforcement, and initial authentication audit logging.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- LabGato uses Laravel session-based authentication for administrators.
- Public user authentication is excluded.
- Sessions use the database driver.
- Session cookies must be secure in production.
- Login attempts must be throttled.
- Session ID must regenerate on successful login.
- Deactivated users must be denied access and logged out.
- Password reset tokens must expire.
- Authentication pages must not be indexable.
- Audit logs are stored in the audit_logs table.
- No Redis.
- No MFA in MVP.
- Remember Me is disabled for MVP.

FILES TO INSPECT:
- app/Models/User.php
- database/migrations/
- database/factories/UserFactory.php
- routes/web.php
- routes/admin.php
- routes/auth.php
- bootstrap/app.php
- config/auth.php
- config/session.php
- config/labgato.php
- resources/views/layouts/
- tests/Feature/

FILES TO CREATE:
- app/Http/Controllers/Auth/LoginController.php
- app/Http/Controllers/Auth/PasswordResetController.php
- app/Http/Controllers/Admin/DashboardController.php
- app/Http/Middleware/EnsureUserIsActive.php
- app/Http/Requests/Auth/LoginRequest.php
- app/Http/Requests/Auth/ForgotPasswordRequest.php
- app/Http/Requests/Auth/ResetPasswordRequest.php
- app/Services/AuditLogService.php
- resources/views/layouts/auth.blade.php
- resources/views/auth/login.blade.php
- resources/views/auth/forgot-password.blade.php
- resources/views/auth/reset-password.blade.php
- resources/views/admin/dashboard.blade.php
- tests/Feature/Auth/LoginTest.php
- tests/Feature/Auth/LogoutTest.php
- tests/Feature/Auth/PasswordResetTest.php
- tests/Feature/Admin/ActiveUserAccessTest.php

FILES TO MODIFY:
- routes/auth.php
- routes/admin.php
- bootstrap/app.php
- config/auth.php
- config/session.php
- config/labgato.php
- app/Providers/AppServiceProvider.php
- app/Models/User.php if required for password reset notification URL behavior

AUTHENTICATION SCOPE:

Implement only administrator authentication.

Do NOT implement:
- Public registration
- Public login
- Email verification for public users
- Social login
- Two-factor authentication
- API token authentication
- Patient accounts
- Student accounts
- Doctor accounts

ROUTES:

Create routes in routes/auth.php.

Authentication routes must use the guest middleware except logout and password update where appropriate.

Required routes:

GET /auth/login
- Name: auth.login
- Controller: LoginController@showLoginForm
- Middleware: guest

POST /auth/login
- Name: auth.login.attempt
- Controller: LoginController@login
- Middleware: guest

POST /auth/logout
- Name: auth.logout
- Controller: LoginController@logout
- Middleware: auth

GET /auth/forgot-password
- Name: auth.password.request
- Controller: PasswordResetController@showForgotPasswordForm
- Middleware: guest

POST /auth/forgot-password
- Name: auth.password.email
- Controller: PasswordResetController@sendResetLink
- Middleware: guest

GET /auth/reset-password/{token}
- Name: auth.password.reset
- Controller: PasswordResetController@showResetPasswordForm
- Middleware: guest

PUT /auth/reset-password
- Name: auth.password.update
- Controller: PasswordResetController@resetPassword
- Middleware: guest

Create routes in routes/admin.php.

Admin routes must be prefixed with /admin and named with admin.

Required route:

GET /admin/dashboard
- Name: admin.dashboard
- Controller: Admin\DashboardController
- Middleware: auth, active

For Build 3, the admin dashboard may be a minimal placeholder page.

Do not build the full admin dashboard UI yet. The full dashboard foundation belongs to Build 5.

SESSION SECURITY REQUIREMENTS:

- Session driver must remain database.
- Session cookie must be HttpOnly.
- Session cookie SameSite must be lax.
- Session cookie secure flag must be true in production.
- Session lifetime should be 120 minutes unless environment configuration defines otherwise.
- Session ID must be regenerated on successful login.
- Session must be invalidated on logout.
- CSRF protection must be enforced on all authentication forms.

LOGIN REQUIREMENTS:

Login form fields:
- email
- password

Rules:
- Email must be required and validated as email.
- Password must be required.
- Do not include a Remember Me checkbox.
- Use a generic authentication failure message.
- Do not reveal whether the email exists.
- Do not reveal whether the account is inactive during login.
- On successful login, regenerate the session.
- On successful login, redirect to intended admin page or admin.dashboard.
- On successful login, update users.last_login_at.
- On successful login, log an audit event with action auth.login.success.
- On failed login, log an audit event with action auth.login.failed without storing the password.
- On throttled login, return to the login form with a safe throttling error message.

LOGIN THROTTLING REQUIREMENTS:

Use Laravel cache-backed rate limiting.

Default limits:
- Maximum 5 attempts.
- Decay or lockout duration: 15 minutes.

Throttle key:
- Lowercase email address combined with IP address.

Behavior:
- Increment attempts on failed login.
- Clear attempts on successful login.
- Block further attempts until decay time expires.
- Store throttling configuration in config/labgato.php.

Add to config/labgato.php if missing:

'security' => [
    'login_max_attempts' => env('LOGIN_RATE_LIMIT_MAX_ATTEMPTS', 5),
    'login_decay_minutes' => env('LOGIN_RATE_LIMIT_DECAY_MINUTES', 15),
    'password_reset_expiry_minutes' => env('PASSWORD_RESET_EXPIRY_MINUTES', 60),
    'reauthorize_timeout_minutes' => env('REAUTHORIZE_TIMEOUT_MINUTES', 30),
],

ACTIVE USER REQUIREMENTS:

Create middleware app/Http/Middleware/EnsureUserIsActive.php.

Middleware behavior:
- If the authenticated user is active, allow the request.
- If the authenticated user is inactive:
  - Log the user out.
  - Invalidate the session.
  - Regenerate the CSRF token.
  - Redirect to auth.login.
  - Display a safe message: "Your account is no longer active."
  - Log an audit event with action auth.session.blocked_inactive.

Register the middleware alias in bootstrap/app.php:

- Alias: active
- Class: App\Http\Middleware\EnsureUserIsActive

Ensure unauthenticated users are redirected to auth.login.

PASSWORD RESET REQUIREMENTS:

Use Laravel's password reset broker.

Rules:
- Password reset tokens must expire after 60 minutes.
- Password reset links must be sent by email.
- Do not reveal whether an email address exists.
- Always display the same success message after requesting a reset link.
- Password reset form must include:
  - token hidden field
  - email hidden or read-only field
  - password
  - password_confirmation
- New password minimum length must be 12 characters.
- New password must contain letters and numbers.
- Password reset tokens must be invalidated after successful use.
- After successful password reset, redirect to auth.login with a success message.
- Do not automatically log the user in after password reset.
- Log an audit event with action auth.password.reset_success after successful reset.

Configure config/auth.php passwords users expiry to 60 minutes if not already configured.

Use AppServiceProvider to define the reset password URL using route auth.password.reset.

The reset URL must include:
- token
- email

AUDIT LOGGING REQUIREMENTS:

Create app/Services/AuditLogService.php.

Initial service responsibilities:
- Create audit log records.
- Capture user_id when available.
- Capture action.
- Capture resource_type and resource_id where relevant.
- Capture changes where relevant.
- Capture IP address.
- Capture user agent.
- Sanitize sensitive fields before storing changes.

Sensitive fields to strip:
- password
- password_confirmation
- current_password
- remember_token
- token
- api_key
- secret

Authentication audit actions:
- auth.login.success
- auth.login.failed
- auth.logout
- auth.session.blocked_inactive
- auth.password.reset_success

For auth.login.failed:
- Do not store the submitted password.
- Do not store the submitted email in the changes column.
- user_id may be null.

AUTH VIEWS:

Create resources/views/layouts/auth.blade.php.

Auth layout requirements:
- Minimal centered card layout.
- Uses Tailwind CSS.
- Includes CSRF token in forms.
- Includes meta robots noindex, nofollow.
- Includes accessible labels.
- Includes visible focus states.
- Includes error message area with role="alert".
- Includes success message area where appropriate.
- Does not include admin sidebar or public navigation.

Create resources/views/auth/login.blade.php.

Login form requirements:
- Email input with label.
- Password input with label.
- autocomplete="email" for email.
- autocomplete="current-password" for password.
- Submit button.
- Link to forgot password.
- Validation errors displayed near fields.
- Generic authentication error displayed safely.
- Throttling error displayed safely.

Create resources/views/auth/forgot-password.blade.php.

Forgot password requirements:
- Short explanatory text.
- Email input with label.
- Submit button.
- Link back to login.
- Status message displayed safely.

Create resources/views/auth/reset-password.blade.php.

Reset password requirements:
- Hidden token field.
- Email field hidden or read-only.
- Password input with label.
- Password confirmation input with label.
- autocomplete="new-password" for password fields.
- Submit button.
- Validation errors displayed near fields.

ADMIN DASHBOARD PLACEHOLDER:

Create app/Http/Controllers/Admin/DashboardController.php.

For Build 3, this controller may return a minimal Blade view.

Create resources/views/admin/dashboard.blade.php.

Placeholder requirements:
- Display page title: Admin Dashboard.
- Display a simple message that the dashboard foundation will be completed in Build 5.
- Do not display fake statistics.
- Do not display fake activity.
- Do not build the full admin layout yet.
- The placeholder may use a simple layout for now.

TESTING REQUIREMENTS:

Use RefreshDatabase for all feature tests.

Create tests/Feature/Auth/LoginTest.php.

Required tests:

1. test_login_page_renders_for_guests
- Visit /auth/login.
- Assert response is successful.
- Assert email and password fields are visible.

2. test_user_can_login_with_valid_credentials
- Create an active user with known password.
- POST /auth/login with valid credentials.
- Assert user is authenticated.
- Assert redirect to admin.dashboard.
- Assert last_login_at is updated.

3. test_user_cannot_login_with_invalid_credentials
- Create a user.
- POST /auth/login with incorrect password.
- Assert user is not authenticated.
- Assert session has error.
- Assert error message does not reveal whether email exists.

4. test_inactive_user_cannot_login
- Create an inactive user.
- POST /auth/login with valid credentials.
- Assert user is not authenticated.
- Assert session has error.

5. test_login_attempts_are_throttled
- Create a user.
- Attempt invalid login 5 times.
- Attempt a sixth invalid login.
- Assert session contains throttling error.
- Assert user is not authenticated.

Create tests/Feature/Auth/LogoutTest.php.

Required tests:

1. test_authenticated_user_can_logout
- Authenticate a user.
- POST /auth/logout.
- Assert user is not authenticated.
- Assert redirect to auth.login.

2. test_guest_cannot_logout
- POST /auth/logout as guest.
- Assert redirected to auth.login.

Create tests/Feature/Auth/PasswordResetTest.php.

Required tests:

1. test_forgot_password_page_renders_for_guests
- Visit /auth/forgot-password.
- Assert response is successful.

2. test_reset_link_status_message_is_displayed_without_revealing_email_existence
- POST /auth/forgot-password with nonexistent email.
- Assert session has success status message.
- Assert message does not reveal that the email was not found.

3. test_password_can_be_reset_with_valid_token
- Create a user.
- Generate a valid password reset token.
- Submit reset password form with valid token, email, new password, and confirmation.
- Assert password was changed.
- Assert user is not automatically authenticated.
- Assert redirect to auth.login.

4. test_password_reset_requires_minimum_length
- Create a user.
- Generate a valid password reset token.
- Submit reset password form with a password shorter than 12 characters.
- Assert session has validation error.

5. test_password_reset_token_cannot_be_reused
- Create a user.
- Generate a valid password reset token.
- Use the token successfully.
- Attempt to use the same token again.
- Assert second attempt fails.

Create tests/Feature/Admin/ActiveUserAccessTest.php.

Required tests:

1. test_active_user_can_access_admin_dashboard
- Create an active user.
- Authenticate user.
- Visit /admin/dashboard.
- Assert response is successful.

2. test_inactive_user_is_logged_out_when_accessing_admin_dashboard
- Create an inactive user.
- Authenticate user directly using actingAs.
- Visit /admin/dashboard.
- Assert redirected to auth.login.
- Assert session has account inactive message.
- Assert user is no longer authenticated.

SECURITY REQUIREMENTS:

- Passwords must be hashed.
- Do not log passwords.
- Do not expose whether an email exists.
- Do not expose whether an account is inactive during login.
- Authentication pages must include noindex.
- CSRF protection must be enforced.
- Session must regenerate on login.
- Session must invalidate on logout.
- Rate limiting must use database cache or another approved shared-hosting-compatible cache store.
- Do not implement Remember Me.

ACCESSIBILITY REQUIREMENTS:

Authentication forms must:
- Use visible labels.
- Associate labels with inputs using for and id.
- Display validation errors clearly.
- Use role="alert" for error messages where appropriate.
- Support keyboard navigation.
- Have visible focus states.
- Use autocomplete attributes.

SEO REQUIREMENTS:

Authentication pages must include:

<meta name="robots" content="noindex, nofollow">

The admin dashboard placeholder must not be indexable.

Do not add authentication pages to sitemap.

API:

No API endpoints are required for Build 3.

DATABASE:

No new tables are required.

Modify users.last_login_at when login succeeds.

Use the existing password_reset_tokens table.

Use the existing audit_logs table.

DEFINITION OF DONE:

- Administrator can log in with valid credentials.
- Administrator cannot log in with invalid credentials.
- Inactive user cannot log in.
- Login attempts are throttled after configured attempts.
- Session regenerates on login.
- Administrator can log out.
- Session invalidates on logout.
- Active-user middleware blocks deactivated authenticated users.
- Password reset request does not reveal whether email exists.
- Password reset works with a valid token.
- Password reset enforces minimum password length.
- Password reset token cannot be reused.
- Authentication pages are noindex.
- Authentication forms are accessible.
- Audit logs are created for authentication events.
- Sensitive fields are not logged.
- All Build 3 tests pass.
- No public user authentication is implemented.
- No role management UI is implemented.
- No full admin dashboard is implemented.
- No secrets are committed.