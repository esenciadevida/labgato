labgato/
├── app/
│   └── Http/
│       └── Controllers/
│           └── Assessments/
│               └── ExamController.php
├── resources/
│   └── views/
│       └── assessments/
│           ├── show.blade.php
│           ├── exam.blade.php
│           ├── submitted.blade.php
│           └── partials/
│               └── question-navigator.blade.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── Assessments/
            └── ExamInterfaceTest.php


IMPLEMENTATION PROMPT ID: LABGATO-BUILD54-EXAM-INTERFACE

CONTEXT:
The examination must look and feel like a professional CBT system. Candidates see a header
with a live countdown, a question area with image support, a question navigator showing
answered/flagged/unanswered states, prev/next/flag controls, auto-save, a submit
confirmation, and browser deterrents. The timer is server-seeded; at zero the exam
auto-submits. After submitting, results are locked until launch.

OBJECTIVE:
Build the intro page, CBT exam page, navigator, submit flow, submitted page, and
deterrents, wired to the Build 53 JSON endpoints.

EXISTING (Build 53):
- POST /assessments/{assessment}/start
- GET  /exam/{attempt}
- POST /exam/{attempt}/answers   {question_id, option_id}
- POST /exam/{attempt}/flag      {question_id}
- POST /exam/{attempt}/submit
- ExamAttempt helpers: remainingSeconds(), optionIdsFor(), isExpired().

FILES TO INSPECT:
- app/Http/Controllers/Assessments/ExamController.php
- app/Models/ExamAttempt.php, Question.php
- routes/web.php
- resources/views/layouts (for a minimal exam layout without global nav)

FILES TO CREATE/MODIFY: (target paths)

──────────────────────────────────────────────
TASK 1: ROUTES + CONTROLLER PAGES
──────────────────────────────────────────────

Routes (auth):
GET /assessments/{assessment}            → intro (name: assessments.show)
GET /exam/{attempt}/submitted            → submitted (name: exam.submitted)

ExamController:
- showIntro(assessment): render intro with rules, duration, total_questions, blueprint
  subject list. If user already has an official attempt → redirect appropriately
  (in_progress → exam.show; submitted → exam.submitted).
- show(attempt): [extend Build 53]
  - Authorize ownership.
  - If submitted/auto_submitted → redirect exam.submitted.
  - If expired & in_progress → score + redirect exam.submitted.
  - Build payload:
    questions = attempt.question_ids → load Question with options; order by frozen
    position; for each, options ordered by attempt.option_orders[qid]; assign display
    letters A..; include image url.
    answersMap = {question_id: selected_option_id}
    flagsMap = {question_id: bool}
    remainingSeconds (server).
  - Render exam.blade (minimal layout: no global nav/footer to avoid distraction).
- submitted(attempt): authorize; render submitted page. If results_released (launch
  setting + assessment) link to results (Build 56), else show locked message.

──────────────────────────────────────────────
TASK 2: INTRO / LANDING PAGE
──────────────────────────────────────────────

assessments/show.blade.php:
- Hero: assessment title + description + badges (type, duration, question count).
- "Examination Rules" card:
  - One attempt only (if one_attempt_only).
  - Timer is server-controlled; exam auto-submits at zero.
  - Fullscreen required; leaving fullscreen/tab is recorded.
  - No copy/paste/external aids.
- Subject distribution chips (from blueprints).
- Big [Begin Examination] button → POST start (with CSRF). If registration closed or not
  open, disable with message.

──────────────────────────────────────────────
TASK 3: CBT EXAM PAGE (CORE)
──────────────────────────────────────────────

assessments/exam.blade.php — minimal fullscreen layout.

Header (sticky):
- Left: LabGOTO logo + assessment title.
- Center: "Question X of N".
- Right: countdown box (DD? no — HH:MM:SS) that turns amber <10min, red <5min, pulses <1min.

Body grid: main question area (left, 3/4) + navigator sidebar (right, 1/4).

