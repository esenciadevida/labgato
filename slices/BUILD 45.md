labgato/
├── app/
│   └── Http/
│       └── Middleware/
│           ├── SecurityHeaders.php
│           ├── AdminInactivityTimeout.php
│           ├── LimitRequestSize.php
│           └── HoneypotProtection.php
├── bootstrap/
│   └── app.php
├── config/
│   ├── session.php
│   ├── auth.php
│   ├── cors.php
│   └── labgato.php
├── public/
│   └── .htaccess
├── resources/
│   └── views/
│       ├── auth/
│       │   ├── login.blade.php
│       │   └── register.blade.php
│       └── public/
│           └── contact.blade.php
├── .env.example
├── .gitignore
└── tests/
    └── Feature/
        └── Security/
            ├── SecurityHeadersTest.php
            ├── InactivityTimeoutTest.php
            ├── HoneypotTest.php
            └── EnumerationPreventionTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD45-SECURITY-HARDENING

CONTEXT:
LabGato needs a comprehensive security hardening pass. Some measures exist; many are missing.
This build implements the missing controls and verifies the existing ones.

OBJECTIVE:
Implement HSTS + security headers, admin inactivity timeout, request size limits, bot
protection, explicit reset-link expiry, secure cookie flags, CORS lockdown, .htaccess
hardening, DB least-privilege guidance, and git hygiene. Verify existing controls.

FILES TO INSPECT:
- bootstrap/app.php
- config/session.php, config/auth.php, config/cors.php
- public/.htaccess
- .gitignore, .env.example
- app/Http/Controllers/Auth/ (login, register, reset)
- resources/views/auth/login.blade.php, register.blade.php
- resources/views/public/contact.blade.php

FILES TO CREATE:
- app/Http/Middleware/SecurityHeaders.php
- app/Http/Middleware/AdminInactivityTimeout.php
- app/Http/Middleware/LimitRequestSize.php
- app/Http/Middleware/HoneypotProtection.php
- tests/Feature/Security/SecurityHeadersTest.php
- tests/Feature/Security/InactivityTimeoutTest.php
- tests/Feature/Security/HoneypotTest.php
- tests/Feature/Security/EnumerationPreventionTest.php

FILES TO MODIFY:
- bootstrap/app.php
- config/session.php
- config/auth.php
- config/cors.php
- public/.htaccess
- .env.example
- .gitignore
- auth login/register + contact views (honeypot field)

TASK 1: SECURITY HEADERS MIDDLEWARE (HSTS + BUNDLE)

Create app/Http/Middleware/SecurityHeaders.php.

Add these headers to every response:
- Strict-Transport-Security: max-age=31536000; includeSubDomains
  (ONLY when the request is secure / APP_ENV=production; skip on plain HTTP local dev).
- X-Frame-Options: SAMEORIGIN
- X-Content-Type-Options: nosniff
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()
- Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline';
  style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:;
  connect-src 'self' (pragmatic; 'unsafe-inline' needed for Alpine/Livewire).
- X-Permitted-Cross-Domain-Policies: none

Register as global middleware in bootstrap/app.php (apply to all responses).

TASK 2: ADMIN INACTIVITY TIMEOUT

Create app/Http/Middleware/AdminInactivityTimeout.php.

