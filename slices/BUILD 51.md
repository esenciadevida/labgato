labgato/
├── database/
│   ├── migrations/
│   │   ├── xxxx_create_question_categories_table.php
│   │   ├── xxxx_create_assessments_table.php
│   │   ├── xxxx_create_assessment_blueprints_table.php
│   │   ├── xxxx_create_questions_table.php
│   │   ├── xxxx_create_question_options_table.php
│   │   ├── xxxx_create_exam_attempts_table.php
│   │   ├── xxxx_create_exam_answers_table.php
│   │   ├── xxxx_create_integrity_events_table.php
│   │   └── xxxx_create_certificates_table.php
│   └── seeders/
│       ├── QuestionCategorySeeder.php
│       └── AssessmentSeeder.php
├── app/
│   └── Models/
│       ├── QuestionCategory.php
│       ├── Assessment.php
│       ├── AssessmentBlueprint.php
│       ├── Question.php
│       ├── QuestionOption.php
│       ├── ExamAttempt.php
│       ├── ExamAnswer.php
│       ├── IntegrityEvent.php
│       └── Certificate.php
└── tests/
    └── Feature/
        └── Assessments/
            └── AssessmentSchemaTest.php


IMPLEMENTATION PROMPT ID: LABGATO-BUILD51-ASSESSMENT-SCHEMA

CONTEXT:
LabGATO is building a generic Assessment Engine. The ASCPI Challenge is the first
assessment instance; the schema must also support future mock exams, practice tests,
daily questions, and monthly challenges. All assessment tables are created now in one
migration phase.

OBJECTIVE:
Create the complete assessment domain schema (9 tables), models with relationships and
helpers, and seeders for subjects + the first ASCPI Challenge assessment with blueprint.

PRINCIPLE:
Assessments are data, not code. Every behavior (duration, attempts, window, reveal
states) is a column on assessments — controlled by the Super Admin later.

FILES TO INSPECT:
- app/Models/User.php
- database/seeders/DatabaseSeeder.php
- database/migrations/ (naming conventions)

FILES TO CREATE:
- The 9 migrations listed above
- The 9 models listed above
- database/seeders/QuestionCategorySeeder.php
- database/seeders/AssessmentSeeder.php
- tests/Feature/Assessments/AssessmentSchemaTest.php

FILES TO MODIFY:
- app/Models/User.php (relationships)
- database/seeders/DatabaseSeeder.php (call new seeders)

TASK 1: MIGRATIONS

1) question_categories:
- id, name (unique), slug (unique), description nullable, sort_order int default 0,
  is_active bool default true, timestamps

2) assessments:
- id
- title, slug (unique), description text nullable
- type: string default 'challenge'  (challenge | mock | practice | daily)
- status: string default 'draft'    (draft | scheduled | open | closed | archived)
- duration_minutes int default 150
- total_questions int default 100
- opens_at datetime nullable, closes_at datetime nullable   (exam window / monthly cycle)
- max_attempts int default 1
- allow_practice bool default false      (post-launch unlimited practice mode)
- shuffle_questions bool default true
- shuffle_options bool default true
- fullscreen_required bool default true
- pass_percentage decimal(5,2) nullable  (certificate eligibility)
- certificate_enabled bool default true
- results_status string default 'locked'          (locked | available)
- answer_review_status string default 'locked'    (locked | available)
- leaderboard_status string default 'locked'      (locked | available)
- timestamps
- index on status, type

3) assessment_blueprints:
- id, assessment_id FK cascade, question_category_id FK cascade
- question_count int
- easy_percent int default 30
- intermediate_percent int default 50
- advanced_percent int default 20
- unique(assessment_id, question_category_id)

4) questions:
- id
- question_category_id FK cascade
- question_type string default 'text' (text | image | text_image | calculation | case)
- stem text
- case_text text nullable
- image_path string nullable, image_alt string nullable
- difficulty string default 'intermediate' (easy | intermediate | advanced)
- explanation text nullable
- reference string nullable
- status string default 'active' (active | inactive | archived)
- timestamps
- index on (question_category_id, difficulty, status)

5) question_options:
- id, question_id FK cascade
- label string (internal A-D)
- text text
- is_correct bool default false
- sort_order int default 0
- index on question_id

