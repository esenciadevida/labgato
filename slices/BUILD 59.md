labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   └── AssessmentController.php
│   │   │   └── Assessments/
│   │   │       ├── AssessmentHubController.php
│   │   │       └── ExamController.php        (accept practice mode)
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StoreAssessmentRequest.php
│   │           └── UpdateAssessmentRequest.php
│   └── Services/
│       └── ResultsReleaseNotifier.php
├── resources/
│   └── views/
│       ├── admin/
│       │   └── assessments/
│       │       ├── index.blade.php
│       │       └── form.blade.php
│       ├── assessments/
│       │   ├── index.blade.php               (public hub)
│       │   └── show.blade.php                (add practice button)
│       └── public/
│           └── home.blade.php                (assessments section)
├── routes/
│   ├── web.php
│   └── admin.php
├── database/
│   └── seeders/
│       └── RolePermissionSeeder.php
└── tests/
    └── Feature/
        └── Assessments/
            └── LaunchIntegrationTest.php


IMPLEMENTATION PROMPT ID: LABGATO-BUILD59-LAUNCH-INTEGRATION

CONTEXT:
The assessment engine is complete. This final build integrates it into the live platform:
the Super Admin manages assessments (the monthly cycle) with blueprints; the public gets an
Assessments hub, navigation, and practice mode; on launch day the site unlocks and
participants are notified when results go live. The ASCPI Challenge becomes a permanent
part of LabGOTO.

OBJECTIVE:
Build admin assessment CRUD + blueprint editor, public hub + nav + home section, practice
mode entry, results-release notifications, and full-journey tests.

EXISTING:
- Assessment + AssessmentBlueprint models (Build 51).
- Exam engine accepts mode official|practice (Build 53) — start endpoint must accept mode.
- Leaderboard per assessment (Build 57); Results (56); Certificates (58).
- LaunchService::isLive(); notifications system; admin layout + sidebar.

FILES TO INSPECT:
- app/Models/Assessment.php, AssessmentBlueprint.php
- app/Http/Controllers/Assessments/ExamController.php
- app/Http/Controllers/Admin/LaunchControlController.php
- app/Console/Commands/ActivateLaunch.php
- routes/web.php, routes/admin.php
- public nav component + homepage view

──────────────────────────────────────────────
TASK 1: PERMISSIONS + ADMIN NAV
──────────────────────────────────────────────

RolePermissionSeeder: add assessments.manage (super_admin + admin).
Admin sidebar "Assessments" group: Launch Control, Assessments, Questions,
Import Questions, Subjects, Integrity, Certificates.

──────────────────────────────────────────────
TASK 2: ADMIN ASSESSMENT CRUD + BLUEPRINT EDITOR
──────────────────────────────────────────────

Routes (admin, assessments.manage):
GET  /admin/assessments                     index
GET/POST create/store
GET/PUT {assessment}/edit/update
POST {assessment}/status                    {status: open|closed|archived|scheduled}

AssessmentController:
- index: list with stats per assessment: participants (official attempts),
  completed, average percent, status badge, window dates. [Edit] [Open/Close] [Blueprint].
- form (create/edit partial):
  - title, slug (auto from title, editable), description, type select,
    duration_minutes, total_questions, start_at, end_at (datetime-local),
    pass_mark_percent, toggles: one_attempt_only, practice_mode_enabled,
    shuffle_questions, shuffle_options, fullscreen_required, certificate_enabled,
    results_released, answer_review_released, leaderboard_visible.
  - BLUEPRINT EDITOR (Alpine): one row per active QuestionCategory:
    [include checkbox] [question_count] [easy%] [intermediate%] [advanced%]
    live row-sum check (must equal 100) and grand-total check (sum of counts must equal
    total_questions). Show running totals with ✓/✗.
- store/update: validate; sync blueprints (delete+insert included rows).

Store/UpdateAssessmentRequest:
- title required max 150; slug unique; type in Assessment::TYPES;
  status in Assessment::STATUSES; duration 10..600; total_questions 1..500;
  pass_mark 0..100 nullable; blueprints array; per-row percents sum 100;
  sum of included counts == total_questions.

