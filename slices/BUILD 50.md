labgato/
├── database/
│   ├── migrations/
│   │   └── xxxx_create_launch_settings_table.php
│   └── seeders/
│       └── LaunchSettingSeeder.php
├── app/
│   ├── Models/
│   │   └── LaunchSetting.php
│   ├── Http/
│   │   ├── Middleware/
│   │   │   └── LaunchWall.php
│   │   └── Controllers/
│   │       ├── LaunchController.php
│   │       └── Admin/
│   │           └── LaunchControlController.php
│   └── Console/
│       └── Commands/
│           └── ActivateLaunch.php
├── bootstrap/
│   └── app.php
├── resources/
│   └── views/
│       ├── launch/
│       │   ├── wall.blade.php
│       │   └── dashboard.blade.php
│       ├── admin/
│       │   └── launch/
│       │       └── index.blade.php
│       └── components/
│           └── launch/
│               └── countdown.blade.php
├── routes/
│   ├── web.php
│   ├── admin.php
│   └── console.php
└── tests/
    └── Feature/
        └── Launch/
            └── LaunchSystemTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD50-LAUNCH-SYSTEM

CONTEXT:
LabGATO's launch is a controlled event. Before launch, visitors see a launch wall with a
countdown instead of the normal website. The launch state is controlled entirely by the
backend — the browser must NEVER determine whether the site has launched. The Super Admin
controls every aspect from a Launch Control panel.

OBJECTIVE:
Build the launch settings model, launch wall middleware, public launch page, launch
dashboard, admin launch control, and scheduled auto-activation.

CRITICAL PRINCIPLE:
The server (middleware + scheduler) decides PRE_LAUNCH vs LIVE. Alpine.js only DISPLAYS
the countdown. Changing the browser clock must never unlock the site.

FILES TO INSPECT:
- bootstrap/app.php
- routes/web.php, routes/admin.php, routes/console.php
- app/Models/User.php (isAdmin helper)
- resources/views/layouts/public.blade.php
- database/seeders/ (existing seeder patterns)

FILES TO CREATE:
- database/migrations/xxxx_create_launch_settings_table.php
- database/seeders/LaunchSettingSeeder.php
- app/Models/LaunchSetting.php
- app/Http/Middleware/LaunchWall.php
- app/Http/Controllers/LaunchController.php
- app/Http/Controllers/Admin/LaunchControlController.php
- app/Console/Commands/ActivateLaunch.php
- resources/views/launch/wall.blade.php
- resources/views/launch/dashboard.blade.php
- resources/views/admin/launch/index.blade.php
- resources/views/components/launch/countdown.blade.php
- tests/Feature/Launch/LaunchSystemTest.php

FILES TO MODIFY:
- bootstrap/app.php (register middleware)
- routes/web.php
- routes/admin.php
- routes/console.php (schedule)

TASK 1: LAUNCH SETTINGS SCHEMA

Create migration: create_launch_settings_table (single-row control table).

Columns:
- id
- launch_at: datetime, nullable        (assembled from date + time + timezone)
- timezone: string, default 'Africa/Lagos'
- auto_activate: boolean, default true (flip to LIVE automatically at launch_at)
- registration_open: boolean, default true
- challenge_open: boolean, default true
- results_status: string, default 'locked'        (locked | available)
- answer_review_status: string, default 'locked'  (locked | available)
- leaderboard_status: string, default 'locked'    (locked | available)
- main_site_status: string, default 'locked'      (locked | open)
- timestamps

TASK 2: LAUNCH SETTING MODEL

Create app/Models/LaunchSetting.php.

- Fillable for all columns.
- Static current(): returns the single row (firstOrCreate with defaults).
- isPreLaunch(): main_site_status === 'locked'.
- isLive(): !isPreLaunch().
- resultsAvailable(), answerReviewAvailable(), leaderboardAvailable() helpers.
- launchAtCarbon(): returns Carbon in the configured timezone.
- remainingSeconds(): max(0, launchAt - now) for the countdown payload.

TASK 3: LAUNCH WALL MIDDLEWARE

Create app/Http/Middleware/LaunchWall.php.

