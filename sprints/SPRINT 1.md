LABGOTO/
├── app/
│   ├── Models/ (All 6 resource models + User, Role, etc.)
│   ├── Rules/
│   │   └── ValidResourceSchema.php
│   ├── Services/
│   │   ├── ContentImageService.php
│   │   └── SopAttachmentService.php
│   └── Support/
│       └── RichTextSanitizer.php
└── tests/
    └── Feature/
        └── Security/
            ├── FileUploadSecurityTest.php
            ├── RichTextSanitizationTest.php
            ├── SchemaInjectionTest.php
            └── MassAssignmentTest.php

IMPLEMENTATION PROMPT ID: LABGOTO-HARDENING-SPRINT1

CONTEXT:
LABGOTO MVP feature development is complete. We are now hardening the application for production release. Sprint 1 focuses strictly on Security and Data Integrity.

Because LABGOTO accepts file uploads (SOP attachments, content images), rich text input (SOPs, Interpretation, Knowledge), and complex JSON schemas (Calculators, Tools), these attack vectors must be explicitly secured and tested.

OBJECTIVE:
Audit and harden file upload services, rich text sanitization, JSON schema validation, and Eloquent model mass assignment protections. Write explicit security regression tests for each vector.

EXISTING ARCHITECTURE:
- File uploads handled by SopAttachmentService and ContentImageService.
- Rich text handled by RichTextSanitizer.
- JSON schemas validated by ValidResourceSchema rule.
- RBAC enforced via Policies and Middleware.

FILES TO INSPECT:
- app/Services/SopAttachmentService.php
- app/Services/ContentImageService.php
- app/Support/RichTextSanitizer.php
- app/Rules/ValidResourceSchema.php
- All Eloquent Models (Calculator, LaboratoryTool, ReferenceRange, SopResource, InterpretationResource, KnowledgeResource)
- config/filesystems.php

FILES TO CREATE:
- tests/Feature/Security/FileUploadSecurityTest.php
- tests/Feature/Security/RichTextSanitizationTest.php
- tests/Feature/Security/SchemaInjectionTest.php
- tests/Feature/Security/MassAssignmentTest.php

FILES TO MODIFY:
- app/Services/SopAttachmentService.php (if MIME spoofing checks are missing)
- app/Services/ContentImageService.php (if MIME spoofing checks are missing)
- app/Support/RichTextSanitizer.php (if XSS vectors remain)
- app/Rules/ValidResourceSchema.php (if schema injection is possible)
- Any Model missing strict `$fillable` or `$guarded` definitions.

TASK 1: FILE UPLOAD HARDENING

Review and update SopAttachmentService and ContentImageService.

Requirements:
1. MIME Type Spoofing Prevention:
   - Do not rely solely on the client-provided MIME type (`$file->getMimeType()`).
   - Use Laravel's `validate` rule `mimes:` which checks the actual file contents/signature, or use the `finfo` extension to verify the file signature manually if needed.
2. Extension Validation:
   - Ensure the file extension matches the allowed list strictly.
3. File Name Sanitization:
   - Ensure stored file names do not contain directory traversal characters (e.g., `../`). Laravel's `store()` or `storeAs()` handles this, but verify custom logic if used.
4. Storage Isolation:
   - Ensure files are stored in `storage/app/public/...` and NOT in the `public/` root directory directly.

TASK 2: RICH TEXT SANITIZATION HARDENING

Review and update RichTextSanitizer.

Requirements:
1. XSS Prevention:
   - Strip `<script>`, `<iframe>`, `<object>`, `<embed>`, `<form>`, and `<input>` tags completely.
   - Strip all inline event handlers (e.g., `onclick`, `onerror`, `onload`) from allowed tags like `<a>` and `<img>`.
   - Strip `javascript:`, `vbscript:`, and `data:` (except for safe images) protocols from `href` and `src` attributes.
