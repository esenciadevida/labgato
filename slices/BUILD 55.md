labgato/
├── database/
│   └── migrations/
│       ├── xxxx_create_integrity_events_table.php
│       └── xxxx_add_session_fingerprint_to_exam_attempts_table.php
├── app/
│   ├── Models/
│   │   └── IntegrityEvent.php
│   ├── Services/
│   │   └── IntegrityService.php
│   └── Http/
│       └── Controllers/
│           ├── Assessments/
│           │   └── IntegrityEventController.php
│           └── Admin/
│               └── IntegrityController.php
├── resources/
│   └── views/
│       └── admin/
│           └── integrity/
│               ├── index.blade.php
│               └── show.blade.php
├── routes/
│   ├── web.php
│   └── admin.php
├── app/
│   ├── Services/
│   │   └── ExamGenerationService.php   (set fingerprint)
│   └── Http/
│       └── Controllers/
│           └── Assessments/
│               └── ExamController.php   (verify fingerprint)
└── tests/
    └── Feature/
        └── Assessments/
            └── IntegrityTest.php


IMPLEMENTATION PROMPT ID: LABGATO-BUILD55-INTEGRITY-MONITORING

CONTEXT:
LabGATO does not claim to be AI-proof. Instead it builds a defensible Exam Integrity System
that records observable events during an examination, weights them, and derives an internal
status (normal / review / high_risk) the Super Admin can review alongside results.

OBJECTIVE:
Add integrity event schema, client capture on the exam page, a batched reporting endpoint,
a weighting/threshold service, multi-session detection, and an admin review panel.

EXISTING:
- exam_attempts.integrity_status column (default 'normal').
- Exam page (Build 54) with deterrents already blocking copy/paste/etc.
- ExamAttempt, ExamAnswer models.

FILES TO INSPECT:
- resources/views/assessments/exam.blade.php
- app/Services/ExamGenerationService.php
- app/Http/Controllers/Assessments/ExamController.php
- routes/web.php, routes/admin.php

FILES TO CREATE/MODIFY: (target paths)

──────────────────────────────────────────────
TASK 1: SCHEMA
──────────────────────────────────────────────

Migration create_integrity_events_table:
- id
- exam_attempt_id: foreignId constrained cascadeOnDelete
- user_id: foreignId constrained cascadeOnDelete
- event_type: string
- question_number: integer nullable
- weight: integer default 1
- metadata: json nullable
- timestamps
Indexes: exam_attempt_id, event_type.

Migration add session_fingerprint: string nullable after option_orders on exam_attempts.

──────────────────────────────────────────────
TASK 2: MODEL + EVENT TYPES + WEIGHTS
──────────────────────────────────────────────

IntegrityEvent model:
- belongsTo ExamAttempt, User. casts metadata array, weight int.
- const TYPES = [
    tab_switch, window_blur, fullscreen_exit,
    copy_attempt, paste_attempt, cut_attempt, print_attempt, right_click_attempt,
    session_change, multiple_session, unusual_timing
  ]
- const WEIGHTS = [
    tab_switch=>1, window_blur=>1, fullscreen_exit=>1, right_click_attempt=>1,
    copy_attempt=>2, paste_attempt=>2, cut_attempt=>2, print_attempt=>2,
    session_change=>5, multiple_session=>5, unusual_timing=>3
  ]

──────────────────────────────────────────────
TASK 3: INTEGRITY SERVICE
──────────────────────────────────────────────

IntegrityService:
- fingerprint(Request): string → sha1(ip + '|' + userAgent).
- log(ExamAttempt, type, ?questionNumber, ?metadata): create event with weight from
  WEIGHTS (default 1); then recalculate(attempt).
- recalculate(ExamAttempt):
  total = sum(weights of its events).
  status = total < 3 ? 'normal' : (total <= 7 ? 'review' : 'high_risk');
  Force 'high_risk' if any multiple_session event exists.
  Save attempt.integrity_status.
