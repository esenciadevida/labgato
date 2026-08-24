labgato/
├── app/
│   ├── Services/
│   │   └── LeaderboardService.php
│   └── Http/
│       └── Controllers/
│           └── PublicSite/
│               └── LeaderboardController.php
├── resources/
│   └── views/
│       └── public/
│           └── leaderboard/
│               ├── index.blade.php
│               ├── show.blade.php
│               └── partials/
│                   └── board-table.blade.php
├── routes/
│   └── web.php
├── app/
│   └── Services/
│       └── ExamScoringService.php   (cache invalidation hook)
└── tests/
    └── Feature/
        └── Assessments/
            └── LeaderboardTest.php


IMPLEMENTATION PROMPT ID: LABGATO-BUILD57-LEADERBOARD

CONTEXT:
The leaderboard ranks participants per assessment (the monthly challenge cycle) and offers
an all-time aggregate board. Ranking is score-first with completion time as the secondary
tiebreaker. Each user sees their own position without scrolling. Users who opted out of
leaderboard display appear as "Anonymous". The board is locked until the admin reveals it.

OBJECTIVE:
Build a cached LeaderboardService, per-assessment + all-time boards, personal position
card with jump shortcut, privacy-aware names, locking with admin bypass, and nav entry.

EXISTING:
- ExamAttempt: score, percent, time_spent_seconds, submitted_at, mode, status.
- UserProfile: show_on_leaderboard boolean; User->profile.
- Assessment: leaderboard_visible; LaunchService::settings()->leaderboard_visible.

FILES TO INSPECT:
- app/Models/ExamAttempt.php
- app/Services/ExamScoringService.php
- app/Services/LaunchService.php
- routes/web.php
- resources/views/layouts/public.blade.php (nav)

FILES TO CREATE/MODIFY: (target paths)

──────────────────────────────────────────────
TASK 1: LEADERBOARD SERVICE (CACHED + RANKED)
──────────────────────────────────────────────

Create app/Services/LeaderboardService.php.

Ordering rule (single source of truth):
  score DESC, then time_spent_seconds ASC, then submitted_at ASC.
Rank = sequential position after that ordering (time breaks score ties).

Methods:

1. isBoardVisible(Assessment $a): bool
   → LaunchService::settings()->leaderboard_visible OR $a->leaderboard_visible.

2. board(Assessment $a): array  (cached 300s, key "leaderboard:{$a->id}")
   → Query official attempts, status in [submitted, auto_submitted],
     with user.profile, ordered by the rule.
   → Map to rows: [user_id, name, photo_url, score, percent, time_spent, rank, anonymous].
     name = profile && !show_on_leaderboard ? 'Anonymous' : (display_name ?? name).
     anonymous flag for styling.
   → Cache the full ranked array.

3. allTime(): array (cached 300s, key "leaderboard:alltime")
   → Across all assessments, per user: best percent per assessment, then
     score = round(avg of best percents), entries = count of assessments taken.
   → Order by score DESC, entries DESC. Rank sequential.
   → Rows: [user_id, name, photo_url, score(avg%), entries, rank, anonymous].

4. position(array $board, ?int $userId): ?int
   → rank of the user in the board, or null.

5. pageFor(int $position, int $perPage=50): int → intdiv(position-1, perPage)+1.

Cache invalidation:
- In ExamScoringService::score(), after saving: Cache::forget("leaderboard:{$assessment->id}")
  and Cache::forget("leaderboard:alltime").

──────────────────────────────────────────────
TASK 2: ROUTES + CONTROLLER
──────────────────────────────────────────────

Routes (web, no auth required to view but personal position needs auth):
GET /leaderboard                      → index (all-time + assessment selector)
GET /leaderboard/{assessment:slug}    → show (per-assessment)

LeaderboardController:
- index: gather assessments that have a visible board (or all for admin). Render
  all-time board + a selector/list of per-assessment boards.
- show(assessment): if !isBoardVisible and not admin → locked message.
  board = service->board; paginate 50 (slice the cached array).
  myPosition = service->position(board, auth id).
  Pass page, myPosition, jumpPage.

──────────────────────────────────────────────
TASK 3: PER-ASSESSMENT BOARD VIEW
──────────────────────────────────────────────

show.blade.php:
- Header: assessment title + "Leaderboard" + total participants.
- "My Position" card (if authed & participated): big #rank, score, percent,
  "Top X%". If not on current page, [Jump to my position] button → ?page={jumpPage}#me.
- Top-3 podium highlight (gold/silver/bronze accent on ranks 1-3).
- Table (partials/board-table): Rank | Participant (photo+name or Anonymous) | Score |
  Percent | Time (mm:ss).
  - Highlight the auth user's row (id="me", ring/background).
  - Anonymous rows muted with a lock icon.
- Pagination links preserving query.
- LOCKED state: 🔒 "Leaderboard will be revealed when LabGOTO launches."

──────────────────────────────────────────────
TASK 4: ALL-TIME BOARD + SELECTOR (INDEX)
──────────────────────────────────────────────

index.blade.php:
- Tabs/selector: [All-Time] + one chip per assessment with a visible board
  (e.g., "ASCPI Challenge 2026", "October 2026").
- All-Time table: Rank | Participant | Avg Best % | Assessments taken.
- Same My Position card + podium + privacy handling.
- Link each assessment chip to its board.

──────────────────────────────────────────────
TASK 5: NAVIGATION + LOCKING
──────────────────────────────────────────────

- Add "Leaderboard" to public nav under Assessments (show only when at least one board
  visible, or always link to index which handles locking).
- Admin bypass: super admin sees boards even when locked (for verification).

──────────────────────────────────────────────
TASK 6: TESTS
──────────────────────────────────────────────

tests/Feature/Assessments/LeaderboardTest.php:
- Test board ordered score DESC then time ASC (seed known attempts).
- Test equal score ordered by faster time.
- Test ranks sequential.
- Test user with show_on_leaderboard=false shows as "Anonymous".
- Test personal position returned correctly.
- Test locked board shows 🔒 to members but visible to super admin.
- Test all-time aggregates best-percent average + entries count.
- Test cache invalidation: scoring a new attempt updates the board (forget called).
- Test pagination pageFor math.
- Test index lists visible assessment boards.

DEFINITION OF DONE:
- LeaderboardService produces cached ranked boards with tiebreakers.
- Per-assessment (monthly) + all-time boards render with podium + privacy.
- My Position card + jump-to-page shortcut work.
- Locking enforced with admin bypass.
- Scoring invalidates leaderboard caches.
- Nav entry added.
- All tests pass.