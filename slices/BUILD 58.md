labgato/
├── composer.json
├── database/
│   └── migrations/
│       ├── xxxx_create_certificates_table.php
│       └── xxxx_add_certificate_enabled_to_assessments_table.php
├── app/
│   ├── Models/
│   │   └── Certificate.php
│   ├── Services/
│   │   └── CertificateService.php
│   └── Http/
│       └── Controllers/
│           ├── Assessments/
│           │   └── CertificateController.php
│           ├── PublicSite/
│           │   └── VerifyController.php
│           └── Admin/
│               └── CertificateAdminController.php
├── resources/
│   └── views/
│       ├── certificates/
│       │   ├── pdf.blade.php
│       │   ├── my-certificates.blade.php
│       │   ├── verify.blade.php
│       │   └── share.blade.php
│       └── admin/
│           └── certificates/
│               └── index.blade.php
├── routes/
│   ├── web.php
│   └── admin.php
├── resources/
│   └── views/
│       └── assessments/
│           └── result.blade.php   (add Download/Share buttons)
└── tests/
    └── Feature/
        └── Assessments/
            └── CertificatesTest.php

IMPLEMENTATION PROMPT ID: LABGATO-BUILD58-CERTIFICATES-SHARING

CONTEXT:
Participants who complete the ASCPI Challenge (and future monthly challenges) earn a
verifiable LabGOTO certificate with a QR code linking to a public verification page.
Certificates must NEVER be presented as ASCP certification or an official ASCP credential.
Participants can also share a branded result card to social media. Generation must work on
shared hosting WITHOUT a queue worker (lazy, cached PDF generation).

OBJECTIVE:
Build the certificate schema + service (tiers, numbering, verification codes), DomPDF
certificate with QR, public verify + share pages, my-certificates + download, admin
revocation, and result-page buttons.

EXISTING:
- ExamAttempt (score, percent), ResultsService (rank/topPercent/isReleased).
- assessments.pass_mark_percent exists.
- Public layout + OG meta component.

FILES TO INSPECT:
- app/Services/ResultsService.php
- app/Models/Assessment.php, ExamAttempt.php
- resources/views/assessments/result.blade.php
- routes/web.php, routes/admin.php

──────────────────────────────────────────────
TASK 1: PACKAGES
──────────────────────────────────────────────

composer require barryvdh/laravel-dompdf
composer require simplesoftwareio/simple-qrcode

(QR PNG needs GD — present on Hostinger. Wrap QR generation in try/catch with graceful
fallback to printing the verification URL as text if GD is unavailable.)

──────────────────────────────────────────────
TASK 2: SCHEMA
──────────────────────────────────────────────

Migration create_certificates_table:
- id
- exam_attempt_id: foreignId constrained cascadeOnDelete (unique)
- user_id: foreignId constrained cascadeOnDelete
- assessment_id: foreignId constrained cascadeOnDelete
- certificate_number: string unique        (e.g. LGT-2026-00042)
- verification_code: string unique indexed (10-char unambiguous uppercase)
- type: string                              (participation | achievement | distinction)
- score_percent: decimal(5,2)               (snapshot at issue time)
- rank_at_issue: integer nullable           (snapshot)
- pdf_path: string nullable
- issued_at: datetime
- revoked_at: datetime nullable
- revoke_reason: string nullable
- timestamps

Migration add to assessments: certificate_enabled boolean default true.

──────────────────────────────────────────────
TASK 3: MODEL
──────────────────────────────────────────────

Certificate model:
- belongsTo ExamAttempt, User, Assessment.
- casts issued_at/revoked_at datetime, score_percent float.
- helpers: isRevoked(), verifyUrl() → url("/verify/{$verification_code}"),
  shareUrl() → url("/share/{$verification_code}").
- const TYPES = [participation, achievement, distinction].
- Label map: participation => "Certificate of Participation",
  achievement => "Certificate of Achievement", distinction => "Certificate of Distinction".