6) exam_attempts:
- id
- user_id FK cascade, assessment_id FK cascade
- attempt_number unsigned int default 1
- mode string default 'official' (official | practice)
- status string default 'in_progress' (in_progress | submitted | auto_submitted)
- question_ids json            (frozen set)
- option_orders json           (shuffled option ids per question)
- started_at datetime, ends_at datetime
- submitted_at datetime nullable
- score int nullable, percentage decimal(5,2) nullable
- time_spent_seconds int nullable
- integrity_status string default 'normal' (normal | review | high_risk)
- timestamps
- unique(user_id, assessment_id, attempt_number)
- index on (status, ends_at)   (for auto-submit scheduler)

7) exam_answers:
- id
- exam_attempt_id FK cascade, question_id FK cascade
- selected_option_id FK nullable nullOnDelete (question_options)
- is_correct bool nullable
- flagged bool default false
- answered_at datetime nullable
- timestamps
- unique(exam_attempt_id, question_id)

8) integrity_events:
- id
- exam_attempt_id FK cascade, user_id FK cascade
- event_type string (tab_switch | window_blur | fullscreen_exit | copy_attempt |
  paste_attempt | print_attempt | contextmenu | session_conflict | other)
- question_number int nullable
- metadata json nullable
- timestamps
- index on (exam_attempt_id, event_type)

9) certificates:
- id
- user_id FK cascade, exam_attempt_id FK cascade
- certificate_number string unique   (e.g. LG-ASCPI-2026-000123)
- verification_uuid uuid unique
- title string
- score int, percentage decimal(5,2)
- issued_at datetime
- revoked bool default false
- timestamps

TASK 2: MODELS

Create all 9 models with $fillable, casts (json arrays, booleans, dates, decimals),
and relationships:

- QuestionCategory: hasMany questions, hasMany blueprints. scopeActive.
- Assessment: hasMany blueprints, attempts; belongsToNothing.
  Helpers: isOpen() (status open + within window), isLockedResults(), etc.
  scopeOpen, scopeChallenges.
- AssessmentBlueprint: belongsTo assessment, category.
- Question: belongsTo category; hasMany options; hasOne correctOption
  (options where is_correct). scopeActive, scopeByDifficulty.
  Helper: correctOption().
- QuestionOption: belongsTo question.
- ExamAttempt: belongsTo user, assessment; hasMany answers, integrityEvents;
  hasOne certificate.
  Helpers: isExpired() (now > ends_at && in_progress), remainingSeconds(),
  isPractice().
- ExamAnswer: belongsTo attempt, question, selectedOption.
- IntegrityEvent: belongsTo attempt, user.
- Certificate: belongsTo user, attempt. scopeValid (not revoked).

Modify User.php:
- hasMany examAttempts, certificates.

TASK 3: SEEDERS

QuestionCategorySeeder: create the 7 subjects with sort_order:
Hematology, Clinical Chemistry, Microbiology, Immunology,
Blood Banking/Transfusion, Molecular Diagnostics, Other.

AssessmentSeeder: firstOrCreate the first assessment:
- title: "ASCPI Challenge 2026"
- slug: ascpi-challenge-2026
- type: challenge, status: scheduled
- duration_minutes: 150, total_questions: 100
- max_attempts: 1, allow_practice: true (post-launch)
- certificate_enabled: true, pass_percentage: 70
- results/answer_review/leaderboard: locked
Plus its blueprint rows:
  Hematology 20, Clinical Chemistry 20, Microbiology 20,
  Blood Banking 15, Immunology 15, Other 10 (all 30/50/20).

Register both in DatabaseSeeder.

TASK 4: TESTS

Create tests/Feature/Assessments/AssessmentSchemaTest.php:
- Test seeders create 7 categories and the ASCPI assessment with 6 blueprint rows.
- Test creating a question with 4 options and one correct; correctOption() works.
- Test attempt creation stores frozen question_ids json.
- Test unique constraint: same user+assessment+attempt_number cannot duplicate.
- Test exam_answers unique per attempt+question.
- Test assessment helpers (isOpen, isExpired on attempt).
- Test certificate model valid scope.

DEFINITION OF DONE:
- All 9 tables migrated with indexes and constraints.
- All 9 models created with relationships and helpers.
- Seeders create 7 subjects + ASCPI Challenge 2026 with blueprint.
- User has attempts/certificates relationships.
- All tests pass.