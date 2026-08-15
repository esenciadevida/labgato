labgato/
├── composer.json
├── config/
│   └── backup.php
├── app/
│   └── Console/
│       └── Commands/
│           └── GenerateSitemap.php
├── resources/
│   └── views/
│       ├── errors/
│       │   ├── 404.blade.php
│       │   ├── 403.blade.php
│       │   ├── 500.blade.php
│       │   └── 503.blade.php
│       ├── public/
│       │   └── legal/
│       │       ├── privacy.blade.php
│       │       ├── terms.blade.php
│       │       └── disclaimer.blade.php
│       ├── components/
│       │   ├── public/
│       │   │   ├── cookie-consent.blade.php
│       │   │   ├── footer.blade.php
│       │   │   └── meta.blade.php
│       │   └── layouts/
│       │       └── public.blade.php
├── routes/
│   └── web.php
├── routes/
│   └── console.php
└── tests/
    └── Feature/
        └── PublicSite/
            └── PreLaunchPolishTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD46-PRELAUNCH-POLISH

CONTEXT:
LabGato is feature-complete and security-hardened. This final build adds the operational,
legal, and SEO elements required for a professional public launch: custom error pages,
legal pages, cookie consent, XML sitemap, Open Graph tags, and automated backups.

OBJECTIVE:
Implement custom error pages, legal pages with footer links, a cookie consent banner,
a scheduled XML sitemap generator, Open Graph meta tags, and Spatie Backup for daily
MySQL dumps.

EXISTING ARCHITECTURE:
- Abyss Blue theme with dark mode.
- Public layout and meta component.
- 6 resource types with public pages.
- Laravel Scheduler (routes/console.php).
- Hostinger shared hosting.

FILES TO INSPECT:
- resources/views/layouts/public.blade.php
- resources/views/components/public/meta.blade.php
- resources/views/components/public/footer.blade.php
- routes/web.php
- routes/console.php
- composer.json

FILES TO CREATE:
- resources/views/errors/404.blade.php
- resources/views/errors/403.blade.php
- resources/views/errors/500.blade.php
- resources/views/errors/503.blade.php
- resources/views/public/legal/privacy.blade.php
- resources/views/public/legal/terms.blade.php
- resources/views/public/legal/disclaimer.blade.php
- resources/views/components/public/cookie-consent.blade.php
- app/Console/Commands/GenerateSitemap.php
- tests/Feature/PublicSite/PreLaunchPolishTest.php

FILES TO MODIFY:
- composer.json (add spatie/laravel-backup)
- config/backup.php (publish + configure)
- resources/views/components/public/footer.blade.php
- resources/views/components/public/meta.blade.php
- resources/views/layouts/public.blade.php
- routes/web.php
- routes/console.php

TASK 1: CUSTOM ERROR PAGES

Create resources/views/errors/404.blade.php, 403.blade.php, 500.blade.php, 503.blade.php.

Rules:
- Standalone pages (do NOT extend the full public layout, to avoid errors if the DB
  or layout fails). Use a minimal inline <html> with the theme's compiled Tailwind classes.
- Dark-friendly background (bg-slate-950 text-slate-100).
- Large status code (e.g., "404") in primary color.
- Friendly message:
  - 404: "Page not found. The resource you're looking for may have moved."
  - 403: "Access denied. You don't have permission to view this page."
  - 500: "Something went wrong on our end. Please try again shortly."
  - 503: "We're undergoing maintenance. We'll be back shortly."
- Buttons: "Go Home" (link /) and "Contact Support" (link /contact).
- Include the LabGato name/logo text.

TASK 2: LEGAL PAGES

Create three legal views under resources/views/public/legal/:

privacy.blade.php — Privacy Policy (what data is collected, how it's used, cookies,
  user rights, contact). Use clear placeholder structure the owner can customize.
terms.blade.php — Terms of Service (acceptable use, IP, liability, account terms).
disclaimer.blade.php — Medical/Educational Disclaimer (content is for educational and
  reference purposes only; verify with validated institutional procedures; not medical advice).

Each page:
- Uses the public layout.
- Has a clear h1, last-updated date, and sectioned content.
- Professional, readable typography (.resource-content).

