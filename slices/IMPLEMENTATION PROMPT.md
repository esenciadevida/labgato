labgato/
├── database/
│   ├── migrations/
│   │   └── xxxx_add_image_metadata_to_questions_table.php
│   ├── seeders/
│   │   └── QuestionJsonSeeder.php
│   └── data/
│       └── questions/
│           └── hematology_batch1.json
├── app/
│   ├── Models/
│   │   └── Question.php
│   ├── Http/
│   │   └── Controllers/
│   │       ├── Admin/
│   │       │   └── QuestionController.php
│   │       └── PublicSite/
│   │           └── AttributionsController.php
│   └── Services/
│       └── ImageDownloaderService.php
├── resources/
│   └── views/
│       ├── assessments/
│       │   ├── exam.blade.php
│       │   └── partials/
│       │       └── question-review.blade.php
│       ├── admin/
│       │   └── questions/
│       │       └── partials/
│       │           └── question-form.blade.php
│       └── public/
│           └── attributions.blade.php
├── routes/
│   ├── web.php
│   └── admin.php
└── tests/
    └── Feature/
        └── Questions/
            └── JsonSeedTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD60-QUESTION-JSON-SEED

CONTEXT:
LabGATO needs a scalable way to seed hundreds of questions with images from legal sources
(CDC PHIL public domain + Wikimedia Commons CC-licensed). The seeder reads JSON files,
downloads/links images, stores metadata, and displays attribution in the exam UI.

OBJECTIVE:
Build the JSON seeder pipeline, image metadata schema, attribution display, admin editing,
and a first 20-question seed batch.

EXISTING:
- Question model with image_path field.
- QuestionOption model.
- Admin question CRUD (Build 52).
- Exam UI (Build 54) and review UI (Build 56).

FILES TO INSPECT:
- app/Models/Question.php
- database/migrations/xxxx_create_questions_table.php
- resources/views/assessments/exam.blade.php
- resources/views/assessments/partials/question-review.blade.php
- resources/views/admin/questions/partials/question-form.blade.php

──────────────────────────────────────────────
TASK 1: SCHEMA — IMAGE METADATA
──────────────────────────────────────────────

Migration add_image_metadata_to_questions_table:
- image_source: string nullable     (e.g. "CDC PHIL", "Wikimedia Commons", "Original")
- image_license: string nullable    (e.g. "Public Domain", "CC BY-SA 4.0", "Original")
- image_attribution: text nullable  (full attribution text if CC required)
- image_url: string nullable        (original source URL for reference)

Question model:
- Add to fillable: image_source, image_license, image_attribution, image_url.
- Helper: requiresAttribution(): bool → str_contains(license, 'CC').
- Helper: attributionHtml(): string → renders as HTML link if URL present.

──────────────────────────────────────────────
TASK 2: JSON SEEDER
──────────────────────────────────────────────

Create database/seeders/QuestionJsonSeeder.php.

Signature: questions:seed {file?} (optional: specific file, else glob all in data/questions/).

Logic:
- For each JSON file (or the specified one):
  - Read + decode.
  - For each item:
    - Find/create category by name.
    - Create Question with all fields (stem, type, difficulty, image_path, explanation,
      image_source, image_license, image_attribution, image_url, status).
    - Create options with correct flag derived from "correct" key (A/B/C/D → position).
    - Output: "Created: {stem first 50 chars}..."
- Summary: "Imported X questions from Y files."

JSON format (per question):
{
  "category": "Hematology",
  "type": "image",
  "difficulty": "intermediate",
  "stem": "The peripheral smear shown demonstrates...",
  "image": "images/questions/hematology/target-cells-001.jpg",
  "image_source": "Wikimedia Commons",
  "image_license": "CC BY-SA 4.0",
  "image_attribution": "By User:Mikrograf, CC BY-SA 4.0",
  "image_url": "https://commons.wikimedia.org/wiki/File:Target_cells.jpg",
  "options": ["Spherocytes", "Target cells", "Schistocytes", "Stomatocytes"],
  "correct": "B",
  "explanation": "Target cells show a bull's-eye pattern...",
  "source_type": "original",
  "status": "draft"
}

──────────────────────────────────────────────
TASK 3: IMAGE DOWNLOADER SERVICE (OPTIONAL)
──────────────────────────────────────────────

Create app/Services/ImageDownloaderService.php.

Method: downloadAndStore(string $sourceUrl, string $destPath): bool
- Fetch image (file_get_contents or Http::get with timeout).
- Validate image type (JPEG/PNG).
- Resize to max 1200px wide (using Intervention Image if installed, else skip resize).
- Store to public disk at destPath.
- Return true/false.

Add command: php artisan questions:download-images
- Iterate questions with image_url but no local image_path.
- Download + store.
- Output: "Downloaded X images, failed Y."

