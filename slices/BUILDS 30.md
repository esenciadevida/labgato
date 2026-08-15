labgato/
├── composer.json
├── config/
│   └── services.php
├── .env.example
├── database/
│   └── migrations/
│       └── xxxx_add_google_id_to_users_table.php
├── app/
│   ├── Models/
│   │   └── User.php
│   └── Http/
│       └── Controllers/
│           └── Auth/
│               └── SocialAuthController.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── auth/
│       │       └── google-button.blade.php
│       └── auth/
│           ├── login.blade.php
│           └── register.blade.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── Auth/
            └── GoogleAuthIMPLEMENTATION PROMPT ID: LABGATO-BUILD30-GOOGLE-OAUTH

CONTEXT:
LabGato has member registration and login (Build 26). We are adding Google OAuth via Laravel Socialite. The system currently runs locally at http://localhost:8000 (no production domain yet).

OBJECTIVE:
Add "Continue with Google" to login and registration. On callback, find or create a member account by email and log the user in. Must work locally using a localhost redirect URI.

EXISTING ARCHITECTURE:
- Single users table with user_type ('admin' or 'member').
- Member registration creates user_type='member'.
- profile_slug auto-generated from display_name.
- Local dev runs via php artisan serve at http://localhost:8000.

FILES TO INSPECT:
- app/Models/User.php
- app/Http/Controllers/Auth/MemberLoginController.php
- app/Http/Controllers/Auth/RegisterController.php
- resources/views/auth/login.blade.php
- resources/views/auth/register.blade.php
- config/services.php
- routes/web.php

FILES TO CREATE:
- database/migrations/xxxx_add_google_id_to_users_table.php
- app/Http/Controllers/Auth/SocialAuthController.php
- resources/views/components/auth/google-button.blade.php
- tests/Feature/Auth/GoogleAuthTest.php

FILES TO MODIFY:
- composer.json
- config/services.php
- .env.example
- app/Models/User.php
- resources/views/auth/login.blade.php
- resources/views/auth/register.blade.php
- routes/web.php

TASK 1: INSTALL SOCIALITE
Run: composer require laravel/socialite

TASK 2: SCHEMA CHANGE
Create migration: add_google_id_to_users_table.
- google_id: string, nullable, after profile_slug, indexed.
Update User model: add google_id to $fillable, add hasGoogleAccount() helper.

TASK 3: CONFIGURATION
Modify config/services.php:
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'),
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    'redirect' => env('GOOGLE_REDIRECT_URI'),
],

Update .env.example:
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

LOCAL NOTE: For local development, the redirect URI must be a localhost URL.
Google Cloud Console allows http://localhost:8000/auth/google/callback as an
authorized redirect URI. When a production domain is added later, add the
domain's callback URL and update .env.

TASK 4: SOCIAL AUTH CONTROLLER
Create SocialAuthController with redirectToGoogle and handleGoogleCallback.

handleGoogleCallback logic:
1. Get Google user via Socialite. Handle exceptions gracefully (redirect to /login with error).
2. Extract email, name, google_id.
3. Look up existing user by email.
   - If found AND user_type='member': update google_id if not set, log in, redirect to /account/dashboard.
   - If found AND user_type='admin': REJECT (prevent admin account takeover). Redirect to /login with error.
   - If not found: create a new member account.
4. New account fields:
   - user_type='member'
   - name = Google name
   - display_name = Google name
   - email = Google email
   - google_id = Google ID
   - profile_slug = generated from display_name
   - password = random secure string
   - email_verified_at = now
   - is_active = true
5. Log in, regenerate session, update last_login_at.
6. Redirect member → /account/dashboard.

Security rules:
- Google OAuth must NEVER create an admin account.
- Existing admin emails are protected from takeover.
- Log the social login event.

TASK 5: ROUTES
Add to routes/web.php:
Route::get('/auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');

TASK 6: GOOGLE BUTTON COMPONENT
Create resources/views/components/auth/google-button.blade.php.
- Google "G" logo (inline SVG).
- Text: "Continue with Google".
- Links to route('auth.google').
- Accessible, theme-consistent (white bg, neutral border, hover).

TASK 7: INTEGRATE INTO AUTH PAGES
Modify login.blade.php and register.blade.php:
- Add Google button above the email/password form.
- Add an "OR" divider between Google button and email form.

TASK 8: FEATURE TESTS
Create tests/Feature/Auth/GoogleAuthTest.php.
1. test_google_redirect_route_exists
2. test_google_callback_creates_new_member
3. test_google_callback_logs_in_existing_member
4. test_google_callback_rejects_admin_email
5. test_google_callback_sets_email_verified

Mock Socialite in tests (do not call real Google API).

SECURITY REQUIREMENTS:
- Google OAuth cannot create admin accounts.
- Admin emails protected from takeover.
- Session regenerated on social login.
- Socialite exceptions handled gracefully.

DEFINITION OF DONE:
- Socialite installed and configured.
- google_id column exists.
- "Continue with Google" on login and register pages.
- New Google users created as members.
- Existing members can log in via Google.
- Admin accounts protected.
- Works locally with localhost redirect URI.
- All feature tests pass.Test.php

