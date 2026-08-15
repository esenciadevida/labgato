# 03 — AI UI and Rate Limiting

## Depends On

This document depends on:

- The approved Phase 02 documentation (specifically RBAC Implementation, Base API Architecture).
- The approved Phase 03 Resource Architecture (specifically Admin CRUD Patterns).
- The approved Phase 05 AI Provider Adapter and Security document.
- The approved Phase 05 AI Content Generation and Summarization document.

## Defines

This document defines:

- The reusable UI components for AI interactions in the Admin Portal.
- The loading, error, and confirmation state patterns.
- The internal application rate limiting strategy to control API costs.
- The AI usage tracking and quota management architecture.
- The accessibility requirements for asynchronous AI operations.
- The testing requirements for UI states and rate limit enforcement.

## Affects

This document affects:

- All future AI-integrated Livewire components and Blade views.
- The database schema (AI usage tracking).
- The global exception handler (for AI-specific UI errors).
- The Advanced Analytics Dashboard document (which will consume usage data).

## Implementation Impact

This document does not directly implement application code.

Its implementation impact is:

- Prevents runaway API costs through strict internal rate limiting.
- Ensures a consistent, accessible, and professional user experience for AI features.
- Prevents accidental double-submissions and UI dead-locks during long AI generation times.
- Provides the data foundation for tracking AI utilization across the platform.

---

# 1. Purpose

This document establishes the AI UI and Rate Limiting architecture for LabGato.

AI generation is asynchronous, slow (up to 15 seconds), and expensive. Without proper UI patterns, administrators may repeatedly click generation buttons, causing UI dead-locks and duplicate API charges. Without strict internal rate limiting, a single compromised or overzealous administrator account could exhaust the platform's monthly API budget in minutes.

This document defines how AI interactions are presented to the user and how the system protects itself from abuse and excessive costs.

---

# 2. AI UI Patterns and Components

AI features must not be scattered as ad-hoc HTML. They must use standardized, reusable components.

## 2.1 The Generate Button Component

Create a reusable Blade component for triggering AI actions.

Location: `resources/views/components/ai/generate-button.blade.php`

Props:
- `action`: The Livewire method or API endpoint to call.
- `label`: The button text (e.g., "Draft with AI", "Summarize").
- `target-field`: The ID of the textarea/editor to inject the result into.
- `confirm-overwrite`: Boolean, whether to prompt if the target field has text.

Visual Rules:
- Use a distinct "AI" visual indicator (e.g., a subtle sparkle icon or a specific secondary/ghost button variant with an AI badge).
- Must clearly indicate that this is an AI-assisted action, not a standard save action.

## 2.2 Loading States

Because AI generation takes up to 15 seconds, the UI must prevent double-submissions and inform the user.

Rules:
- When the button is clicked, it must immediately enter a `disabled` and `loading` state.
- Display a spinner and change the text to "Generating...".
- Use `aria-busy="true"` on the button or the surrounding container.
- Disable the target textarea/editor while generation is in progress.

## 2.3 Confirmation Dialogs

If the target field already contains user-typed text, clicking the AI button must trigger a confirmation modal.

Modal text:
"This will replace your existing text with AI-generated content. Are you sure you want to continue?"

Actions:
- "Cancel" (closes modal, does nothing).
- "Replace Text" (proceeds with AI generation).

## 2.4 Hallucination Warning Banner

Whenever an AI generation completes and text is injected into the editor, a temporary warning banner must appear above the field.

Banner text:
"AI-generated content inserted. Please review for scientific accuracy and edit as needed before saving."

Visual Rules:
- Use the `warning` semantic color (yellow/amber).
- Automatically dismiss after 10 seconds, or allow manual dismissal.

---

# 3. Error Handling in the UI

AI requests will inevitably fail due to network issues, provider outages, or content filters. The UI must handle these gracefully without exposing technical jargon.

## 3.1 Error Display

Errors must be displayed as inline alerts near the AI button or the target field, not as global toast notifications that might be missed.