Logic:
- $settings = LaunchSetting::current();
- If NOT pre-launch → pass through.
- If pre-launch:
  - ALWAYS allow: assets, livewire/*, _debugbar, storage.
  - ALWAYS allow super admin (auth()->user()?->isAdmin()).
  - Allow these paths: '/', 'login', 'register', 'logout', 'password/*', 'auth/google/*',
    'launch-dashboard', 'privacy', 'terms', 'disclaimer', 'contact'.
  - If registration_open is false, block 'register' (redirect to '/' with message).
  - Everything else → redirect('/').

Register in bootstrap/app.php web group (APPEND so it runs after session/auth):
$middleware->web(append: [\App\Http\Middleware\LaunchWall::class]);

TASK 4: LAUNCH CONTROLLER + ROUTES

Create app/Http/Controllers/LaunchController.php.

- wall(): if live → redirect to normal home; else render launch/wall with settings.
- dashboard(): auth required; render launch/dashboard with settings + user.

routes/web.php:
- GET / → LaunchController@wall (name: launch.wall)   [replaces/aheads home route]
- GET /launch-dashboard → middleware auth → LaunchController@dashboard (name: launch.dashboard)

NOTE: When live, '/' should render the NORMAL home page. Implement in wall():
  if ($settings->isLive()) return app(HomeController)->index() or view normal home.

TASK 5: COUNTDOWN COMPONENT

Create resources/views/components/launch/countdown.blade.php.

Props: $target (Carbon), $size ('lg' | 'md').

- Render server-provided target ISO timestamp + server now timestamp into data attributes.
- Alpine computes remaining = target - serverNow, then ticks locally each second.
- Display DD : HH : MM : SS in styled boxes (Abyss Blue theme, dark friendly).
- When remaining hits 0, show "We are live!" state.
- Accessible: aria-live polite off (don't announce every second), labels on units.

TASK 6: LAUNCH WALL PAGE

Create resources/views/launch/wall.blade.php.

Standalone page (own minimal layout, Abyss Blue gradient):
- LabGOTO logo.
- Headline: "LABGOTO IS COMING"
- Sub: "A new digital platform for medical laboratory science."
- <x-launch.countdown :target="$settings->launchAtCarbon()" size="lg" />
- Buttons: [Create Account] → register, [Login] → login.
- If registration closed: hide Create Account, show "Registration opens soon."
- Footer: © LabGOTO.

TASK 7: LAUNCH DASHBOARD

Create resources/views/launch/dashboard.blade.php (uses public layout).

- "Welcome to LabGOTO, {name}"
- "You are registered for the LabGOTO launch."
- <x-launch.countdown size="md" />
- ASCPI Challenge card:
  - Status: ✓ Registered
  - Result: 🔒 Locked
  - "Your challenge result will be revealed when LabGOTO officially launches."
- If challenge_open false: show "Challenge opens soon."

TASK 8: SUPER ADMIN LAUNCH CONTROL

Create app/Http/Controllers/Admin/LaunchControlController.php.
- index(): show settings.
- update(): validate + save. Permission: super admin only.

Validation:
- launch_date: required, date
- launch_time: required
- timezone: required, timezone
- booleans for auto_activate, registration_open, challenge_open
- in:locked,available for results/answer_review/leaderboard
- in:locked,open for main_site

On save: assemble launch_at from date+time+timezone.

Create resources/views/admin/launch/index.blade.php:
- "LABGOTO LAUNCH CONTROL" header with current computed status badge (PRE-LAUNCH / LIVE).
- Form: launch date, launch time, timezone select, auto-activate toggle.
- Toggle grid: Registration, Challenge, Results, Answer Review, Leaderboard, Main Website.
- Live preview of countdown target.
- Save button + success flash.
- Danger zone note: "Unlocking Main Website makes the full platform public immediately."

Add sidebar item "Launch Control" (super admin only).

routes/admin.php:
- GET /admin/launch → index (name admin.launch)
- PUT /admin/launch → update

TASK 9: SCHEDULED AUTO-ACTIVATION

Create app/Console/Commands/ActivateLaunch.php (signature: launch:check).

Logic (every minute):
- $s = LaunchSetting::current();
- If $s->auto_activate && $s->launch_at && now >= launch_at && pre-launch:
  - main_site_status = 'open'
  - (Leave results/leaderboard as configured; admin reveals them separately or now)
  - save; log 'Launch activated automatically'.

routes/console.php: Schedule::command('launch:check')->everyMinute();

TASK 10: SEEDER

Create LaunchSettingSeeder: firstOrCreate default row:
- launch_at: now +30 days 20:00 Africa/Lagos
- auto_activate true, registration_open true, challenge_open true
- results/answer_review/leaderboard locked, main_site locked.

Add to DatabaseSeeder.

TASK 11: TESTS

Create tests/Feature/Launch/LaunchSystemTest.php:
- Test pre-launch: guest visiting /calculators redirects to '/'.
- Test pre-launch: guest sees launch wall at '/'.
- Test pre-launch: login/register accessible.
- Test pre-launch: authenticated user sees launch dashboard.
- Test pre-launch: super admin can reach /calculators and /admin/launch.
- Test registration_open=false blocks register.
- Test live: '/' shows normal home; /calculators accessible to guest.
- Test launch:check flips status when launch_at passed.
- Test admin can update launch settings.

DEFINITION OF DONE:
- Launch settings stored and server-authoritative.
- Launch wall gates the whole site pre-launch (admin bypass).
- Countdown displays server-provided target; browser clock can't unlock site.
- Launch dashboard shows registration + locked result state.
- Super Admin Launch Control edits every toggle without code changes.
- Scheduler auto-activates launch at launch_at.
- All tests pass.