Add routes to routes/web.php:
- GET /privacy → privacy view (name: legal.privacy)
- GET /terms → terms view (name: legal.terms)
- GET /disclaimer → disclaimer view (name: legal.disclaimer)

Modify resources/views/components/public/footer.blade.php:
- Add footer links: Privacy Policy, Terms of Service, Medical Disclaimer, Contact.

TASK 3: COOKIE CONSENT BANNER

Create resources/views/components/public/cookie-consent.blade.php.

An Alpine.js fixed bottom banner:
- Shows only if localStorage 'labgato-cookie-consent' is not set.
- Text: "We use cookies to improve your experience and analyze site traffic. By
  continuing, you agree to our use of cookies." + link to Privacy Policy.
- Buttons: "Accept" and "Decline".
- On click: store 'accepted' or 'declined' in localStorage and hide the banner.
- Non-blocking (does not prevent page interaction).
- Dark mode + mobile friendly (stacks on small screens).
- Accessible (role="dialog", aria-label).

Modify resources/views/layouts/public.blade.php:
- Include <x-public.cookie-consent /> before </body>.

TASK 4: XML SITEMAP GENERATOR

Create app/Console/Commands/GenerateSitemap.php.

Signature: sitemap:generate

Logic:
1. Build a list of URLs:
   - Static: /, /calculators, /tools, /reference-ranges, /sops, /interpretation, /knowledge, /contact.
   - All published resources across the 6 types (their public URLs).
   - Active contributor profiles (optional).
2. For each URL include <loc>, <lastmod> (updated_at date), <changefreq>, <priority>.
3. Write valid XML to public/sitemap.xml.
4. Log the count of URLs.

Register in routes/console.php:
Schedule::command('sitemap:generate')->daily();

Add route (fallback if file missing): GET /sitemap.xml serves public/sitemap.xml.

Ensure robots.txt references the sitemap (add "Sitemap: {APP_URL}/sitemap.xml").

TASK 5: OPEN GRAPH + TWITTER META

Modify resources/views/components/public/meta.blade.php.

Add Open Graph and Twitter tags, using per-page values when provided (via $slot/props or
view data) with sensible defaults from settings:

<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:site_name" content="{{ setting('site_name') }}">
<meta property="og:title" content="{{ $ogTitle ?? $title }}">
<meta property="og:description" content="{{ $ogDescription ?? $description }}">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $ogImage ?? url('/images/og-default.png') }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="...">
<meta name="twitter:description" content="...">

Ensure resource detail pages pass their title/description as og values.

TASK 6: AUTOMATED BACKUPS (SPATIE)

Run: composer require spatie/laravel-backup
Run: php artisan vendor:publish --provider="Spatie\Backup\BackupServiceProvider"

Configure config/backup.php:
- backup.name = 'labgato'
- backup.source.files.include = [base_path()] but EXCLUDE vendor, node_modules, storage/cache.
- backup.source.files.exclude = vendor, node_modules, .git.
- backup.database.dump = ['mysqldump'] with the MySQL connection.
- backup.destination.disks = ['local'] (storage/app/LabGato/backups).
- Configure cleanup: keep daily for 7 days, weekly for 4 weeks (default cleanup strategy).

Register in routes/console.php:
Schedule::command('backup:run')->dailyAt('02:00');
Schedule::command('backup:clean')->dailyAt('03:00');

Document in a comment that on shared hosting, backups should be downloaded periodically
or pointed to an external disk (S3) when available.

TASK 7: FEATURE TESTS

Create tests/Feature/PublicSite/PreLaunchPolishTest.php:
- Test 404 page renders with theme (visit a bad URL).
- Test legal pages render (/privacy, /terms, /disclaimer).
- Test footer contains legal links.
- Test sitemap:generate creates public/sitemap.xml with published resource URLs.
- Test meta component includes og:title and og:description.
- Test cookie consent component is present in the public layout.

DEFINITION OF DONE:
- Custom error pages render for 404/403/500/503 with theme.
- Legal pages exist and are linked in the footer.
- Cookie consent banner shows once and respects the stored choice.
- sitemap.xml is generated with all published resources and scheduled daily.
- Open Graph + Twitter tags present on public pages.
- Spatie Backup configured for daily MySQL dumps with cleanup.
- All feature tests pass.