Use the `x-ui.alert` component with the `danger` variant.

## 3.2 Error Message Mapping

The UI must display user-friendly messages mapped from the backend exceptions (defined in Doc 01):

| Backend Exception | UI Error Message |
|---|---|
| `AiTimeoutException` | "The AI took too long to respond. Please try again." |
| `AiRateLimitException` | "You have reached your AI usage limit for today. Please try again tomorrow." |
| `AiContentFilterException` | "The AI could not process this request. Please revise your input and try again." |
| `AiDisabledException` | "AI features are currently disabled by the administrator." |
| Generic `AiException` | "The AI assistant is temporarily unavailable. Please try again later." |

## 3.3 Recovery

- After an error, the Generate button must return to its normal, enabled state.
- The target textarea must remain enabled and retain its original content.

---

# 4. Rate Limiting Strategy

To protect the platform's API budget, LabGato will enforce strict internal rate limits before requests are ever sent to the AI provider.

## 4.1 Rate Limit Tiers

1. **Cooldown Limit (Anti-Spam)**:
   - 1 request per 5 seconds per user.
   - Prevents accidental double-clicks or script-based spamming.

2. **Daily User Quota**:
   - Maximum 50 AI generations per user, per day.
   - Prevents a single administrator from monopolizing the API or generating massive amounts of unreviewed content.

3. **Global Platform Quota**:
   - Maximum 1,000 AI generations per day across the entire platform.
   - Hard cap to prevent catastrophic billing events.

## 4.2 Rate Limit Implementation

Because LabGato runs on shared hosting without Redis, rate limiting must use the **database cache driver** or direct database queries.

- **Cooldowns**: Use Laravel's cache-backed `RateLimiter` facade with the `database` cache store.
- **Daily Quotas**: Use a dedicated database table (see Section 5) to track daily usage, as cache expiration can be unreliable for strict daily billing limits on shared hosting.

## 4.3 Handling Provider Rate Limits (429)

