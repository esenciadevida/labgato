labgato/
├── app/
│   ├── Contracts/
│   │   └── AiProviderInterface.php
│   ├── Exceptions/
│   │   └── Ai/
│   │       ├── AiException.php
│   │       ├── AiDisabledException.php
│   │       ├── AiProviderNotConfiguredException.php
│   │       ├── AiProviderNotFoundException.php
│   │       ├── AiTimeoutException.php
│   │       ├── AiRateLimitException.php
│   │       ├── AiAuthenticationException.php
│   │       └── AiContentFilterException.php
│   ├── Services/
│   │   └── Ai/
│   │       ├── AiProviderService.php
│   │       └── Providers/
│   │           └── GroqAdapter.php
│   └── Support/
│       └── AiResponse.php
├── config/
│   └── labgato.php
├── database/
│   └── seeders/
│       └── RolePermissionSeeder.php
├── .env.example
└── tests/
    └── Unit/
        └── Services/
            └── Ai/
                ├── GroqAdapterTest.php
                └── AiProviderServiceTest.php

IMPLEMENTATION PROMPT ID: LABGATO-PHASE05-BUILD17

CONTEXT:
LabGato MVP is complete and hardened. Phase 05 AI & Advanced Features Architecture is approved.

We are now implementing Build 17: AI Foundation & Security (Slice 7).

This build creates the core AI adapter layer, exceptions, and configuration. It does NOT create the AI UI, the ai_usage database table, or the rate limiting middleware. Those are scheduled for Build 18.

OBJECTIVE:
Implement the provider-agnostic AI adapter layer, the Groq HTTP client, the AI exception hierarchy, the configuration boundaries, and the AI permissions.

EXISTING ARCHITECTURE:
Relevant approved decisions:
- AI is disabled by default (AI_ENABLED=false).
- Groq is the initial provider, using an OpenAI-compatible API.
- The adapter pattern must isolate provider-specific logic.
- AI responses must use a standardized AiResponse DTO.
- Timeouts must be strictly enforced (default 15 seconds).
- Prompt injection and data leakage are prevented via system prompts and sanitization (handled in Build 18, but the adapter must support passing structured messages).

FILES TO INSPECT:
- config/labgato.php
- .env.example
- database/seeders/RolePermissionSeeder.php
- app/Contracts/ directory
- app/Services/ directory
- app/Exceptions/ directory

FILES TO CREATE:
- app/Contracts/AiProviderInterface.php
- app/Support/AiResponse.php
- app/Exceptions/Ai/AiException.php
- app/Exceptions/Ai/AiDisabledException.php
- app/Exceptions/Ai/AiProviderNotConfiguredException.php
- app/Exceptions/Ai/AiProviderNotFoundException.php
- app/Exceptions/Ai/AiTimeoutException.php
- app/Exceptions/Ai/AiRateLimitException.php
- app/Exceptions/Ai/AiAuthenticationException.php
- app/Exceptions/Ai/AiContentFilterException.php
- app/Services/Ai/Providers/GroqAdapter.php
- app/Services/Ai/AiProviderService.php
- tests/Unit/Services/Ai/GroqAdapterTest.php
- tests/Unit/Services/Ai/AiProviderServiceTest.php

FILES TO MODIFY:
- config/labgato.php
- .env.example
- database/seeders/RolePermissionSeeder.php

TASK 1: CONFIGURATION & ENVIRONMENT

Modify config/labgato.php to include AI configuration:

'ai' => [
    'enabled' => env('AI_ENABLED', false),
    'provider' => env('AI_PROVIDER', ''),
    'timeout_seconds' => env('AI_TIMEOUT_SECONDS', 15),
    'max_tokens' => env('AI_MAX_TOKENS', 2048),
    'temperature' => env('AI_TEMPERATURE', 0.7),
    'providers' => [
        'groq' => [
            'api_key' => env('GROQ_API_KEY', ''),
            'model' => env('GROQ_MODEL', 'llama-3.1-70b-versatile'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        ],
    ],
],

Modify .env.example to include:

AI_ENABLED=false
AI_PROVIDER=
GROQ_API_KEY=
GROQ_MODEL=llama-3.1-70b-versatile
AI_TIMEOUT_SECONDS=15
AI_MAX_TOKENS=2048
AI_TEMPERATURE=0.7

FEATURE_AI=false

TASK 2: AI EXCEPTION HIERARCHY

Create the base exception and specific exceptions in app/Exceptions/Ai/.

1. AiException (base class, extends \Exception)
2. AiDisabledException
3. AiProviderNotConfiguredException
4. AiProviderNotFoundException
5. AiTimeoutException
6. AiRateLimitException
7. AiAuthenticationException
8. AiContentFilterException

Rules:
- All AI exceptions must extend AiException.
- Do not expose sensitive configuration details in exception messages.

TASK 3: CONTRACTS & DATA TRANSFER OBJECTS

Create app/Contracts/AiProviderInterface.php:

interface AiProviderInterface
{
    /**
     * Generate content from the AI provider.
     *
     * @param array $messages Array of message arrays: [['role' => 'system'|'user', 'content' => '...']]
     * @param array $options Optional provider-specific overrides (e.g., max_tokens, temperature)
     * @return \App\Support\AiResponse
     * @throws \App\Exceptions\Ai\AiException
     */
    public function generate(array $messages, array $options = []): AiResponse;
}

Create app/Support/AiResponse.php:

class AiResponse
{
    public function __construct(
        public readonly string $content,
        public readonly string $provider,
        public readonly string $model,
        public readonly int $promptTokens,
        public readonly int $completionTokens,
        public readonly int $latencyMs,
        public readonly array $rawMetadata = []
    ) {}
}

TASK 4: GROQ ADAPTER IMPLEMENTATION

Create app/Services/Ai/Providers/GroqAdapter.php.

Responsibilities:
- Implement AiProviderInterface.
- Use Laravel's Http facade to make POST requests to {base_url}/chat/completions.
- Set Authorization header: Bearer {api_key}.
- Set timeout: config('labgato.ai.timeout_seconds').
- Construct the JSON payload: model, messages, max_tokens, temperature.
- Parse the JSON response.
- Calculate latency.
- Return an AiResponse object.
- Map HTTP errors and API errors to specific AI exceptions.

Error Mapping Rules:
- Connection Timeout -> AiTimeoutException
- HTTP 401/403 -> AiAuthenticationException
- HTTP 429 -> AiRateLimitException
- HTTP 400 (content filter) -> AiContentFilterException
- Other HTTP errors -> AiException

TASK 5: AI PROVIDER SERVICE

Create app/Services/Ai/AiProviderService.php.

Responsibilities:
- Resolve the active AI provider based on configuration.
- Throw AiDisabledException if AI_ENABLED is false.
- Throw AiProviderNotConfiguredException if AI_PROVIDER is empty.
- Throw AiProviderNotFoundException if AI_PROVIDER is unknown.
- Return the GroqAdapter if AI_PROVIDER is 'groq'.
- Register this service as a singleton in AppServiceProvider.

TASK 6: AI PERMISSIONS

Modify database/seeders/RolePermissionSeeder.php.

Add the following permissions:
- ai.use
- ai.manage

Assign ai.use and ai.manage to the Super Administrator role.
Do not assign AI permissions to Content Manager or Viewer/Analyst roles for the MVP.

TASK 7: UNIT TESTS

Create tests/Unit/Services/Ai/GroqAdapterTest.php.

Required tests:
1. test_groq_adapter_returns_ai_response_on_success
   - Mock the Http facade to return a successful Groq API response.
   - Call generate() with a simple message.
   - Assert the returned AiResponse has the correct content, provider, model, and token counts.
2. test_groq_adapter_throws_timeout_exception_on_connection_timeout
   - Mock the Http facade to throw a ConnectionException.
   - Assert AiTimeoutException is thrown.
3. test_groq_adapter_throws_authentication_exception_on_401
   - Mock the Http facade to return a 401 response.
   - Assert AiAuthenticationException is thrown.
4. test_groq_adapter_throws_rate_limit_exception_on_429
   - Mock the Http facade to return a 429 response.
   - Assert AiRateLimitException is thrown.

Create tests/Unit/Services/Ai/AiProviderServiceTest.php.

Required tests:
1. test_service_throws_disabled_exception_when_ai_is_disabled
   - Set config labgato.ai.enabled to false.
   - Assert AiDisabledException is thrown when resolving the provider.
2. test_service_throws_not_configured_exception_when_provider_is_empty
   - Set config labgato.ai.enabled to true, provider to ''.
   - Assert AiProviderNotConfiguredException is thrown.
3. test_service_resolves_groq_adapter_when_provider_is_groq
   - Set config labgato.ai.enabled to true, provider to 'groq'.
   - Assert the resolved service is an instance of GroqAdapter.

SECURITY REQUIREMENTS:
- Do not log API keys.
- Do not expose API keys in exception messages.
- Do not expose raw request payloads in AiResponse.
- Ensure HTTP client verifies SSL certificates.

DEFINITION OF DONE:
- AiProviderInterface and AiResponse are implemented.
- GroqAdapter is implemented with strict timeouts and error mapping.
- AiProviderService resolves the correct adapter and throws appropriate exceptions.
- AI configuration is added to config/labgato.php and .env.example.
- ai.use and ai.manage permissions are seeded.
- Unit tests for the adapter and service pass.
- No UI or database tracking is implemented in this build.