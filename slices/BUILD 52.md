labgato/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Admin/
│   │   │       ├── QuestionController.php
│   │   │       ├── QuestionImportController.php
│   │   │       └── QuestionCategoryController.php
│   │   └── Requests/
│   │       └── Admin/
│   │           ├── StoreQuestionRequest.php
│   │           ├── UpdateQuestionRequest.php
│   │           └── ImportQuestionsRequest.php
│   └── Services/
│       └── QuestionImportService.php
├── resources/
│   └── views/
│       └── admin/
│           └── questions/
│               ├── index.blade.php
│               ├── create.blade.php
│               ├── edit.blade.php
│               ├── import.blade.php
│               ├── categories.blade.php
│               └── partials/
│                   └── question-form.blade.php
├── routes/
│   └── admin.php
├── database/
│   └── seeders/
│       └── RolePermissionSeeder.php
└── tests/
    └── Feature/
        └── Admin/
            ├── QuestionCrudTest.php
            └── QuestionImportTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD52-QUESTION-MANAGEMENT

CONTEXT:
The question bank is the heart of the assessment engine. Super admins need a professional
interface to create, edit, filter, bulk-manage, and import questions (with images and
explanations), and to archive rather than delete questions already used in examinations.

OBJECTIVE:
Build full question CRUD with a dynamic options editor and image upload, a CSV importer
with template + validation preview, subject management, permissions, and tests.

EXISTING ARCHITECTURE:
- Models: Question, QuestionOption, QuestionCategory, Assessment, AssessmentBlueprint.
- Question::TYPES = [text, image, text_image, calculation, case]
- Question::DIFFICULTIES = [easy, intermediate, advanced]
- Question::STATUSES = [draft, active, archived]
- Admin layout + sidebar + role/permission system.

FILES TO INSPECT:
- app/Models/Question.php, QuestionOption.php, QuestionCategory.php
- resources/views/admin/ (existing CRUD pages for patterns)
- routes/admin.php
- database/seeders/RolePermissionSeeder.php

FILES TO CREATE: (all listed in target paths)

──────────────────────────────────────────────
TASK 1: PERMISSIONS + NAVIGATION
──────────────────────────────────────────────

Modify RolePermissionSeeder:
- Add permissions: questions.view, questions.manage, questions.import, categories.manage
- Assign all to super_admin; questions.view + questions.manage to content/admin roles.

Modify admin sidebar:
- Add "Assessments" group containing: Launch Control, Questions, Import Questions,
  Subjects. (Assessments list/exam engine come later.)

──────────────────────────────────────────────
TASK 2: QUESTION LIST (INDEX) WITH FILTERS + BULK
──────────────────────────────────────────────

Route: GET /admin/questions (permission questions.view)

QuestionController@index:
- Query Question with category, options (count), eager loaded.
- Filters via query string:
  ?search=        → stem LIKE
  ?category=      → question_category_id
  ?difficulty=
  ?type=
  ?status=        → default show all; quick tabs [All|Active|Draft|Archived]
- Paginate 25, with filters appended to links.
- Stats cards at top: Total, Active, Draft, Archived, and per-subject counts (small chips).

index.blade.php:
- Stats card row.
- Filter bar: search input, category select, difficulty select, type select, status tabs,
  [Filter] + [Clear] buttons.
- Table: # | Question (truncated stem + image thumb if any) | Subject | Type |
  Difficulty (colored badge) | Status (badge) | Options count | Updated | Actions.
- Row actions: Edit, Preview, Archive/Restore, Delete.
- Checkbox column + bulk bar: [Archive selected] [Activate selected] [Delete selected].
- Buttons: [+ New Question] [Import CSV] [Subjects].

Bulk endpoints:
POST /admin/questions/bulk  {action: archive|activate|delete, ids: []}
- delete uses the same hard-delete guard as single delete.

──────────────────────────────────────────────
TASK 3: QUESTION CREATE / EDIT FORM
──────────────────────────────────────────────

Routes:
GET/POST /admin/questions/create|store
GET/PUT  /admin/questions/{question}/edit|update
(permissions questions.manage)

Shared partial: partials/question-form.blade.php

Fields:
- Subject (category) select [required]
- Question type select [required] (TYPES)
- Difficulty select [required]
- Status select [required]
- Stem textarea [required] (label changes to "Case scenario" when type=case)
- Image upload (shown/required when type in [image, text_image]):
  - file input accept .jpg,.jpeg,.png,.webp, max 5MB
  - live preview (existing image on edit + new selection via FileReader)
  - "Remove image" checkbox on edit
- Explanation textarea [optional]
- OPTIONS EDITOR (Alpine.js):
  - Repeatable rows: [option text input] + [radio "correct"] + [remove btn]
  - [+ Add option] (min 2, max 6)
  - Exactly one radio must be selected.
  - Sends: options[] (array of labels) + correct_index (integer).
- Save button + Cancel.

Alpine options editor sketch:
<div x-data="{ options: @js($options), correct: @js($correctIndex),
               add(){ if(this.options.length<6) this.options.push('') },
               remove(i){ if(this.options.length>2){ this.options.splice(i,1);
                          if(this.correct>=this.options.length) this.correct=0 } } }">
  <template x-for="(opt,i) in options" :key="i"> ... inputs named options[] ...
     radio :checked="correct===i" @change="correct=i" ...
  </template>
  <input type="hidden" name="correct_index" :value="correct">