This lets you:
1. Seed JSON with remote URLs.
2. Run the command to fetch + store locally.
3. Exam UI uses local copies (fast, reliable).

──────────────────────────────────────────────
TASK 4: ATTRIBUTION DISPLAY
──────────────────────────────────────────────

Modify exam.blade.php (question area):
- After image, if question requiresAttribution():
  <div class="text-xs text-slate-500 mt-2">
    Image: {{ $question->image_source }} · {{ $question->image_license }}
    @if($question->image_url)
      <a href="{{ $question->image_url }}" target="_blank" class="underline">Source</a>
    @endif
  </div>

Modify question-review.blade.php (Build 56):
- Same attribution line under image.

Create resources/views/public/attributions.blade.php:
- List all questions with image_license containing "CC".
- Group by source (Wikimedia Commons, CDC PHIL).
- Show: question stem (truncated) + image + full attribution + source link.
- Link from footer: "Image Attributions".

Add route: GET /attributions → AttributionsController@index.

──────────────────────────────────────────────
TASK 5: ADMIN QUESTION FORM — IMAGE METADATA
──────────────────────────────────────────────

Modify admin/questions/partials/question-form.blade.php:
- After image upload, add fields:
  - Image Source (text input, placeholder: "CDC PHIL" or "Wikimedia Commons" or "Original")
  - Image License (select: "Public Domain", "CC BY 4.0", "CC BY-SA 4.0", "CC BY-NC 4.0", "Original")
  - Image Attribution (textarea, shown when license != Public Domain and != Original)
  - Image URL (text input, optional, for reference)
- Conditional display: attribution textarea shows when CC license selected.

Update StoreQuestionRequest + UpdateQuestionRequest:
- Add validation: image_source nullable string max 255, image_license nullable in list,
  image_attribution nullable string max 1000, image_url nullable url.

Update QuestionController store/update:
- Save new fields.

──────────────────────────────────────────────
TASK 6: FIRST 20-QUESTION SEED BATCH
──────────────────────────────────────────────

Create database/data/questions/hematology_batch1.json with 20 questions:
- 5 text questions (no image).
- 10 image questions (mix of CDC PHIL + Wikimedia Commons).
- 5 calculation/case questions.
- All with correct answers, explanations, source metadata.
- All status: "draft" (admin reviews before activating).

Example entries (5 shown here, full 20 in separate file):

[
  {
    "category": "Hematology",
    "type": "image",
    "difficulty": "intermediate",
    "stem": "The peripheral smear demonstrates red cells with a bull's-eye appearance. Which morphology is shown?",
    "image": "images/questions/hematology/target-cells-001.jpg",
    "image_source": "Wikimedia Commons",
    "image_license": "CC BY-SA 4.0",
    "image_attribution": "By User:Mikrograf, CC BY-SA 4.0",
    "image_url": "https://commons.wikimedia.org/wiki/File:Target_cells_(codocytes)_-_peripheral_blood_smear.jpg",
    "options": ["Spherocytes", "Target cells", "Schistocytes", "Stomatocytes"],
    "correct": "B",
    "explanation": "Target cells (codocytes) show a bull's-eye pattern from increased surface-to-volume ratio.",
    "source_type": "original",
    "status": "draft"
  },
  {
    "category": "Microbiology",
    "type": "image",
    "difficulty": "intermediate",
    "stem": "The Gram stain demonstrates Gram-positive cocci in clusters. Which organism is most likely?",
    "image": "images/questions/microbiology/gram-staph-001.jpg",
    "image_source": "CDC PHIL",
    "image_license": "Public Domain",
    "image_attribution": null,
    "image_url": "https://phil.cdc.gov/Details.aspx?pid=23366",
    "options": ["Streptococcus pneumoniae", "Staphylococcus aureus", "Neisseria meningitidis", "Enterococcus faecalis"],
    "correct": "B",
    "explanation": "Gram-positive cocci in clusters are characteristic of Staphylococcus.",
    "source_type": "original",
    "status": "draft"
  },
  ... (18 more)
]

──────────────────────────────────────────────
TASK 7: TESTS
──────────────────────────────────────────────

tests/Feature/Questions/JsonSeedTest.php:
- Test seeder reads JSON and creates questions + options.
- Test correct option flagged based on "correct" key.
- Test image metadata saved (source, license, attribution).
- Test invalid JSON structure throws exception.
- Test duplicate category handled (firstOrCreate).
- Test requiresAttribution() returns true for CC licenses.
- Test attributions page lists CC-licensed questions.
- Test admin form saves image metadata.

DEFINITION OF DONE:
- JSON seeder imports questions with image metadata.
- Image downloader fetches remote URLs to local storage.
- Exam + review UI show attribution for CC-licensed images.
- Public attributions page lists all CC credits.
- Admin form edits image source/license/attribution.
- First 20-question batch ready to seed.
- All tests pass.