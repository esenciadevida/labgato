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

## Question authoring (ASCP standard)

Aim for ASCP MLS-style items - most should be clinical vignettes, not one-line recall:
- Use `type: "case"` with a `case_text` vignette (age/sex + presentation + lab values/results), then a focused `stem` ending in the actual question.
- Exactly 4 plausible options, **single best answer**. Distractors must be plausible and at consistent length (avoid single-word options where possible).
- No "all/none of the above" stems/options, no negative stems (`EXCEPT`/`NOT`) unless genuinely required.
- Always write a teaching `explanation` that states the correct answer AND why the distractors are wrong.
- Set `difficulty` (`easy`/`intermediate`/`advanced`) and `source_type: "original"`; seed as `status: "active"`.
- Verify with `php artisan questions:quality-report` (target score >= 70) and `php artisan questions:ascp-audit` (composition vs ASCP weights).
- Balance authoring toward the ASCP-heavy areas (Chemistry & Hematology ~20-25% each) and be wary of over-supplying Laboratory Operations.

## Reference range governance

`reference_ranges` is the single source of truth for reference data. SOPs link canonical ranges via a pivot; calculators resolve them via `reference_key` on output fields (`CalculatorService::resolveReference`, unit-aware).

- **SOP dumps** carry a `reference_ranges: [slugs]` key; `MasterContentImporter::upsertSop` syncs the pivot (`sop_reference_range`). The SOP show page renders the canonical panel; range show pages list "Covered in SOPs".
- **Decision thresholds that are not a single adult interval must live as prose** in the SOP body under a `Decision limits` heading (e.g. therapeutic INR 2.0–3.0, HbA1c ADA categories, APTT heparin target, random glucose <140) - never rewrite them as a canonical range.
- **Calculator `references` = literature sources only.** Range-authority hosts (medscape reference, Lab Tests Guide, Medical Algorithms, MedAptly, Loyola/meddean) are banned. Enforce with `php artisan calculator-references:clean` (idempotent, in-place) and monitor with `php artisan ranges:audit`.
- When adding a SOP: check `php artisan ranges:audit` for `[!]` lines (hardcoded-interval dumps without `reference_ranges`, output fields missing `reference_key`, banned host links).

## Deployment (Hostinger)

- Remotes: `origin` = GitHub, `hostinger` = production docroot (non-bare, `updateInstead`).
- **Server CLI `php` is 8.3 but the app needs 8.4** - use `/opt/alt/php84/usr/bin/php artisan ...` over SSH.
- `public/build/` is gitignored: after `npm run build`, sync assets manually (tar+scp into `public_html/public/`). Use `./scripts/sync-assets.ps1` (builds, syncs `public/build`, clears it on the server, then runs `optimize`).
- **New Tailwind utility classes in Blade only take effect after `npm run build` + syncing `public/build`** - otherwise the class silently has no effect in production.
- **Any Blade/UI change requires `/opt/alt/php84/usr/bin/php artisan optimize`** on the server (view cache) or the old template keeps rendering.
- If `composer.json` / `composer.lock` changed, run Composer on the server with the PHP 8.4 binary so new vendor packages are installed (e.g. `COMPOSER_MEMORY_LIMIT=-1 /opt/alt/php84/usr/bin/php /usr/local/bin/composer install --no-dev --no-interaction`).
- After pushing: `/opt/alt/php84/usr/bin/php artisan migrate --force && php artisan optimize`.
- Server-only files `.htaccess` / `.user.ini` are protected via `.git/info/exclude` on the server.
- If push is rejected with "Working directory has unstaged changes", someone edited the server directly - stash there first and investigate before discarding.

## Featured images

- Admin forms accept an external image URL (`featured_image_url`; upload > URL > remove). The CSP `img-src` blocks unknown hosts, and remote URLs (e.g. LinkedIn signed links, which expire) can stop rendering.
- **Standard: self-host external featured images** with `php artisan featured-images:download` (`--dry-run` previews). It scans knowledge/SOP/interpretation `featured_image` columns for `http(s)` URLs, runs each through `ContentImageService` (WebP + responsive variants, 5 MB cap), and repoints the row to the local path. Idempotent, run at quiet times (slow, like `questions:download-images`).

## Gotchas

- **Certificate PDFs cache on disk** at `storage/app/public/certificates/*.pdf`; `CertificateService::ensurePdf()` returns the cached file. Cleared automatically when certificate settings are saved, but clear manually (or re-download) to pick up template changes.
- **Image linking/downloading**: `questions:link-images` attaches Wikimedia image URLs to image-typed questions (idempotent). `questions:download-images` self-hosts them to `image_path` (slow ~20s/img due to Wikimedia throttling; run at a quiet time).
- Recently viewed types: `sop`, `knowledge`, `calculator`, `interpretation` (see `RecentlyViewedService`).
- Contribution types enum: `sop`, `knowledge`, `interpretation`.
- Member routes live under `routes/member.php` prefix `account`, guarded by `member` + `active.user` middleware.
