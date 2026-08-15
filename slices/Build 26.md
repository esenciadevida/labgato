labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Auth/
│   │   │       ├── RegisterController.php
│   │   │       ├── MemberLoginController.php
│   │   │       ├── LogoutController.php
│   │   │       ├── ForgotPasswordController.php
│   │   │       ├── ResetPasswordController.php
│   │   │       └── EmailVerificationController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureUserIsAdmin.php
│   │   │   ├── EnsureUserIsMember.php
│   │   │   └── EnsureUserIsActive.php
│   │   └── Requests/
│   │       └── Auth/
│   │           ├── RegisterRequest.php
│   │           ├── MemberLoginRequest.php
│   │           ├── ForgotPasswordRequest.php
│   │           └── ResetPasswordRequest.php
│   └── Models/
│       └── User.php
├── bootstrap/
│   └── app.php
├── database/
│   ├── migrations/
│   │   └── xxxx_add_member_fields_to_users_table.php
│   └── seeders/
│       └── UserSeeder.php
├── resources/
│   └── views/
│       └── auth/
│           ├── register.blade.php
│           ├── login.blade.php
│           ├── forgot-password.blade.php
│           ├── reset-password.blade.php
│           └── verify-email.blade.php
├── routes/
│   ├── web.php
│   └── admin.php
└── tests/
    └── Feature/
        └── Auth/
            ├── RegistrationTest.php
            ├── MemberLoginTest.php
            ├── AdminLoginSeparationTest.php
            ├── PasswordResetTest.php
            └── AuthMiddlewareTest.php

IMPLEMENTATION PROMPT ID: LABGATO-PHASE06-BUILD26

CONTEXT:
LabGato environment is aligned (MySQL, AI activated). Phase 06 is approved. We are now implementing Build 26: Public User Accounts & Authentication (Slice 11).

This build adds public member accounts alongside the existing admin accounts, using a single users table with user_type separation.

OBJECTIVE:
Implement the public user account foundation: schema changes, auth middleware, registration, member login, password reset, admin login rejection, and comprehensive feature tests.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Single users table with user_type column ('admin' or 'member').
- Default web guard for both; no separate guard.
- Members do not use granular RBAC.
- Two auth entry points: /admin/login (admins) and /login (members).
- Email verification is conditional on mail configuration.
- profile_slug is auto-generated from display_name.

FILES TO INSPECT:
- app/Models/User.php
- app/Http/Controllers/Auth/ (existing admin auth controllers)
- app/Http/Middleware/ (existing middleware)
- routes/web.php
- routes/admin.php
- bootstrap/app.php
- database/seeders/UserSeeder.php
- database/migrations/ (existing users migration)

FILES TO CREATE:
- database/migrations/xxxx_add_member_fields_to_users_table.php
- app/Http/Middleware/EnsureUserIsAdmin.php
- app/Http/Middleware/EnsureUserIsMember.php
- app/Http/Middleware/EnsureUserIsActive.php
- app/Http/Controllers/Auth/RegisterController.php
- app/Http/Controllers/Auth/MemberLoginController.php
- app/Http/Controllers/Auth/LogoutController.php
- app/Http/Controllers/Auth/ForgotPasswordController.php
- app/Http/Controllers/Auth/ResetPasswordController.php
- app/Http/Controllers/Auth/EmailVerificationController.php
- app/Http/Requests/Auth/RegisterRequest.php
- app/Http/Requests/Auth/MemberLoginRequest.php
- app/Http/Requests/Auth/ForgotPasswordRequest.php
- app/Http/Requests/Auth/ResetPasswordRequest.php
- resources/views/auth/register.blade.php
- resources/views/auth/login.blade.php
- resources/views/auth/forgot-password.blade.php
- resources/views/auth/reset-password.blade.php
- resources/views/auth/verify-email.blade.php
- tests/Feature/Auth/RegistrationTest.php
- tests/Feature/Auth/MemberLoginTest.php
- tests/Feature/Auth/AdminLoginSeparationTest.php
- tests/Feature/Auth/PasswordResetTest.php
- tests/Feature/Auth/AuthMiddlewareTest.php

FILES TO MODIFY:
- app/Models/User.php
- app/Http/Controllers/Auth/AdminLoginController.php (or equivalent existing admin login)
- bootstrap/app.php
- routes/web.php
- routes/admin.php
- database/seeders/UserSeeder.php

TASK 1: SCHEMA CHANGES

Create migration: add_member_fields_to_users_table.

Add columns to users table:

user_type
- Type: string
- Default: 'member'
- After: id
- Indexed

display_name
- Type: string
- Nullable: true
- After: name

bio
- Type: text
- Nullable: true
- After: email

profile_slug
- Type: string
- Nullable: true
- Unique: true
- After: bio

is_active
- Type: boolean
- Default: true
- After: profile_slug

last_login_at
- Type: timestamp
- Nullable: true
- After: remember_token

Update app/Models/User.php:
- Add new fields to $fillable.
- Add casts: is_active to boolean, last_login_at to datetime.
- Add helper methods: isAdmin(), isMember(), isActive().
- Add a static method to generate a unique profile_slug from display_name.

TASK 2: AUTH MIDDLEWARE

Create app/Http/Middleware/EnsureUserIsAdmin.php:
- Check auth()->check() and auth()->user()->user_type === 'admin'.
- On failure: abort(403) or redirect to home with error.

Create app/Http/Middleware/EnsureUserIsMember.php:
- Check auth()->check() and user_type is 'member' OR 'admin' (admins can access member area).
- On failure: redirect to home with error.

