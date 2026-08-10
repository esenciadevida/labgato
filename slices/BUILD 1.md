IMPLEMENTATION PROMPT ID: LABGATO-SLICE1-BUILD1

CONTEXT:
LabGato is a professional laboratory toolkit built with Laravel, Blade, Tailwind CSS, Alpine.js, and Livewire. The project must be compatible with Hostinger PHP 8.4 shared hosting. Phase 01 and Phase 02 documentation are approved. We are now implementing Slice 1: Application Foundation.

OBJECTIVE:
Initialize the Laravel project and configure the environment for local development and Hostinger compatibility.

EXISTING ARCHITECTURE:
- Laravel latest stable compatible with PHP 8.4.
- Database driver: MySQL.
- Session driver: database.
- Cache driver: database.
- Queue driver: database.
- Frontend: Vite + Tailwind CSS + Alpine.js.
- No Redis.
- No Node.js on production server.
- Secrets must never be committed.

FILES TO CREATE:
- composer.json
- package.json
- vite.config.js
- tailwind.config.js
- postcss.config.js
- .env.example
- config/labgato.php
- public/.htaccess
- public/robots.txt
- resources/css/app.css
- resources/js/app.js
- resources/js/bootstrap.js
- routes/web.php
- routes/admin.php
- routes/auth.php
- routes/api.php

FILES TO MODIFY:
- .env
- config/app.php
- config/database.php
- config/session.php
- config/cache.php
- config/queue.php
- config/logging.php
- bootstrap/app.php

TASKS:
1. Create a new Laravel project using the latest stable version compatible with PHP 8.4.
2. Configure .env.example with the following required variables:
   - APP_NAME="LabGato"
   - APP_ENV=local
   - APP_DEBUG=true
   - APP_URL=http://localhost
   - DB_CONNECTION=mysql
   - SESSION_DRIVER=database
   - CACHE_STORE=database
   - QUEUE_CONNECTION=database
   - AI_ENABLED=false
   - LOG_CHANNEL=stack
3. Create config/labgato.php with placeholder configuration for AI, uploads, security, and feature flags.
4. Configure config/session.php to use the database driver and secure cookie settings.
5. Configure config/cache.php to use the database driver.
6. Configure config/queue.php to use the database driver.
7. Configure config/logging.php to use daily logs with a 14-day retention limit.
8. Install and configure Tailwind CSS, Alpine.js, and Vite.
9. Create resources/css/app.css with Tailwind directives.
10. Create resources/js/app.js and resources/js/bootstrap.js with Alpine.js initialization.
11. Create route files: routes/web.php, routes/admin.php, routes/auth.php, routes/api.php.
12. Ensure public/.htaccess is configured for Laravel and denies access to sensitive files.
13. Ensure public/robots.txt disallows /admin, /auth, and /api.
14. Initialize Git repository and commit the initial Laravel skeleton.

DATABASE:
No migrations yet. Database configuration only.

API:
No API endpoints yet. Route file preparation only.

UI:
No UI components yet. Asset pipeline preparation only.

SECURITY:
- Ensure APP_KEY is generated securely.
- Ensure .env is included in .gitignore.
- Ensure debug mode is enabled only for local development.
- Ensure public/.htaccess protects sensitive directories.

ACCESSIBILITY:
No accessibility implementation required for Build 1.

SEO:
Create public/robots.txt with disallow rules for /admin, /auth, and /api.

TESTING:
- Verify the application boots locally.
- Verify php artisan about displays the correct PHP version and drivers.
- Verify npm run dev compiles assets successfully.
- Verify npm run build compiles production assets successfully.

DEFINITION OF DONE:
- Laravel project boots locally without errors.
- .env.example contains all required variables.
- config/labgato.php exists.
- Tailwind CSS and Alpine.js compile successfully.
- Route files exist and are registered.
- Git repository initialized with initial commit.
- No secrets committed.