- verifySession(ExamAttempt, Request): if attempt.session_fingerprint set and differs
  from fingerprint(request) → log session_change once (guard: only if not already logged
  for this attempt) + recalculate.

──────────────────────────────────────────────
TASK 4: SET + VERIFY FINGERPRINT
──────────────────────────────────────────────

ExamGenerationService.startAttempt: set session_fingerprint = IntegrityService::fingerprint.

ExamController (show/saveAnswer/flag/submit): call IntegrityService::verifySession before
processing (only for in_progress attempts).

──────────────────────────────────────────────
TASK 5: BATCHED REPORTING ENDPOINT
──────────────────────────────────────────────

Route (auth, throttle:120/min):
POST /exam/{attempt}/integrity  → IntegrityEventController@store

Accepts JSON: { events: [ {type, q, meta} , ... ] }  (also tolerate single object).
- Authorize ownership + in_progress.
- For each event: validate type in TYPES; cap batch to 50; log via service.
- Return 204.

──────────────────────────────────────────────
TASK 6: CLIENT EVENT CAPTURE (EXAM PAGE)
──────────────────────────────────────────────

Modify exam.blade.php: add an integrity capture module inside the exam Alpine component.

Queue + flush:
- pushEvent(type): queue.push({type, q: currentIndex+1}); (client-side dedupe: same type
  within 2s ignored).
- setInterval(flush, 5000); window 'pagehide' → flush (navigator.sendBeacon preferred).
- flush(): POST batch to endpoint with CSRF; clear queue.

Listeners:
- document 'visibilitychange' → if hidden pushEvent('tab_switch').
- window 'blur' → pushEvent('window_blur').
- document 'fullscreenchange' → if !document.fullscreenElement pushEvent('fullscreen_exit').
- 'copy'/'paste'/'cut' → pushEvent('copy_attempt'/'paste_attempt'/'cut_attempt').
- 'beforeprint' → pushEvent('print_attempt').
- 'contextmenu' → pushEvent('right_click_attempt').

(These complement the existing deterrents; they now also REPORT.)

──────────────────────────────────────────────
TASK 7: ADMIN INTEGRITY REVIEW PANEL
──────────────────────────────────────────────

Routes (permission integrity.view, super admin):
GET /admin/integrity                 → index
GET /admin/integrity/{attempt}       → show
POST /admin/integrity/{attempt}/status  → manual override {status}

Add permission integrity.view to RolePermissionSeeder (super_admin).
Add "Integrity" item under Assessments in admin sidebar.

index.blade.php:
- Filter tabs: All | 🟢 Normal | 🟡 Review |  High Risk.
- Search by candidate name/email.
- Table: Candidate | Assessment | Mode | Score | Events count | Integrity status (badge)
  | Started | [Review].
- Default sort: high_risk first, then review.

show.blade.php:
- Attempt summary card: candidate, assessment, mode, score/percent, status, times.
- Manual override select (normal/review/high_risk) + save.
- Events timeline (ordered): time | event_type badge | question # | metadata.
- Link to candidate profile + results (Build 56).

──────────────────────────────────────────────
TASK 8: TESTS
──────────────────────────────────────────────

tests/Feature/Assessments/IntegrityTest.php:
- Test endpoint logs a batch of events and returns 204.
- Test invalid event_type rejected.
- Test weights: 2 tab_switch (2) → still normal; add copy attempts to reach review;
  many events → high_risk.
- Test multiple_session forces high_risk.
- Test session fingerprint mismatch logs session_change.
- Test cannot post events to another user's attempt.
- Test admin index filters by status; show lists events.
- Test manual override updates status.
- Test integrity route blocked for non-admin.

DEFINITION OF DONE:
- integrity_events + fingerprint schema in place.
- Client captures + batches events; endpoint stores with weights.
- Status thresholds compute normal/review/high_risk; multiple_session forces high_risk.
- Multi-session detection logs session_change.
- Admin panel lists + reviews attempts and events; manual override works.
- All tests pass.