2. Implementation:
   - If using a package like `mews/purifier` (HTMLPurifier), ensure the configuration is strict.
   - If using a custom regex/strip_tags approach, replace it with a robust library or highly tested DOMDocument parsing logic, as regex is unsafe for HTML sanitization.
3. SVG Sanitization (Content Images):
   - If SVGs are allowed, they MUST be sanitized to remove embedded `<script>` tags and XSS payloads. (Recommendation: Disable SVG uploads for MVP unless a dedicated SVG sanitizer like `enshrined/svg-sanitize` is installed).

TASK 3: JSON SCHEMA VALIDATION HARDENING

Review and update ValidResourceSchema (used for Calculator/Tool input/output schemas).

Requirements:
1. Depth and Size Limits:
   - Prevent Denial of Service (DoS) via massive JSON payloads. Limit the JSON string length (e.g., max 64KB) and array depth.
2. Type Safety:
   - Ensure the decoded JSON is strictly an array of objects/arrays.
   - Prevent unexpected types (e.g., a string instead of an array) from causing fatal errors in the CalculatorService or ToolService.
3. Key Validation:
   - Ensure `key` values in the schema only contain alphanumeric characters and underscores (prevent injection into dynamic form names or Livewire properties).

TASK 4: MASS ASSIGNMENT PROTECTION

Review all 6 resource models + User, Role, Permission, etc.

Requirements:
1. Explicit `$fillable` or `$guarded`:
   - Every model MUST have an explicitly defined `$fillable` array OR `$guarded = []` (only if using strict request validation everywhere, but `$fillable` is safer).
   - Ensure fields like `status`, `created_by`, `updated_by`, and `published_at` are NOT mass-assignable from standard public forms. They should only be updated via explicit controller logic or dedicated service methods.
2. Audit Bypass Prevention:
   - Ensure `created_by` and `updated_by` cannot be spoofed by a malicious admin submitting a hidden form field. These must be injected by the Controller/Service using `auth()->id()`.

TASK 5: SECURITY REGRESSION TESTS

Create the following test files to lock in these security boundaries.

1. tests/Feature/Security/FileUploadSecurityTest.php
   - test_sop_upload_rejects_php_file_with_image_extension (e.g., `malware.php.jpg`).
   - test_sop_upload_rejects_executable_files.
   - test_content_image_upload_rejects_svg_with_xss (if SVG is allowed).
   - test_content_image_upload_rejects_files_exceeding_size_limit.

2. tests/Feature/Security/RichTextSanitizationTest.php
   - test_sanitizer_removes_script_tags.
   - test_sanitizer_removes_inline_event_handlers (e.g., `<img src="x" onerror="alert(1)">`).
   - test_sanitizer_removes_javascript_protocol_from_links.
   - test_sanitizer_preserves_safe_html_structure.

3. tests/Feature/Security/SchemaInjectionTest.php
   - test_calculator_schema_rejects_massive_json_payload.
   - test_calculator_schema_rejects_invalid_keys (e.g., keys with spaces or special characters).
   - test_calculator_schema_rejects_non_array_json.

4. tests/Feature/Security/MassAssignmentTest.php
   - test_user_cannot_spoof_created_by_field_via_mass_assignment.
   - test_user_cannot_spoof_status_field_via_mass_assignment (if status transitions are restricted).

SECURITY REQUIREMENTS:
- Do not introduce heavy external dependencies unless strictly necessary for security (e.g., HTMLPurifier).
- Ensure all tests run using the `RefreshDatabase` trait.
- Do not expose real file paths in error messages.

DEFINITION OF DONE:
- File upload services verify actual file signatures, not just extensions.
- SVG uploads are either disabled or strictly sanitized.
- Rich text sanitizer reliably strips XSS vectors.
- JSON schema validator prevents DoS and injection.
- All models have strict mass assignment protection.
- All 4 security test files exist and pass.
- No existing functionality is broken by the hardening.