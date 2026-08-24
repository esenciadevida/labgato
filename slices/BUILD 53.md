labgato/
├── database/
│   └── migrations/
│       ├── xxxx_create_exam_attempts_table.php
│       └── xxxx_create_exam_answers_table.php
├── app/
│   ├── Models/
│   │   ├── ExamAttempt.php
│   │   └── ExamAnswer.php
│   ├── Services/
│   │   ├── ExamGenerationService.php
│   │   ├── ExamEligibilityService.php
│   │   └── ExamScoringService.php
│   ├── Console/
│   │   └── Commands/
│   │       └── AutoSubmitExams.php
│   └── Http/
│       └── Controllers/
│           └── Assessments/
│               └── ExamController.php
├── routes/
│   └── web.php
└── tests/
    └── Feature/
        └── Assessments/
            └── ExamEngineTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD53-EXAM-ENGINE

CONTEXT:
The exam engine must give every participant a unique but balanced examination (blueprint
driven), freeze it the moment they start (refresh/close/reconnect returns the SAME exam),
shuffle answer options while preserving the correct answer, enforce one official attempt,
and control the timer on the server so clock changes/refreshes never extend the exam.

OBJECTIVE:
Build the attempt/answer schema, generation + eligibility + scoring services, server timer
with auto-submit, and JSON endpoints for save/flag/submit. (The CBT UI is Build 54.)

EXISTING ARCHITECTURE:
- Models: Assessment, AssessmentBlueprint, Question, QuestionOption, QuestionCategory.
- Assessment fields: duration_minutes, total_questions, one_attempt_only,
  practice_mode_enabled, shuffle_questions, shuffle_options, status, start_at, end_at.
- Question scopes: active, byDifficulty, byCategory.

FILES TO INSPECT:
- app/Models/Assessment.php, Question.php, QuestionOption.php
- routes/web.php

FILES TO CREATE: (all in target paths)

──────────────────────────────────────────────
TASK 1: EXAM ATTEMPTS + ANSWERS SCHEMA
──────────────────────────────────────────────

Migration create_exam_attempts_table:
- id
- user_id: foreignId constrained cascadeOnDelete
- assessment_id: foreignId constrained cascadeOnDelete
- attempt_number: integer default 1
- mode: string default 'official'   (official | practice)
- status: string default 'in_progress'  (in_progress | submitted | auto_submitted | abandoned)
- question_ids: json                 (frozen ordered array of question IDs)
- option_orders: json                (frozen map: question_id => [option IDs in display order])
- started_at: datetime nullable
- ends_at: datetime nullable
- submitted_at: datetime nullable
- time_spent_seconds: integer nullable
- score: integer nullable
- percent: decimal(5,2) nullable
- integrity_status: string default 'normal'   (normal | review | high_risk)  [used in Build 55]
- timestamps
Indexes: (user_id, assessment_id), (status, ends_at).

Migration create_exam_answers_table:
- id
- exam_attempt_id: foreignId constrained cascadeOnDelete
- question_id: foreignId constrained cascadeOnDelete
- question_option_id: foreignId nullable constrained nullOnDelete
- is_correct: boolean nullable
- flagged: boolean default false
- answered_at: datetime nullable
- timestamps
Unique: (exam_attempt_id, question_id).

──────────────────────────────────────────────
TASK 2: MODELS
──────────────────────────────────────────────

ExamAttempt:
- belongsTo User, Assessment; hasMany ExamAnswer.
- casts: question_ids array, option_orders array, started_at/ends_at/submitted_at datetime,
  percent float, flagged n/a.
- helpers:
  - remainingSeconds(): int → max(0, ends_at?->diffInSeconds(now()))
  - isExpired(): bool → status in_progress AND ends_at AND now>=ends_at
  - isOfficial(), isPractice()
  - optionIdsFor($questionId): array → option_orders[$questionId] ?? []

ExamAnswer:
- belongsTo ExamAttempt, Question, QuestionOption.
- casts is_correct/flagged boolean.

──────────────────────────────────────────────
TASK 3: ELIGIBILITY SERVICE
──────────────────────────────────────────────

ExamEligibilityService:
- canStart(User, Assessment, string $mode='official'): array{ok:bool, reason:?string, redirect:?ExamAttempt}
  Rules:
  - assessment.status must be 'open' (or windowActive()).
  - If mode official AND one_attempt_only:
      existing official attempt → return ok=false with redirect to that attempt (frozen).
  - If mode practice: require practice_mode_enabled.
  - If exam_submission_open false (launch setting) → block.
- existingOfficialAttempt(User, Assessment): ?ExamAttempt.

──────────────────────────────────────────────
TASK 4: GENERATION SERVICE (BLUEPRINT + FREEZE)
──────────────────────────────────────────────

