# AGENTS.md

Guidance for AI coding agents working in this repository.

## Project

Labgoto: Laravel 13 medical-laboratory knowledge platform. Blade + Livewire + Alpine.js + Tailwind v4. Production runs on Hostinger shared hosting (PHP 8.4).

## Commands

```bash
# Tests - run suites SEPARATELY; a single full "php artisan test" run times out
php artisan test tests/Feature --compact   # ~4 min
php artisan test tests/Unit --compact

# Single test file
php artisan test tests/Feature/Member/OnboardingTest.php --compact

# Frontend
npm run build

# Lint PHP syntax after edits
php -l <file>
```

## Conventions

- **Never use `@js(...)` inside double-quoted HTML attributes** (`x-data="..."`) - it breaks attribute parsing. Use `data-*` attributes instead.
- Fetch calls must send `'X-CSRF-TOKEN': '{{ csrf_token() }}'` and `'Accept': 'application/json'` headers (Laravel then returns JSON 422 automatically).
- Alpine components are written inline in Blade views; shared JS goes in `resources/js/alpine/` or feature folders under `resources/js/`.
- Dark mode: always pair classes with `dark:` variants. Tailwind v4 CSS variables (`var(--color-slate-800)`) in app.css.
- User-generated rich text MUST pass through `App\Support\RichTextSanitizer::render()` before display.
- No emojis in code or UI copy.
- Migrations: timestamp-prefixed, descriptive names.

## Deployment (Hostinger)

- Remotes: `origin` = GitHub, `hostinger` = production docroot (non-bare, `updateInstead`).
- **Server CLI `php` is 8.3 but the app needs 8.4** - use `/opt/alt/php84/usr/bin/php artisan ...` over SSH.
- `public/build/` is gitignored: after `npm run build`, sync assets manually (tar+scp into `public_html/public/`).
- If `composer.json` / `composer.lock` changed, run Composer on the server with the PHP 8.4 binary so new vendor packages are installed (e.g. `COMPOSER_MEMORY_LIMIT=-1 /opt/alt/php84/usr/bin/php /usr/local/bin/composer install --no-dev --no-interaction`).
- After pushing: `/opt/alt/php84/usr/bin/php artisan migrate --force && php artisan optimize`.
- Server-only files `.htaccess` / `.user.ini` are protected via `.git/info/exclude` on the server.
- If push is rejected with "Working directory has unstaged changes", someone edited the server directly - stash there first and investigate before discarding.

## Gotchas

- Recently viewed types: `sop`, `knowledge`, `calculator`, `interpretation` (see `RecentlyViewedService`).
- Contribution types enum: `sop`, `knowledge`, `interpretation`.
- Member routes live under `routes/member.php` prefix `account`, guarded by `member` + `active.user` middleware.
