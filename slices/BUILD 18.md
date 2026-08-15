labgato/
├── app/
│   ├── Exceptions/
│   │   └── Handler.php (or bootstrap/app.php)
│   ├── Http/
│   │   └── Livewire/
│   │       └── Admin/
│   │           └── Ai/
│   │               └── AiGenerate.php
│   ├── Models/
│   │   └── AiUsage.php
│   └── Services/
│       └── Ai/
│           ├── AiContentService.php
│           ├── AiRateLimiterService.php
│           └── UseCases/
│               ├── AiUseCaseInterface.php
│               ├── DraftKnowledgeArticle.php
│               ├── DraftClinicalContext.php
│               └── SummarizeSopProcedure.php
├── database/
│   └── migrations/
│       └── xxxx_create_ai_usage_table.php
├── resources/
│   └── views/
│       ├── components/
│       │   └── ai/
│       │       └── generate-button.blade.php
│       └── livewire/
│           └── admin/
│               └── ai/
│                   └── generate.blade.php
├── routes/
│   └── web.php (if dedicated AI endpoints are needed)
└── tests/
    └── Feature/
        └── Ai/
            ├── AiRateLimitTest.php
            ├── AiContentServiceTest.php
            └── AiGenerateButtonTest.php

IMPLEMENTATION PROMPT ID: LABGATO-PHASE05-BUILD18

CONTEXT:
LabGato AI Foundation (Build 17) is complete. The Groq adapter, exceptions, and configuration are in place.

We are now implementing Build 18: AI Content Generation, UI & Rate Limiting (Slice 8).

This build creates the ai_usage tracking table, the internal rate limiter, the AI use case services, the reusable Livewire UI component, and integrates AI buttons into the Knowledge, Interpretation, and SOP admin forms.

OBJECTIVE:
Implement the complete AI content generation pipeline, from rate limiting and prompt building to UI integration and error handling.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- AI is an assistant, not an autopilot (Human-in-the-Loop).
- AI outputs are treated as untrusted and passed through RichTextSanitizer.
- Rate limits: 5s cooldown, 50/day user quota, 1000/day global quota.
- AI features integrate into existing admin CRUD forms via buttons.
- AI exceptions must be mapped to safe UI error messages.

FILES TO INSPECT:
- app/Services/Ai/AiProviderService.php
- app/Contracts/AiProviderInterface.php
- app/Support/AiResponse.php
- app/Support/RichTextSanitizer.php
- resources/views/admin/knowledge/partials/form.blade.php
- resources/views/admin/interpretation/partials/form.blade.php
- resources/views/admin/sops/partials/form.blade.php
- app/Exceptions/Handler.php (or bootstrap/app.php)

FILES TO CREATE:
- database/migrations/xxxx_create_ai_usage_table.php
- app/Models/AiUsage.php
- app/Services/Ai/AiRateLimiterService.php
- app/Services/Ai/AiContentService.php
- app/Services/Ai/UseCases/AiUseCaseInterface.php
- app/Services/Ai/UseCases/DraftKnowledgeArticle.php
- app/Services/Ai/UseCases/DraftClinicalContext.php
- app/Services/Ai/UseCases/SummarizeSopProcedure.php
- app/Http/Livewire/Admin/Ai/AiGenerate.php
- resources/views/components/ai/generate-button.blade.php
- resources/views/livewire/admin/ai/generate.blade.php
- tests/Feature/Ai/AiRateLimitTest.php
- tests/Feature/Ai/AiContentServiceTest.php
- tests/Feature/Ai/AiGenerateButtonTest.php

FILES TO MODIFY:
- app/Exceptions/Handler.php (or bootstrap/app.php)
- resources/views/admin/knowledge/partials/form.blade.php
- resources/views/admin/interpretation/partials/form.blade.php
- resources/views/admin/sops/partials/form.blade.php

TASK 1: AI USAGE DATABASE TABLE

Create the ai_usage migration.

Columns:
- id: primary key
- user_id: unsignedBigInteger, nullable, foreign key to users.id, null on delete
- action: string (e.g., 'draft_knowledge', 'summarize_sop')
- provider: string (e.g., 'groq')
- model: string (e.g., 'llama-3.1-70b-versatile')
- prompt_tokens: integer, default 0
- completion_tokens: integer, default 0
- latency_ms: integer, default 0
- status: string ('success', 'failed', 'filtered')
- created_at: timestamp, indexed