──────────────────────────────────────────────
TASK 4: CERTIFICATE SERVICE
──────────────────────────────────────────────

Create app/Services/CertificateService.php.

1. eligibleType(ExamAttempt $t): ?string
   - Return null unless: attempt submitted/auto_submitted AND
     ResultsService::isReleased(assessment) AND assessment.certificate_enabled.
   - distinction: topPercent($t) <= 10.
   - achievement: percent >= assessment.pass_mark_percent (if set).
   - participation: otherwise.
   - Return highest applicable (distinction > achievement > participation).

2. issue(ExamAttempt $t): ?Certificate
   - If certificate exists for attempt → return it (idempotent).
   - type = eligibleType(); null → return null.
   - certificate_number: "LGT-{year}-{5-digit sequence}" using count+1 with a
     retry loop on unique collision (max 3).
   - verification_code: 10 chars from "ABCDEFGHJKLMNPQRSTUVWXYZ23456789" (no 0/O/1/I).
   - Snapshot score_percent + rank_at_issue (ResultsService::rank).
   - issued_at now(). Save. Return.

3. ensurePdf(Certificate $c): string (storage path)
   - If pdf_path exists on disk → return it.
   - QR: try QrCode::format('png')->size(300)->generate($c->verifyUrl()) → base64.
     catch → qrBase64 = null.
   - Render certificates/pdf view with certificate + user + assessment + qrBase64.
   - Pdf::loadView(...)->setPaper('a4', 'landscape')
     ->save(storage_path("app/public/certificates/{$c->certificate_number}.pdf")).
   - Save pdf_path; return.

4. verify(string $code): array
   - Find by verification_code with user, assessment.
   - Not found → [status => 'not_found'].
   - Found → [status => revoked_at ? 'revoked' : 'valid', certificate, user, assessment].

──────────────────────────────────────────────
TASK 5: CERTIFICATE PDF VIEW (BRANDED, LANDSCAPE)
──────────────────────────────────────────────

certificates/pdf.blade.php — plain embedded CSS ONLY (DomPDF; no Tailwind).

Layout (A4 landscape):
- Outer border frame (double border, primary/dark).
- Top band: "LabGOTO" wordmark + "Laboratory Toolkit".
- Certificate type label (from label map) in caps.
- "This is proudly presented to"
- Participant full name — large serif-style (font-family: Georgia, serif).
- "for successfully completing the {assessment title}"
- Score line: "Score: {percent}%  ·  Rank: #{rank}" (omit for participation if desired).
- Date of issue + Certificate number.
- Bottom row: left = signature line "Program Director, LabGOTO";
  right = QR image (base64) + "Scan to verify" + verification code text
  (if qrBase64 null, print verify URL as text).
- DISCLAIMER (small, full width, required):
  "This certificate is issued by LabGOTO in recognition of performance in the
  {assessment title}. It is a LabGOTO achievement certificate and is NOT an ASCP
  certification nor an official ASCP credential."

──────────────────────────────────────────────
TASK 6: ROUTES + CONTROLLERS (USER + PUBLIC)
──────────────────────────────────────────────

Routes (auth):
GET  /assessments/my-certificates                 → myCertificates
GET  /exam/{attempt}/certificate/download         → download

Routes (public):
GET  /verify/{code}                               → verify
GET  /share/{code}                                → share

CertificateController:
- myCertificates: auth user's certificates (assessment loaded), newest first;
  rows: number | assessment | tier badge | score | issued | [Download] [Share].
- download: authorize ownership; issue() (creates if eligible, else 403 with message);
  if revoked → 410 "Certificate revoked"; ensurePdf(); response()->download().

VerifyController:
- verify: service->verify; render verify view (valid/revoked/not_found states).
- share: find certificate (must NOT be revoked; revoked → generic card without score or
  404 — choose 404); render share view with OG tags.

