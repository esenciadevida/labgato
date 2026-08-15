labgato/
├── .env (local only, not committed)
├── .env.example
├── config/
│   ├── database.php
│   └── labgato.php
└── database/
    └── database.sqlite (delete if present)

IMPLEMENTATION PROMPT ID: LABGATO-PHASE06-BUILD25

CONTEXT:
LabGato Phase 06 is approved. Before implementing public user accounts and community contributions, we must align the local development environment with production (MySQL) and activate the AI features.

This is a setup and configuration build. It does not introduce new application features.

OBJECTIVE:
Switch the local development database from SQLite to MySQL, activate the AI features with the Groq API key, and verify that all existing migrations and tests work correctly on MySQL.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- Production uses MySQL on Hostinger.
- Local development may have been using SQLite (Laravel default).
- AI features are disabled by default (AI_ENABLED=false, FEATURE_AI=false).
- Groq is the AI provider.
- The user has a Groq API key and MySQL installed.

FILES TO INSPECT:
- .env
- .env.example
- config/database.php
- config/labgato.php
- database/database.sqlite (check if exists)

FILES TO MODIFY:
- .env (local only)
- .env.example

FILES TO DELETE (if present):
- database/database.sqlite

TASK 1: CREATE LOCAL MYSQL DATABASE

Create a MySQL database for LabGato local development.

Using MySQL CLI or a tool like phpMyAdmin, MySQL Workbench, or TablePlus:

CREATE DATABASE labgato CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

Verify the database was created successfully.

TASK 2: UPDATE .ENV FOR MYSQL

Modify the local .env file (do NOT commit this file).

Update the database section:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=labgato
DB_USERNAME=root
DB_PASSWORD=

Replace DB_USERNAME and DB_PASSWORD with your actual local MySQL credentials if different.

Remove or comment out any SQLite-specific configuration if present.

TASK 3: UPDATE .ENV.EXAMPLE

Modify .env.example to reflect MySQL as the default database.

Update the database section:

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=labgato
DB_USERNAME=root
DB_PASSWORD=

Ensure the AI section is present with safe defaults:

AI_ENABLED=false
AI_PROVIDER=
GROQ_API_KEY=
GROQ_MODEL=llama-3.1-70b-versatile
AI_TIMEOUT_SECONDS=15
AI_MAX_TOKENS=2048
AI_TEMPERATURE=0.7

FEATURE_AI=false

TASK 4: DELETE SQLITE DATABASE (IF PRESENT)

If database/database.sqlite exists, delete it.

This ensures no accidental fallback to SQLite.

TASK 5: RUN MIGRATIONS ON MYSQL

Run the following commands to verify all migrations work on MySQL:

php artisan config:clear
php artisan cache:clear
php artisan migrate:fresh --seed

Expected outcome:
- All migrations run without errors.
- All seeders run without errors.
- Full-text indexes are created (InnoDB supports them).
- JSON columns work correctly.
- Foreign keys are enforced.

If any migration fails, investigate and fix the MySQL compatibility issue before proceeding.

TASK 6: ACTIVATE AI FEATURES

Modify the local .env file to activate AI.

Update the AI section:

AI_ENABLED=true
AI_PROVIDER=groq
GROQ_API_KEY=gsk_your_actual_key_here
GROQ_MODEL=llama-3.1-70b-versatile
AI_TIMEOUT_SECONDS=15
AI_MAX_TOKENS=2048
AI_TEMPERATURE=0.7

FEATURE_AI=true

Replace gsk_your_actual_key_here with your real Groq API key.

IMPORTANT: Never commit the real API key. The .env file is gitignored.

TASK 7: VERIFY AI ACTIVATION

Clear the config cache:

php artisan config:clear

Verify AI works:
1. Start the local development server: php artisan serve
2. Log in as Super Admin.
3. Navigate to Admin → Knowledge → Create.
4. Click the "Draft with AI" button.
5. Confirm that a response is generated successfully.

If AI does not work:
- Check that the API key is correct.
- Check that AI_ENABLED=true and FEATURE_AI=true.
- Check the Laravel logs (storage/logs/laravel.log) for errors.
- Verify the Groq API is reachable from your network.

TASK 8: RUN FULL TEST SUITE

Run the full test suite to ensure no SQLite-specific behavior remains:

php artisan test

Expected outcome:
- All tests pass.
- No tests fail due to SQLite-specific SQL syntax.
- No tests fail due to missing MySQL features.

If any tests fail:
- Investigate whether the test uses SQLite-specific syntax.
- Update the test or migration to be MySQL-compatible.
- Re-run the tests until all pass.

TASK 9: VERIFY PRODUCTION READINESS

Confirm the following:
- .env uses MySQL.
- .env.example uses MySQL.
- AI is activated locally.
- All migrations run on MySQL.
- All tests pass.
- No database.sqlite file exists.

DEFINITION OF DONE:
- Local MySQL database is created and configured.
- .env and .env.example use MySQL.
- SQLite database file is deleted (if it existed).
- All migrations run successfully on MySQL.
- AI features are activated and verified working.
- Full test suite passes.
- No SQLite-specific behavior remains.
- No new application features are introduced.