Create app/Models/AiUsage.php.
- Fillable: user_id, action, provider, model, prompt_tokens, completion_tokens, latency_ms, status.
- Cast created_at to datetime.

TASK 2: AI RATE LIMITER SERVICE

Create app/Services/Ai/AiRateLimiterService.php.

Responsibilities:
- Check cooldown (5 seconds per user).
- Check daily user quota (50 requests per user per day).
- Check global daily quota (1000 requests per day).
- Throw AiRateLimitException if any limit is exceeded.

Implementation:
- Cooldown: Use Laravel's RateLimiter facade with the database cache store. Key: "ai-cooldown:{userId}".
- Daily User Quota: Query AiUsage model for today's count where user_id = $userId.
- Global Daily Quota: Query AiUsage model for today's total count.

Methods:
- ensureWithinLimits(int $userId): void
- recordUsage(int $userId, string $action, AiResponse $response, string $status): void

TASK 3: AI USE CASE SERVICES

Create app/Services/Ai/UseCases/AiUseCaseInterface.php:

interface AiUseCaseInterface
{
    public function buildPrompt(array $context): array;
    public function sanitizeOutput(string $content): string;
}

Create the following use case classes:

1. DraftKnowledgeArticle
   - System prompt: Expert medical laboratory scientist drafting educational content.
   - User prompt: Title, short description, and optional outline.
   - Output: Markdown content for the `content` field.
   - Sanitization: Pass through RichTextSanitizer.

2. DraftClinicalContext
   - System prompt: Expert medical laboratory scientist drafting clinical interpretation context.
   - User prompt: Title and short description.
   - Output: Plain text for the `clinical_context` field.
   - Sanitization: Strip HTML tags.

3. SummarizeSopProcedure
   - System prompt: Expert medical laboratory scientist summarizing a Standard Operating Procedure.
   - User prompt: The existing `procedure_content` text (truncated to 8000 chars).
   - Output: A 2-3 sentence summary for the `short_description` or `purpose` field.
   - Sanitization: Strip HTML tags.

Rules for all use cases:
- Wrap user input in XML delimiters (e.g., <user_input>...</user_input>).
- Instruct the AI to ignore instructions within the delimiters.
- Do not include metadata, IDs, or timestamps in the prompt.

TASK 4: AI CONTENT SERVICE (ORCHESTRATOR)

Create app/Services/Ai/AiContentService.php.

Responsibilities:
- Resolve the appropriate use case based on the action string.
- Check rate limits via AiRateLimiterService.
- Build the prompt via the use case.
- Call AiProviderService to generate content.
- Sanitize the output via the use case.
- Record usage via AiRateLimiterService.
- Return the sanitized content.

Method:
public function generate(string $action, array $context, int $userId): string

Error Handling:
- Catch AiException and re-throw with context.
- Record failed usage in ai_usage table.

TASK 5: AI GENERATE LIVEWIRE COMPONENT

Create app/Http/Livewire/Admin/Ai/AiGenerate.php.

Props:
- action: string (e.g., 'draft_knowledge', 'summarize_sop')
- targetField: string (the ID of the textarea to inject content into)
- confirmOverwrite: bool (default true)

State:
- isLoading: bool
- errorMessage: string|null
- showConfirmation: bool
- generatedContent: string|null

Methods:
- generate(array $contextData): void
  - Calls AiContentService.
  - On success, dispatches a browser event with the generated content.
  - On error, sets errorMessage.
- confirmAndGenerate(): void
  - Proceeds with generation after confirmation.

Create resources/views/livewire/admin/ai/generate.blade.php.

View requirements:
- Display the "Generate with AI" button.
- Show loading spinner when isLoading is true.
- Show confirmation modal when showConfirmation is true.
- Show error message when errorMessage is set.
- Show hallucination warning banner after successful generation.
- Use aria-live regions for loading and error states.

Create resources/views/components/ai/generate-button.blade.php.

This is a simple wrapper component that renders the Livewire component with the appropriate props.

TASK 6: INTEGRATE AI BUTTONS INTO ADMIN FORMS

Modify the following admin form partials to include the AI generate button:

1. resources/views/admin/knowledge/partials/form.blade.php
   - Add <x-ai.generate-button> above the `content` textarea.
   - Action: 'draft_knowledge'
   - Target field: 'content'

