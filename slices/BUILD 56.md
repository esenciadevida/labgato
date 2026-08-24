labgato/
├── app/
│   ├── Services/
│   │   └── ResultsService.php
│   └── Http/
│       └── Controllers/
│           └── Assessments/
│               └── ResultController.php
├── resources/
│   └── views/
│       └── assessments/
│           ├── my-results.blade.php
│           ├── result.blade.php
│           └── partials/
│               └── question-review.blade.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── Assessments/
            └── ResultsTest.phplabgato/
├── app/
│   ├── Services/
│   │   └── ResultsService.php
│   └── Http/
│       └── Controllers/
│           └── Assessments/
│               └── ResultController.php
├── resources/
│   └── views/
│       └── assessments/
│           ├── my-results.blade.php
│           ├── result.blade.php
│           └── partials/
│               └── question-review.blade.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── Assessments/
            └── ResultsTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD56-RESULTS-SYSTEM

CONTEXT:
Results must stay LOCKED until launch (prevents early answer-sharing), then be REVEALED
with score, rank, percentile, per-subject performance, and a full question review with
explanations and images. The reveal is controlled by the backend (launch setting and/or
assessment flag), never by the client.

OBJECTIVE:
Build a ResultsService plus My Results and Result detail pages with lock/reveal logic,
rank/percentile, subject breakdown, and question review.

EXISTING:
- ExamAttempt (score, percent, question_ids, option_orders, integrity_status).
- ExamAnswer (question_option_id, is_correct, flagged).
- LaunchService::settings()->results_released; Assessment->results_released.
- Question + QuestionOption + QuestionCategory.

FILES TO INSPECT:
- app/Models/ExamAttempt.php, ExamAnswer.php
- app/Services/LaunchService.php
- routes/web.php
- resources/views/assessments/submitted.blade.php (has "View My Result" link)

FILES TO CREATE: (target paths)

──────────────────────────────────────────────
TASK 1: RESULTS SERVICE
──────────────────────────────────────────────

Create app/Services/ResultsService.php.

Methods:

1. isReleased(Assessment $a): bool
   → LaunchService::settings()->results_released OR $a->results_released.

2. officialAttempts(Assessment $a): query of attempts mode=official,
   status in [submitted, auto_submitted].

3. rank(ExamAttempt $t): int
   → 1 + count(official attempts same assessment with score > $t->score).

4. topPercent(ExamAttempt $t): int
   → total = count(official attempts); if total==0 return 100;
     round((rank/total)*100).

5. summary(ExamAttempt $t): array
   → [score, total, percent, rank, topPercent, integrity_status].

6. subjectBreakdown(ExamAttempt $t): Collection
   → group the attempt's answers by question category:
     per category: name, correct, total, percent.
   → ordered by category sort_order/name.

7. review(ExamAttempt $t): array of items in frozen question order:
   per question: id, stem, image_url, type, subject, difficulty,
   your_option (label + display letter), correct_option (label + display letter),
   is_correct, flagged, explanation.
   Display letter = index+1 → A/B/C/D from option_orders[question_id].

──────────────────────────────────────────────
TASK 2: ROUTES + CONTROLLER
──────────────────────────────────────────────

Routes (auth):
GET /assessments/my-results            → myResults (name assessments.my-results)
GET /exam/{attempt}/result             → show (name exam.result)

ResultController:
- myResults: list auth user's attempts (assessment loaded), newest first, with status
  badges and per-row [View Result] (enabled only if released) else 🔒.
- show: authorize ownership; attempt must be submitted/auto_submitted.
  If !isReleased → render result page in LOCKED state (no score).
  Else render full reveal with summary, breakdown, review.

──────────────────────────────────────────────
TASK 3: MY RESULTS PAGE
──────────────────────────────────────────────

assessments/my-results.blade.php:
- Header "My Results".
- Table/card per attempt: Assessment title | Mode badge | Date | Status | Score (or 🔒) |
  [View].
- Empty state if none: "You haven't taken any assessments yet" + [Browse Assessments].

──────────────────────────────────────────────
TASK 4: RESULT DETAIL PAGE (LOCKED + REVEALED)
──────────────────────────────────────────────

assessments/result.blade.php:

LOCKED state (not released):
- Large 🔒 icon + "Your result is securely recorded."
- "Results will be revealed when LabGOTO officially launches."
- Show assessment title + submitted date only (NO score).

REVEALED state:
- Score hero card: big percent, score/total, rank #X, "Top Y%", integrity badge (subtle).
- Subject breakdown section:
  Table Subject | Correct | Total | Percent + horizontal progress bar per row
  (color: >=70 green, 40-69 amber, <40 red).
- Question review section:
  Loop partials/question-review per question.
- Actions: [Share My Result] (Build 58), [Back to My Results], [Leaderboard] (Build 57).

──────────────────────────────────────────────
TASK 5: QUESTION REVIEW PARTIAL
──────────────────────────────────────────────

partials/question-review.blade.php (receives $item, $index):
- Card header: "Question {index}" + subject + difficulty badges + ✓/✗ icon.
- Stem (line breaks), image if present (bordered frame).
- Two answer rows:
  "Your answer:  {letter}. {label}"  (green if correct, red if wrong)
  "Correct answer: {letter}. {label}" (green) — show only if wrong, else single row.
- Flagged indicator if flagged.
- Explanation in a collapsible <details> or Alpine accordion:
  "Explanation" + text.
- Options list (A–D in frozen order) with correct highlighted and user's selection marked.

──────────────────────────────────────────────
TASK 6: TESTS
──────────────────────────────────────────────

tests/Feature/Assessments/ResultsTest.php:
- Test result page shows LOCKED (no score) when not released.
- Test result page reveals score/percent when released.
- Test rank computed correctly (seed 3 attempts with known scores).
- Test topPercent computed correctly.
- Test subjectBreakdown correct/total per category.
- Test review shows your answer letter + correct letter + explanation.
- Test review shows image for image questions.
- Test my-results lists attempts; View disabled when locked.
- Test cannot view another user's result (403/404).
- Test unsubmitted attempt cannot view result.

DEFINITION OF DONE:
- ResultsService gates release + computes rank/percentile/breakdown/review.
- My Results lists attempts with lock state.
- Result page locked before release, full reveal after.
- Subject breakdown with bars; question review with explanations + images.
- Ownership + state guards enforced.
- All tests pass.