──────────────────────────────────────────────
TASK 3: PUBLIC ASSESSMENTS HUB
──────────────────────────────────────────────

Route: GET /assessments → assessments.index (auth optional; taking requires auth).

AssessmentHubController@index:
- Only assessments with status != draft (scheduled/open/closed/archived).
- For authed user, attach per-assessment state:
  hasOfficialAttempt, attemptStatus, practiceEnabled.
- Order: open first, then scheduled (soonest), then closed (newest).

assessments/index.blade.php:
- Hero: "Assessments" + intro line.
- Card per assessment:
  - title + type badge + subject chips (blueprint categories) + duration + question count.
  - OPEN: [Take Challenge] → assessments.show (intro). If in_progress → [Resume].
    If submitted → [View Result] / [Leaderboard].
    If practice allowed & official done → [Start Practice Run].
  - SCHEDULED: countdown to start_at + [Remind me] (optional notify bell).
  - CLOSED: [My Result] [Leaderboard] + "Next challenge coming soon".
- Side card: "How it works" (randomized, one attempt, results at launch, certificate).

──────────────────────────────────────────────
TASK 4: PRACTICE MODE ENTRY
──────────────────────────────────────────────

Modify ExamController@start: accept request mode (official|practice, default official);
pass to eligibility + generation (already supported).

Modify assessments/show.blade.php (intro):
- If isLive AND practice_mode_enabled AND user has submitted official attempt:
  show secondary button [Start Practice Run] → POST start with mode=practice.
- Practice banner on exam page header when mode=practice: "PRACTICE — not ranked".

Practice attempts excluded from leaderboard + certificates (already: official-only).

──────────────────────────────────────────────
TASK 5: NAVIGATION + HOMEPAGE SECTION
──────────────────────────────────────────────

Public nav: add "Assessments" dropdown (visible when isLive):
- Assessments Hub (/assessments)
- My Results (/assessments/my-results)
- Leaderboard (/leaderboard)
- My Certificates (/assessments/my-certificates)

Homepage (public home view): after launch, add "Assessments" section:
- Flagship card: latest open challenge (title, countdown or CTA).
- Secondary: Leaderboard + Practice cards.
- Hidden during pre_launch (launch wall already gates, but guard anyway).

──────────────────────────────────────────────
TASK 6: RESULTS-RELEASE NOTIFICATIONS
──────────────────────────────────────────────

Create app/Services/ResultsReleaseNotifier.php:
- notifyReleased(Assessment $a): for each user with an official submitted attempt,
  create in-app notification: "🎉 Your {title} result is live! View your score, rank and
  certificate." link exam.result. Use existing notifications table/system. Deduplicate
  (skip if same notification exists).

Hook points:
- LaunchControlController@update: when results_released transitions false→true →
  notifyReleased(assessment(s) affected) — for launch challenge use the launch assessment.
- ActivateLaunch command: when auto-release fires → notifyReleased for assessments with
  results_released now true.

──────────────────────────────────────────────
TASK 7: FULL-JOURNEY + INTEGRATION TESTS
──────────────────────────────────────────────

tests/Feature/Assessments/LaunchIntegrationTest.php:
- Test admin creates assessment with blueprint; validation rejects count/percent mismatch.
- Test status transitions (scheduled→open→closed) via endpoint.
- Test hub lists open first, scheduled with countdown data, closed with links.
- Test nav contains Assessments links when live; absent pre_launch.
- Test homepage shows assessments section only when live.
- Test practice run allowed post-launch after official attempt; blocked pre-launch.
- Test practice attempt excluded from leaderboard.
- Test results release creates in-app notifications for participants (once only).
- FULL JOURNEY test: register → complete profile → open assessment → start official →
  answer all → submit → locked → release results → view result + review → leaderboard
  shows user → certificate issued → verify page valid → share page 200.

DEFINITION OF DONE:
- Admin can run the monthly cycle: create assessment + blueprint, open/close it.
- Public hub presents open/scheduled/closed assessments with correct CTAs.
- Practice mode available post-launch, unranked.
- Nav + homepage surface the assessment ecosystem after launch.
- Participants notified when results go live.
- Full journey passes end-to-end.
- All tests pass.