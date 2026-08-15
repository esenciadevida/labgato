<?php

namespace App\Services\Ai;

use App\Exceptions\Ai\AiContentFilterException;
use App\Exceptions\Ai\AiDisabledException;
use App\Exceptions\Ai\AiException;
use App\Models\AiUsage;
use App\Models\User;
use App\Services\Ai\UseCases\AiUseCaseInterface;
use App\Services\Ai\UseCases\DraftClinicalContext;
use App\Services\Ai\UseCases\DraftKnowledgeArticle;
use App\Services\Ai\UseCases\SummarizeSopProcedure;
use App\Support\AiResponse;

class AiContentService
{
    /**
     * Map of use case keys to their implementations.
     *
     * @var array<string, class-string<AiUseCaseInterface>>
     */
    private const USE_CASES = [
        'draft_knowledge' => DraftKnowledgeArticle::class,
        'draft_clinical_context' => DraftClinicalContext::class,
        'summarize_sop' => SummarizeSopProcedure::class,
    ];

    public function __construct(
        private readonly AiRateLimiterService $rateLimiter,
        private readonly AiProviderService $providerService,
    ) {}

    /**
     * @param  array{title?: string, outline?: string, content?: string}  $input
     */
    public function generate(string $useCaseKey, array $input, ?User $user = null): AiResponse
    {
        if (! (bool) config('labgoto.features.ai', false)) {
            throw new AiDisabledException('AI features are currently disabled by the administrator.');
        }

        $useCase = $this->resolveUseCase($useCaseKey);

        $this->rateLimiter->assertWithinLimits($user);
        $this->rateLimiter->hitCooldown($user);

        try {
            $response = $useCase->generate($input);
            $this->record($useCase, $user, $response, 'success');

            return $response;
        } catch (AiException $e) {
            $status = $e instanceof AiContentFilterException ? 'filtered' : 'failed';
            $this->record($useCase, $user, null, $status);

            throw $e;
        }
    }

    private function resolveUseCase(string $key): AiUseCaseInterface
    {
        $class = self::USE_CASES[$key] ?? null;

        if ($class === null) {
            throw new AiException("The AI use case '{$key}' is not supported.");
        }

        return app($class);
    }

    private function record(AiUseCaseInterface $useCase, ?User $user, ?AiResponse $response, string $status): void
    {
        AiUsage::create([
            'user_id' => $user?->id,
            'action' => $useCase->actionName(),
            'provider' => $response?->provider ?? (string) config('labgoto.ai.provider', ''),
            'model' => $response?->model ?? (string) config('labgoto.ai.providers.groq.model', ''),
            'prompt_tokens' => $response?->promptTokens ?? 0,
            'completion_tokens' => $response?->completionTokens ?? 0,
            'latency_ms' => $response?->latencyMs ?? 0,
            'status' => $status,
        ]);
    }
}