Question area:
- Type badge + subject + difficulty badge.
- Stem (render line breaks; if type=case, label "Case Scenario").
- Image (if image_path) in a bordered frame, max-h, object-contain.
- Options list: radio-style buttons labeled A–D by DISPLAY position (frozen order).
  Selected state = primary ring/fill. Clicking selects + auto-saves.
- Controls row: [← Previous] [Flag 🚩 toggle] [Next →].

Navigator sidebar (partials/question-navigator.blade.php):
- Grid of numbered tiles 1..N.
- Tile states: answered (primary/green), flagged (amber), unanswered (slate),
  current (ring-2).
- Legend below.
- Click tile jumps to that question.
- Summary counts: Answered X · Flagged Y · Unanswered Z.
- [Submit Examination] button at bottom.

Alpine component exam():
data: questions, answers, flags, current, remaining, submitting, confirmOpen.
methods:
- select(qid, oid): answers[qid]=oid; POST answers; (optimistic).
- toggleFlag(qid): flags[qid]=!; POST flag.
- goto(i), next(), prev().
- tick(): remaining--; if 0 → doSubmit(auto).
- doSubmit(): POST submit then location = submittedUrl.
- openConfirm(): set confirmOpen (shows modal).
init: restore current from localStorage key exam-{id}-idx; setInterval tick 1000;
  save current to localStorage on change.

CSRF: read from <meta name="csrf-token">. Use fetch with X-CSRF-TOKEN + Accept json.

Submit confirmation modal:
- "You have answered X of N. Y unanswered. Z flagged."
- Warn if unanswered>0.
- [Cancel] [Yes, Submit].

Auto-submit at zero: call doSubmit() and navigate.

beforeunload: if in_progress, e.preventDefault() (deterrent).

──────────────────────────────────────────────
TASK 4: BROWSER DETERRENTS (EXAM PAGE ONLY)
──────────────────────────────────────────────

On exam.blade.php add:
- CSS: .no-select { user-select:none; -webkit-user-select:none; } on body.
- JS: block contextmenu, copy, cut, paste, dragstart, drop (return false / preventDefault).
- Block key combos: Ctrl/Cmd+P, +S, +U, +C, +V, +X, +A, and F12.
- On Begin (from intro) and on exam load: request fullscreen (document.documentElement
  .requestFullscreen().catch(()=>{})).
These are deterrents only; real integrity logging is Build 55.

──────────────────────────────────────────────
TASK 5: SUBMITTED PAGE
──────────────────────────────────────────────

assessments/submitted.blade.php:
- Success icon + "Your examination has been submitted."
- "Your result is securely recorded."
- 🔒 "Results will be revealed when LabGOTO officially launches." (if not released)
- If released → [View My Result] link (Build 56 route guard).
- [Back to Dashboard] button.

──────────────────────────────────────────────
TASK 6: TESTS
──────────────────────────────────────────────

tests/Feature/Assessments/ExamInterfaceTest.php:
- Test intro shows rules + Begin for open assessment; disabled when closed.
- Test intro redirects to exam if in_progress attempt exists.
- Test exam page renders frozen question order + display letters A–D.
- Test exam page shows image for image questions.
- Test navigator counts (answered/flagged/unanswered) reflect saved answers.
- Test timer value equals server remainingSeconds.
- Test selecting an option persists (exam_answers updated via endpoint).
- Test submit redirects to submitted page; submitted shows locked message.
- Test a submitted attempt's exam page redirects to submitted.
- Test user cannot open another user's exam page (403/404).

DEFINITION OF DONE:
- Intro, CBT exam, and submitted pages render with Abyss Blue theme.
- Navigator shows answered/flagged/unanswered/current states and jumps.
- Auto-save + flag wired to endpoints; optimistic UI.
- Server-seeded countdown with color warnings; auto-submit at zero.
- Submit confirmation shows counts; submit navigates to submitted.
- Deterrents active on exam page only.
- Ownership + state guards enforced.
- All tests pass.