If the Groq API returns a 429 Too Many Requests error (meaning we hit the provider's limits before our internal limits):
- The `GroqAdapter` must catch this and throw `AiRateLimitException`.
- The UI must display the "usage limit" error message.
- The system should log a critical warning to the application logs so administrators know the internal quotas need adjustment.

---

# 5. AI Usage Tracking Architecture

To enforce daily quotas and provide data for the Advanced Analytics Dashboard, AI usage must be tracked in the database.

## 5.1 The `ai_usage` Table

Create a lightweight, dedicated table for AI metrics. Do not use the `audit_logs` table for this, as querying `audit_logs` for daily quotas is too slow.

Columns:
- `id`: primary key
- `user_id`: foreign key to users.id (nullable for system tasks)
- `action`: string (e.g., 'draft_knowledge', 'summarize_sop')
- `provider`: string (e.g., 'groq')
- `model`: string (e.g., 'llama-3.1-70b-versatile')
- `prompt_tokens`: integer (estimated or actual input tokens)
- `completion_tokens`: integer (actual output tokens)
- `latency_ms`: integer
- `status`: string ('success', 'failed', 'filtered')
- `created_at`: timestamp (indexed for daily queries)

## 5.2 Tracking Rules

- A record must be inserted into `ai_usage` *after* the AI provider responds (whether success or failure).
- If the request times out or fails before reaching the provider, `status` is 'failed' and token counts are 0.
- The `AiProviderService` is responsible for writing to this table, ensuring it happens even if the subsequent UI injection fails.

## 5.3 Quota Enforcement Logic

Before calling the AI provider, the `AiProviderService` must check:
1. Is the user's cooldown active? (Throw 429/RateLimitException).
2. Has the user exceeded 50 requests today? (Throw 429/RateLimitException).
3. Has the platform exceeded 1,000 requests today? (Throw 429/RateLimitException).

---

# 6. Accessibility Requirements for AI UI

Asynchronous AI operations introduce specific accessibility challenges.

## 6.1 Live Regions

- The area where the loading spinner appears must have `aria-live="polite"`.
- The area where the success/error message appears must have `aria-live="assertive"` (for errors) or `aria-live="polite"` (for success warnings).

## 6.2 Focus Management

- When the "Replace Text" confirmation modal opens, focus must move to the modal.
- When the modal closes, focus must return to the Generate button.
- When an error occurs, focus should not aggressively jump to the error message, but the error message must be announced to screen readers via `aria-live`.

## 6.3 Screen Reader Announcements

- When generation starts, announce: "Generating AI content, please wait."
- When generation succeeds, announce: "AI content generated. Please review the text area."
- When generation fails, announce the specific error message.

---

# 7. Testing Requirements

## 7.1 Rate Limit Tests

- Test that a user cannot trigger two AI requests within 5 seconds.
- Test that a user is blocked after 50 requests in a single day.
- Test that the platform is blocked after 1,000 requests in a single day.
- Test that the `ai_usage` table accurately records successful and failed requests.

## 7.2 UI Component Tests

- Test that the Generate button disables and shows a spinner when clicked.
- Test that the confirmation modal appears if the target field has text.
- Test that the hallucination warning banner appears after successful generation.

## 7.3 Error Handling Tests

- Mock the `AiProviderInterface` to throw `AiTimeoutException` and verify the correct UI error message is displayed.
- Mock the provider to throw `AiContentFilterException` and verify the correct UI error message is displayed.

---

# Document Completion Check

## Requirements Covered

- Reusable UI components for AI defined.
- Loading, error, and confirmation states defined.
- Internal application rate limiting strategy defined.
- AI usage tracking and quota management defined.
- Accessibility requirements for async operations defined.
- Testing requirements defined.

## Decisions Established

- AI UI uses standardized Blade/Livewire components.
- Overwriting existing text requires a confirmation modal.
- Rate limiting uses a 5-second cooldown, 50/day user quota, and 1000/day global quota.
- A dedicated `ai_usage` table is used for quota tracking and analytics, separate from `audit_logs`.
- AI errors are displayed inline near the trigger button, not as global toasts.

## Dependencies

- Approved Phase 05 AI Provider Adapter and Security.
- Approved Phase 05 AI Content Generation and Summarization.
- Approved Phase 02 RBAC and Caching strategies.

## Files Affected

This document creates:

05_AI_and_Advanced_Features_Architecture/03_AI_UI_and_Rate_Limiting.md

It dictates the implementation of AI UI components, rate limiting middleware/services, and the `ai_usage` database table.

## Open Questions

1. Should the daily quotas (50 user / 1000 global) be configurable via the Admin Settings UI, or hardcoded in config for the MVP?
2. Should administrators with the `ai.manage` permission be exempt from the daily user quota?

## TBD Decisions

- TBD: Final decision on quota configurability.
- TBD: Final decision on quota exemptions for Super Admins.

---

# Implementation Prompt

## Implementation Prompt ID

LABGATO-PHASE05-DOC03

## Context

LabGato requires strict UI patterns and rate limiting to ensure AI features are usable, accessible, and financially safe. This document defines the reusable UI components, error handling, internal rate limits, and usage tracking architecture.

This prompt implements the documentation outcome of the AI UI and Rate Limiting document.

## Objective

Create the official LabGato AI UI and Rate Limiting document inside the approved Phase 05 documentation structure.

## Existing Architecture

Relevant approved decisions:

- AI is an assistant, not an autopilot.
- Hostinger shared hosting requires database-backed caching/rate limiting.
- WCAG 2.2 AA requires proper live regions for async operations.

## Files to Create

Create:

05_AI_and_Advanced_Features_Architecture/03_AI_UI_and_Rate_Limiting.md

Use the approved document content from this documentation output.

## Definition of Done

This implementation is complete when:

- The AI UI and Rate Limiting file exists.
- The file matches the approved Phase 05 file tree.
- The file includes the document completion check.
- The file includes the implementation prompt.
- No unauthorized files were added.