</div>

StoreQuestionRequest / UpdateQuestionRequest validation:
- question_category_id: required, exists
- type: required, in TYPES
- difficulty: required, in DIFFICULTIES
- status: required, in STATUSES
- stem: required, string, max 5000
- explanation: nullable, string, max 5000
- image: nullable|required_if:type,image,text_image, image mimes jpg,png,webp max 5120
- options: required, array, min 2, max 6
- options.*: required, string, max 1000
- correct_index: required, integer, between 0 and (count(options)-1)

Store/Update logic:
- Create/update question.
- Store image to public disk: questions/{question->id}/{filename}; delete old on replace.
- Sync options: delete existing options, re-insert in order with position=i and
  is_correct = (i == correct_index).

──────────────────────────────────────────────
TASK 4: PREVIEW, ARCHIVE, DELETE GUARD
──────────────────────────────────────────────

- GET /admin/questions/{question} → preview modal/page rendering the question exactly as a
  candidate would see it (stem, image, options A–D, explanation hidden behind toggle).

- POST /admin/questions/{question}/archive   → status=archived
- POST /admin/questions/{question}/restore   → status=active

- DELETE /admin/questions/{question}:
  Guard: if $question->usedInExams() → 422 with message "This question has been used in an
  examination. Archive it instead." Else hard delete (cascade options + image).

Add Question::usedInExams(): bool
- If Schema::hasTable('exam_answers'): return exam_answers where question_id exists.
- Else false. (Future-proof for Build 53.)

──────────────────────────────────────────────
TASK 5: CSV IMPORT (TEMPLATE + PREVIEW + IMPORT)
──────────────────────────────────────────────

Routes (permission questions.import):
GET  /admin/questions/import            → upload form
GET  /admin/questions/import/template   → downloads CSV template
POST /admin/questions/import/preview    → parse + validate, show preview
POST /admin/questions/import            → import confirmed rows

CSV TEMPLATE columns (header row):
category,type,difficulty,stem,option_a,option_b,option_c,option_d,correct_option,explanation

Provide 2 example rows in the template (one Hematology, one Chemistry).

QuestionImportService:
- parse($file): read CSV (handle BOM, quotes).
- validateRow($row, $lineNo): returns [valid, errors[], normalized[]]
  Rules:
  - category: required, must match an existing QuestionCategory name (case-insensitive)
  - type: in TYPES (default 'text' if empty)
  - difficulty: in DIFFICULTIES (default 'intermediate')
  - stem: required
  - options: at least 2 of option_a..d non-empty; collect non-empty in order
  - correct_option: required, one of A/B/C/D (case-insensitive) AND its letter must have a
    non-empty option; mapped to index.
  - explanation: optional
- preview(): returns rows with line number, valid flag, errors, and parsed preview.
- import($rows, $asStatus): create Question + options for valid rows only;
  status = $asStatus (draft|active), default draft. Return counts.

import.blade.php:
- Step 1: [Download template] + file upload + "Import as" select (Draft/Active) + [Preview].
- Step 2 (preview): table per row: Line | Status (✓/✗) | Errors | Category | Stem (trunc) |
  Correct. Summary: X valid, Y invalid.
- [Import N valid questions] button (posts only valid rows) + [Cancel].

ImportQuestionsRequest: file required, mimes csv,txt max 5120.

──────────────────────────────────────────────
TASK 6: SUBJECT (CATEGORY) MANAGEMENT
──────────────────────────────────────────────

Routes (permission categories.manage):
GET  /admin/questions/subjects
POST /admin/questions/subjects          (create)
PUT  /admin/questions/subjects/{id}     (update name/description/sort_order)
POST /admin/questions/subjects/{id}/toggle (active/inactive)

categories.blade.php:
- Table: Name | Slug | Questions count | Sort | Active | Edit
- Inline create form at top (name + sort_order).
- Edit via modal or inline row.
- Prevent deleting/toggling inactive a category that has active questions (warn).

──────────────────────────────────────────────
TASK 7: TESTS
──────────────────────────────────────────────

tests/Feature/Admin/QuestionCrudTest.php:
- Test index lists questions and filters by category/difficulty/status/search.
- Test store creates question + options with correct flag from correct_index.
- Test validation rejects: no correct option, two correct, <2 options, bad image type.
- Test image upload stores to public disk and edit replace deletes old file.
- Test archive sets status archived; restore sets active.
- Test delete blocked when usedInExams() true; allowed when false.
- Test bulk archive/activate work.
- Test permission: member cannot access admin questions.

tests/Feature/Admin/QuestionImportTest.php:
- Test template downloads with header + examples.
- Test preview flags invalid rows (bad category, missing stem, wrong correct letter).
- Test import creates only valid rows as draft by default.
- Test import as active sets status active.
- Test correct_option letter maps to the right option index.

DEFINITION OF DONE:
- Question list with filters, stats, and bulk actions.
- Create/edit with dynamic options editor + image upload/replace/preview.
- Archive/restore preferred; hard delete guarded for used questions.
- CSV template downloads; preview validates; import creates valid rows.
- Subject management works.
- Permissions enforced; sidebar updated.
- All tests pass.