Create app/Http/Middleware/EnsureUserIsActive.php:
- Check auth()->check() and auth()->user()->is_active === true.
- On failure: log out, invalidate session, redirect to login with message "Your account has been deactivated."

Register middleware in bootstrap/app.php:
- Alias 'admin' => EnsureUserIsAdmin
- Alias 'member' => EnsureUserIsMember
- Alias 'active.user' => EnsureUserIsActive

Update existing admin route middleware to use 'admin' alias.

TASK 3: MEMBER REGISTRATION

Create RegisterController with showRegistrationForm and register methods.

Route: GET /register, POST /register

RegisterRequest validation:
- display_name: required, string, 2-50 characters
- email: required, email, unique:users,email
- password: required, string, min:8, confirmed, must contain letter and number
- terms: required, accepted

Registration logic:
1. Validate input.
2. Generate unique profile_slug from display_name.
3. Create user with user_type='member', is_active=true.
4. If MAIL_MAILER is configured: send verification email, redirect to verification notice.
5. If MAIL_MAILER is not configured: set email_verified_at to now, redirect to /account/dashboard.
6. Log the registration.

Create resources/views/auth/register.blade.php:
- Use the auth layout.
- Display form fields: display_name, email, password, password_confirmation, terms checkbox.
- Display validation errors inline.
- Link to login page.

TASK 4: MEMBER LOGIN

Create MemberLoginController with showLoginForm and login methods.

Route: GET /login, POST /login

MemberLoginRequest validation:
- email: required, email
- password: required, string

Login logic:
1. Validate input.
2. Attempt authentication with throttle (5 attempts per minute).
3. On success: check user_type.
   - If member: redirect to /account/dashboard (or intended URL).
   - If admin: redirect to /admin/dashboard (or intended URL).
4. Update last_login_at.
5. Regenerate session.
6. On failure: return with generic error "These credentials do not match our records."

Create resources/views/auth/login.blade.php:
- Use the auth layout.
- Display form fields: email, password, remember checkbox.
- Link to register and forgot-password.
- Display validation errors inline.

TASK 5: ADMIN LOGIN REJECTION

Modify the existing admin login controller (AdminLoginController or equivalent).

Update the login logic:
1. After successful credential check, verify user_type === 'admin'.
2. If user_type is 'member', log out and redirect to /login with error "Please use the member login page."
3. Do not reveal whether the email exists.

TASK 6: PASSWORD RESET

Create ForgotPasswordController and ResetPasswordController using Laravel's built-in password reset.

Routes:
- GET /forgot-password
- POST /forgot-password
- GET /reset-password/{token}
- POST /reset-password

Rules:
- Works for both members and admins.
- Rate limit: 3 requests per minute per email.
- Generic success message: "If the email exists, a reset link has been sent."
- Password must meet strength requirements.

Create resources/views/auth/forgot-password.blade.php and reset-password.blade.php.

TASK 7: LOGOUT

Create LogoutController with logout method.

Route: POST /logout

Logic:
1. Invalidate the session.
2. Regenerate the CSRF token.
3. Log out the user.
4. Redirect to home page.

TASK 8: EMAIL VERIFICATION (CONDITIONAL)

Create EmailVerificationController with notice, verify, and resend methods.

Routes:
- GET /email/verify (notice)
- GET /email/verify/{id}/{hash} (verify)
- POST /email/verification-notification (resend)

Rules:
- Only relevant if MAIL_MAILER is configured.
- If not configured, skip verification entirely.

Create resources/views/auth/verify-email.blade.php.

TASK 9: UPDATE SEEDERS

Modify database/seeders/UserSeeder.php:
- Ensure all existing admin users have user_type='admin'.
- Ensure profile_slug is set for admins (or null if not needed).
- Ensure is_active=true for seeded admins.

TASK 10: FEATURE TESTS

Create tests/Feature/Auth/RegistrationTest.php:
- Test successful registration creates a member user.
- Test registration validates required fields.
- Test registration rejects duplicate email.
- Test registration rejects weak password.
- Test profile_slug is generated uniquely.
- Test user_type is set to 'member'.

Create tests/Feature/Auth/MemberLoginTest.php:
- Test member can log in with valid credentials.
- Test member is redirected to /account/dashboard.
- Test login fails with invalid credentials.
- Test login is rate limited.
- Test last_login_at is updated.

Create tests/Feature/Auth/AdminLoginSeparationTest.php:
- Test admin can log in at /admin/login.
- Test member is rejected at /admin/login.
- Test member can log in at /login.
- Test admin can log in at /login (redirected to admin dashboard).

Create tests/Feature/Auth/PasswordResetTest.php:
- Test password reset request sends email (or generic message).
- Test password reset with valid token works.
- Test password reset with invalid token fails.
- Test password strength is enforced.

Create tests/Feature/Auth/AuthMiddlewareTest.php:
- Test EnsureUserIsAdmin blocks members.
- Test EnsureUserIsAdmin allows admins.
- Test EnsureUserIsMember allows members and admins.
- Test EnsureUserIsActive blocks deactivated users.
- Test guest is redirected to login for protected routes.

SECURITY REQUIREMENTS:
- Passwords hashed with bcrypt.
- Rate limit login attempts (5/min) and password reset requests (3/min).
- Regenerate session on login.
- CSRF protection on all forms.
- Generic error messages (do not reveal if email exists).
- Members cannot set user_type via any form.
- Audit log all authentication events.

DEFINITION OF DONE:
- users table has user_type, display_name, bio, profile_slug, is_active, last_login_at.
- Auth middleware is registered and functional.
- Members can register, log in, and log out.
- Admin login rejects members.
- Password reset works for both members and admins.
- Email verification is conditional on mail config.
- All feature tests pass.
- No privilege escalation is possible.