Logic:
- Apply to /admin/* routes.
- Store last_activity timestamp in session.
- On each request: if (now - last_activity) > threshold, log out and redirect to
  /admin/login with "Session expired due to inactivity."
- Threshold: config('labgato.security.admin_inactivity_minutes', 30).
- Otherwise update last_activity = now.

Add config key to config/labgato.php:
'security' => [ 'admin_inactivity_minutes' => env('ADMIN_INACTIVITY_MINUTES', 30) ]

Register middleware on the admin route group.

TASK 3: REQUEST SIZE LIMIT

Create app/Http/Middleware/LimitRequestSize.php.

Logic:
- For non-upload routes, reject requests with Content-Length > 1MB (return 413).
- Exclude upload routes (CSV import, attachments) which have their own limits.
- Register globally or on web group.

TASK 4: BOT PROTECTION (HONEYPOT)

Create app/Http/Middleware/HoneypotProtection.php OR add inline validation.

Add a hidden honeypot field to public forms (login, register, contact):
- Field name: "website" (hidden via CSS, aria-hidden, tabindex=-1, autocomplete=off).
- On submit, if the field is filled, reject silently (redirect back with generic success
  to avoid tipping off bots, but do NOT process the action).

Add the field to login.blade.php, register.blade.php, contact.blade.php.
Validate in the corresponding controllers/requests: if filled, abort the action quietly.

TASK 5: SESSION & COOKIE HARDENING

Modify config/session.php:
- 'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production')
- 'http_only' => true
- 'same_site' => 'lax'
- 'lifetime' => 120 (minutes)

Update .env.example:
SESSION_SECURE_COOKIE=true   (for production)

TASK 6: RESET LINK EXPIRY + THROTTLE

Modify config/auth.php:
'passwords' => [ 'users' => [ ... 'expire' => 60, 'throttle' => 60 ] ]
(expire = 60 minutes; throttle = 60s between resets).

TASK 7: CORS LOCKDOWN

Modify config/cors.php:
'paths' => ['api/*'],
'allowed_methods' => ['GET'],
'allowed_origins' => [env('APP_URL', 'http://localhost:8000')],
'allowed_headers' => ['Content-Type', 'X-Requested-With'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,

(Since the app is server-rendered, CORS should be minimal.)

TASK 8: .HTACCESS HARDENING

Modify public/.htaccess. Add:

Options -Indexes
ServerSignature Off

# Deny access to sensitive files
<FilesMatch "^\.env|\.git|composer\.(json|lock)|package(-lock)?\.json|phpunit\.xml">
  Require all denied
</FilesMatch>

# HSTS (also set via middleware; belt-and-suspenders)
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"

# Disable MIME sniffing
Header always set X-Content-Type-Options "nosniff"

TASK 9: DB LEAST-PRIVILEGE + GIT HYGIENE (DOCUMENTATION)

Add comments/notes (or a docs file) describing:
- Create a dedicated MySQL user with only SELECT, INSERT, UPDATE, DELETE on the app DB:
  CREATE USER 'labgato_app'@'localhost' IDENTIFIED BY 'strong_password';
  GRANT SELECT, INSERT, UPDATE, DELETE ON labgato.* TO 'labgato_app'@'localhost';
  (No DROP/ALTER/CREATE in production.)
- Git hygiene: ensure .env, .env.*, storage/, vendor/ are in .gitignore.
  If secrets were ever committed, rotate them and rewrite history (git filter-repo).

Verify .gitignore includes: .env, .env.*, node_modules, vendor, storage/app/*, etc.

TASK 10: VERIFY EXISTING CONTROLS

Confirm (and fix if broken):
- Login + password reset use generic errors (no user enumeration).
- Upload whitelist enforced on attachments + CSV (mime + extension + size).
- AI usage caps active.
- APP_DEBUG=false in .env.example and production.
- No DB::raw / raw SQL without bindings anywhere (search codebase).
- Mass assignment protected via $fillable on all models.

TASK 11: SECURITY TESTS

Create tests/Feature/Security/SecurityHeadersTest.php:
- Test responses include HSTS (when secure), X-Frame-Options, nosniff, Referrer-Policy.

Create tests/Feature/Security/InactivityTimeoutTest.php:
- Test admin is logged out after inactivity threshold.
- Test active admin session persists under threshold.

Create tests/Feature/Security/HoneypotTest.php:
- Test filling the honeypot field prevents the action (register/contact).
- Test leaving it empty allows normal processing.

Create tests/Feature/Security/EnumerationPreventionTest.php:
- Test login with unknown email returns generic error.
- Test password reset with unknown email returns generic success message.

DEFINITION OF DONE:
- HSTS + security headers on all responses.
- Admin sessions expire after inactivity.
- Oversized non-upload requests rejected (413).
- Honeypot blocks bots on public forms.
- Secure/HTTP-only/same-site cookies; reset links expire in 60 min.
- CORS locked down to minimal.
- .htaccess disables directory listing and protects sensitive files.
- DB least-privilege + git hygiene documented.
- Existing controls verified (enumeration, uploads, AI caps, debug off).
- All security tests pass.