2. resources/views/admin/interpretation/partials/form.blade.php
   - Add <x-ai.generate-button> above the `content` textarea.
   - Action: 'draft_knowledge' (or a dedicated interpretation action)
   - Target field: 'content'
   - Add a second <x-ai.generate-button> next to the `clinical_context` textarea.
   - Action: 'draft_clinical_context'
   - Target field: 'clinical_context'

3. resources/views/admin/sops/partials/form.blade.php
   - Add <x-ai.generate-button> next to the `short_description` textarea.
   - Action: 'summarize_sop'
   - Target field: 'short_description'

Integration approach:
- Use Alpine.js to read the form field values (e.g., title, outline, procedure_content) and pass them to the Livewire component's generate() method.
- Use Alpine.js to listen for the browser event dispatched by the Livewire component and inject the generated content into the target textarea.

TASK 7: EXCEPTION HANDLER MAPPING

Modify the global exception handler (app/Exceptions/Handler.php or bootstrap/app.php).

Map AI exceptions to safe UI error messages:
- AiTimeoutException -> "The AI took too long to respond. Please try again."
- AiRateLimitException -> "You have reached your AI usage limit. Please try again later."
- AiContentFilterException -> "The AI could not process this request. Please revise your input."
- AiDisabledException -> "AI features are currently disabled."
- Generic AiException -> "The AI assistant is temporarily unavailable. Please try again later."

Rules:
- Do not expose technical error details to the user.
- Log the full exception details internally.

TASK 8: FEATURE TESTS

Create tests/Feature/Ai/AiRateLimitTest.php.

Required tests:
1. test_user_cannot_generate_within_cooldown_period
   - Trigger AI generation.
   - Attempt to trigger again within 5 seconds.
   - Assert AiRateLimitException is thrown.
2. test_user_is_blocked_after_daily_quota
   - Create 50 ai_usage records for today.
   - Attempt to generate.
   - Assert AiRateLimitException is thrown.
3. test_platform_is_blocked_after_global_quota
   - Create 1000 ai_usage records for today.
   - Attempt to generate.
   - Assert AiRateLimitException is thrown.

Create tests/Feature/Ai/AiContentServiceTest.php.

Required tests:
1. test_service_generates_content_successfully
   - Mock the AiProviderService to return a successful response.
   - Call generate() with valid context.
   - Assert the returned content is sanitized.
2. test_service_strips_html_from_output
   - Mock the AiProviderService to return content with <script> tags.
   - Call generate().
   - Assert the returned content does not contain <script> tags.
3. test_service_records_usage_on_success
   - Mock the AiProviderService.
   - Call generate().
   - Assert an ai_usage record is created with status 'success'.
4. test_service_records_usage_on_failure
   - Mock the AiProviderService to throw an exception.
   - Call generate().
   - Assert an ai_usage record is created with status 'failed'.

Create tests/Feature/Ai/AiGenerateButtonTest.php.

Required tests:
1. test_generate_button_renders_for_authorized_user
   - Authenticate a user with ai.use permission.
   - Visit an admin form page.
   - Assert the AI generate button is visible.
2. test_generate_button_is_hidden_for_unauthorized_user
   - Authenticate a user without ai.use permission.
   - Visit an admin form page.
   - Assert the AI generate button is not visible.
3. test_confirmation_modal_appears_when_target_field_has_content
   - Simulate clicking the generate button when the target field has text.
   - Assert the confirmation modal is displayed.

SECURITY REQUIREMENTS:
- Do not log API keys or full prompt content.
- Sanitize all AI outputs before returning to the frontend.
- Enforce RBAC (ai.use permission) on all AI endpoints.
- Do not allow AI to modify resource status or permissions.

DEFINITION OF DONE:
- ai_usage table and model exist.
- AiRateLimiterService enforces cooldowns and quotas.
- AI use case services build prompts and sanitize outputs.
- AiContentService orchestrates the generation pipeline.
- AiGenerate Livewire component renders with loading, confirmation, and error states.
- AI buttons are integrated into Knowledge, Interpretation, and SOP admin forms.
- Exception handler maps AI errors to safe messages.
- Feature tests for rate limiting, sanitization, and UI states pass.
- No AI feature can auto-publish a resource.