labgato/
├── app/
│   ├── Enums/
│   │   └── ResourceStatus.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── Role.php
│   │   ├── Permission.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Setting.php
│   │   └── AuditLog.php
│   ├── Policies/
│   │   ├── UserPolicy.php
│   │   ├── RolePolicy.php
│   │   └── SettingPolicy.php
│   ├── Services/
│   │   ├── Auth/
│   │   │   └── AuthService.php
│   │   ├── Settings/
│   │   │   └── SettingService.php
│   │   └── AuditLogService.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── DashboardController.php
│   │   │   ├── PublicSite/
│   │   │   │   ├── HomeController.php
│   │   │   │   └── LegalController.php
│   │   │   └── Auth/
│   │   │       ├── LoginController.php
│   │   │       └── PasswordResetController.php
│   │   ├── Middleware/
│   │   │   ├── EnsureUserIsActive.php
│   │   │   └── CheckPermission.php
│   │   ├── Requests/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginRequest.php
│   │   │   │   └── PasswordResetRequest.php
│   │   │   └── Admin/
│   │   └── Livewire/
│   │       ├── Admin/
│   │       └── PublicSite/
│   ├── Providers/
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   └── RouteServiceProvider.php
│   └── Support/
├── bootstrap/
│   ├── app.php
│   └── cache/
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cache.php
│   ├── database.php
│   ├── filesystems.php
│   ├── labgato.php
│   ├── logging.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php
│   └── session.php
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── RoleFactory.php
│   │   └── PermissionFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php
│   │   ├── 0001_01_01_000001_create_roles_table.php
│   │   ├── 0001_01_01_000002_create_permissions_table.php
│   │   ├── 0001_01_01_000003_create_role_user_table.php
│   │   ├── 0001_01_01_000004_create_permission_role_table.php
│   │   ├── 0001_01_01_000005_create_settings_table.php
│   │   ├── 0001_01_01_000006_create_audit_logs_table.php
│   │   ├── 0001_01_01_000007_create_categories_table.php
│   │   ├── 0001_01_01_000008_create_tags_table.php
│   │   ├── 0001_01_01_000009_create_sessions_table.php
│   │   ├── 0001_01_01_000010_create_cache_table.php
│   │   ├── 0001_01_01_000011_create_jobs_table.php
│   │   └── 0001_01_01_000012_create_password_reset_tokens_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RolePermissionSeeder.php
│       └── AdminUserSeeder.php
├── public/
│   ├── .htaccess
│   ├── index.php
│   └── robots.txt
├── resources/
│   ├── css/
│   │   └── app.css
│   ├── js/
│   │   ├── app.js
│   │   └── bootstrap.js
│   └── views/
│       ├── layouts/
│       │   ├── public.blade.php
│       │   ├── admin.blade.php
│       │   ├── auth.blade.php
│       │   └── error.blade.php
│       ├── components/
│       │   ├── ui/
│       │   │   ├── button.blade.php
│       │   │   ├── input.blade.php
│       │   │   ├── alert.blade.php
│       │   │   └── badge.blade.php
│       │   ├── admin/
│       │   │   ├── sidebar.blade.php
│       │   │   └── header.blade.php
│       │   └── public/
│       │       ├── meta.blade.php
│       │       ├── header.blade.php
│       │       └── footer.blade.php
│       ├── auth/
│       │   ├── login.blade.php
│       │   ├── forgot-password.blade.php
│       │   └── reset-password.blade.php
│       ├── admin/
│       │   └── dashboard.blade.php
│       ├── public/
│       │   └── home.blade.php
│       ├── legal/
│       │   ├── privacy-policy.blade.php
│       │   ├── terms-of-service.blade.php
│       │   ├── disclaimer.blade.php
│       │   └── accessibility-statement.blade.php
│       └── errors/
│           ├── 403.blade.php
│           ├── 404.blade.php
│           ├── 500.blade.php
│           └── 503.blade.php
├── routes/
│   ├── web.php
│   ├── admin.php
│   ├── auth.php
│   └── api.php
├── storage/
│   ├── app/
│   ├── framework/
│   └── logs/
├── tests/
│   ├── Feature/
│   │   ├── Admin/
│   │   ├── Auth/
│   │   └── PublicSite/
│   └── Unit/
│       └── Models/
├── .env.example
├── artisan
├── composer.json
├── package.json
├── tailwind.config.js
├── vite.config.js
└── README.md