──────────────────────────────────────────────
TASK 7: VERIFY + SHARE VIEWS
──────────────────────────────────────────────

verify.blade.php (public, minimal branded layout):
- VALID: green check + "Certificate Verified" + details table:
  Holder | Assessment | Tier | Score | Rank | Issued | Certificate No.
- REVOKED: red + "This certificate has been revoked." + reason if public-safe.
- NOT FOUND: neutral + "No certificate matches this code."
- Footer: LabGOTO branding + disclaimer repeated.

share.blade.php (public):
- Centered branded result card (Abyss Blue gradient, gold accent):
  "I scored {percent}% in the {assessment title}"
  🏆 Rank #{rank} · Top {top}%
  LabGOTO logo + verify link (small).
- Share intent buttons (prefilled):
  WhatsApp: https://wa.me/?text={encoded text + url}
  X:        https://twitter.com/intent/tweet?text=...&url=...
  LinkedIn: https://www.linkedin.com/sharing/share-offsite/?url=...
  Facebook: https://www.facebook.com/sharer/sharer.php?u=...
- OG/Twitter meta via meta component overrides:
  og:title "I scored {percent}% in the {assessment title} — LabGOTO"
  og:description "🏆 Rank #{rank} · Top {top}% · Verified on LabGOTO"
  og:url = share URL; og:image = default brand image.

──────────────────────────────────────────────
TASK 8: RESULT PAGE BUTTONS (BUILD 56)
──────────────────────────────────────────────

Modify assessments/result.blade.php (revealed state):
- If CertificateService::eligibleType(attempt) not null:
  [⬇ Download Certificate] → download route
  [🔗 Share My Result] → share route (issue first if needed via download controller? 
  better: add GET /exam/{attempt}/certificate/share that issues then redirects to shareUrl)
- Else show nothing (or subtle "Certificates unlock when results are revealed").

Add route GET /exam/{attempt}/certificate/share → shareRedirect.

──────────────────────────────────────────────
TASK 9: ADMIN CERTIFICATES PANEL
──────────────────────────────────────────────

RolePermissionSeeder: add certificates.view, certificates.revoke (super_admin).
Sidebar: "Certificates" under Assessments.

Routes (admin):
GET  /admin/certificates                     → index (filter: assessment, tier, revoked)
POST /admin/certificates/{certificate}/revoke  {reason nullable}

index.blade.php:
- Stats: total issued, by tier, revoked.
- Table: Number | Holder | Assessment | Tier | Score | Issued | Status | [Revoke].
- Revoke modal: optional reason; sets revoked_at (keeps record; verify page shows revoked;
  download blocked with 410).

──────────────────────────────────────────────
TASK 10: TESTS
──────────────────────────────────────────────

tests/Feature/Assessments/CertificatesTest.php:
- Test eligibility: participation for completed below pass mark; achievement at/above
  pass mark; distinction for top 10%; null before release or when not submitted.
- Test issue is idempotent (same id on second call).
- Test certificate_number format LGT-{year}-00001 and uniqueness.
- Test verification_code charset excludes 0/O/1/I and length 10.
- Test download returns application/pdf and caches pdf_path.
- Test download blocked for non-owner (403/404).
- Test verify page shows valid details; unknown code → not_found.
- Test revoked certificate: verify shows revoked; download 410; share 404.
- Test share page renders score + OG title containing percent.
- Test admin revoke sets revoked_at + reason; permission enforced.
- Test my-certificates lists user's certificates only.

DEFINITION OF DONE:
- Certificates schema + tiered eligibility + idempotent issue.
- Branded landscape PDF with QR + mandatory non-ASCP disclaimer.
- Public verify page (valid/revoked/not_found).
- Share card page with intents + OG tags.
- Result page offers Download/Share when eligible.
- My Certificates + admin panel with revocation.
- Works on shared hosting without queue workers.
- All tests pass.