ExamGenerationService::startAttempt(User, Assessment, mode): ExamAttempt

1. If an in_progress or submitted official attempt exists and one_attempt_only → return it
   (do NOT regenerate).
2. Determine attempt_number = (max attempt_number for user+assessment)+1.
3. Build question set from blueprints:
   For each AssessmentBlueprint:
     easyN  = round(count * easy_percent/100)
     advN   = round(count * advanced_percent/100)
     interN = count - easyN - advN
     Select active questions in that category per difficulty using inRandomOrder()->limit().
     Backfill any shortfall from other difficulties in the same category, then category-wide.
   Merge all selected IDs.
4. If assessment.shuffle_questions → shuffle the merged array. (Else keep blueprint order.)
5. Build option_orders: for each question ID, load options ordered by position;
   if assessment.shuffle_options shuffle the option ID list; else keep position order.
   Store as { questionId: [optionIds] }.
6. Create ExamAttempt:
   started_at = now(); ends_at = now()->addMinutes(assessment.duration_minutes);
   question_ids, option_orders, mode, attempt_number, status in_progress.
7. Create one empty ExamAnswer per question (question_option_id null).
8. Return the attempt (wrapped in DB transaction).

Guarantee: the SAME attempt is returned on repeat calls (frozen).

──────────────────────────────────────────────
TASK 5: SCORING SERVICE
──────────────────────────────────────────────

ExamScoringService::score(ExamAttempt):
- For each answer with a selected option: is_correct = option->is_correct; save.
- score = count(is_correct true); percent = round(score/total*100, 2).
- time_spent_seconds = min(duration, submitted_at - started_at).
- status = submitted (or auto_submitted if called by scheduler), submitted_at = now().
- Save attempt. Return attempt.

──────────────────────────────────────────────
TASK 6: SERVER TIMER + AUTO-SUBMIT SCHEDULER
──────────────────────────────────────────────

Create app/Console/Commands/AutoSubmitExams.php (signature exam:autosubmit).
- Find ExamAttempt where status=in_progress AND ends_at <= now().
- For each: ExamScoringService::score with status auto_submitted.
- Log count.

Register in routes/console.php: Schedule::command('exam:autosubmit')->everyMinute();

──────────────────────────────────────────────
TASK 7: EXAM CONTROLLER + JSON ENDPOINTS
──────────────────────────────────────────────

Routes (auth):
POST /assessments/{assessment}/start            → start
GET  /exam/{attempt}                            → show (minimal page for now; UI in Build 54)
POST /exam/{attempt}/answers                    → save answer (JSON)
POST /exam/{attempt}/flag                       → toggle flag (JSON)
POST /exam/{attempt}/submit                     → submit (JSON)

ExamController:
- start: eligibility check; if redirect attempt → redirect to it; else startAttempt;
  redirect to exam.show.
- show: authorize attempt belongs to auth user; if expired and in_progress → auto-submit
  then show submitted state. Pass attempt + remainingSeconds (server) for Build 54.
- saveAnswer: validate question_id in attempt.question_ids; option_id nullable and in
  option_orders[question_id]; if expired → auto-submit + 422. Update exam_answers.
- flag: toggle flagged; return new state.
- submit: if already submitted → return existing; else score + return summary JSON.

All mutating endpoints re-check ownership (attempt.user_id == auth id) and expiry.

──────────────────────────────────────────────
TASK 8: TESTS
──────────────────────────────────────────────

Seed helper: create categories, assessment (open, duration 90, total from blueprint),
blueprints, and enough active questions per category/difficulty.

tests/Feature/Assessments/ExamEngineTest.php:
- Test start creates attempt with blueprint counts (assert question_ids length == total).
- Test difficulty distribution approx matches blueprint percentages.
- Test option_orders frozen and contains the correct option ID for each question.
- Test repeat start returns the SAME attempt id (frozen, not regenerated).
- Test second official attempt blocked when one_attempt_only.
- Test practice attempt allowed (attempt_number increments) when practice_mode_enabled.
- Test practice blocked when practice_mode_enabled false.
- Test save answer updates exam_answers.question_option_id.
- Test save answer rejects option not in the frozen option_orders.
- Test flag toggles true/false.
- Test submit computes score + percent correctly against known correct options.
- Test ends_at == started_at + duration_minutes (server timer).
- Test exam:autosubmit auto-submits expired in_progress attempts.
- Test a user cannot access/save on another user's attempt (403/404).

DEFINITION OF DONE:
- Attempts + answers tables migrated with constraints.
- Generation produces balanced, shuffled, frozen sets.
- One official attempt enforced; practice mode gated.
- Server timer authoritative; auto-submit scheduler works.
- Save/flag/submit endpoints secure and correct.
- Scoring accurate.
- All tests pass.