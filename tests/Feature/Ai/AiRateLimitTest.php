<?php

namespace Tests\Feature\Ai;

use App\Exceptions\Ai\AiRateLimitException;
use App\Models\AiUsage;
use App\Models\User;
use App\Services\Ai\AiContentService;
use App\Support\AiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Ai\Concerns\FakesAiProvider;
use Tests\TestCase;

class AiRateLimitTest extends TestCase
{
    use FakesAiProvider;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableAiFeature();
    }

    private function user(): User
    {
        return User::factory()->create();
    }

    private function assertBlocks(callable $callback, string $message = 'You have reached your AI usage limit for today. Please try again tomorrow.'): void
    {
        try {
            $callback();
            $this->fail('Expected AiRateLimitException was not thrown.');
        } catch (AiRateLimitException $e) {
            $this->assertSame($message, $e->getMessage());
        }
    }

    public function test_user_cannot_trigger_two_requests_within_cooldown(): void
    {
        config()->set('labgoto.ai.limits.cooldown_seconds', 5);

        $this->fakeAiProvider(new AiResponse(
            content: 'Draft.',
            provider: 'groq',
            model: 'test-model',
            promptTokens: 1,
            completionTokens: 1,
            latencyMs: 1,
        ));

        $user = $this->user();
        $service = app(AiContentService::class);

        $service->generate('draft_knowledge', ['title' => 'Hematology'], $user);

        $this->assertBlocks(
            fn () => $service->generate('draft_knowledge', ['title' => 'Hematology'], $user),
            'You must wait a few seconds before generating again.'
        );
    }

    public function test_user_is_blocked_after_daily_user_quota(): void
    {
        config()->set('labgoto.ai.limits.daily_user', 2);

        $this->fakeAiProvider(new AiResponse(
            content: 'Draft.',
            provider: 'groq',
            model: 'test-model',
            promptTokens: 1,
            completionTokens: 1,
            latencyMs: 1,
        ));

        $user = $this->user();
        $service = app(AiContentService::class);

        $service->generate('draft_knowledge', ['title' => 'One'], $user);
        $service->generate('draft_knowledge', ['title' => 'Two'], $user);

        $this->assertBlocks(fn () => $service->generate('draft_knowledge', ['title' => 'Three'], $user));
    }

    public function test_platform_is_blocked_after_daily_global_quota(): void
    {
        config()->set('labgoto.ai.limits.daily_global', 2);

        $this->fakeAiProvider(new AiResponse(
            content: 'Draft.',
            provider: 'groq',
            model: 'test-model',
            promptTokens: 1,
            completionTokens: 1,
            latencyMs: 1,
        ));

        $service = app(AiContentService::class);

        $service->generate('draft_knowledge', ['title' => 'One'], $this->user());
        $service->generate('draft_knowledge', ['title' => 'Two'], $this->user());

        $this->assertBlocks(
            fn () => $service->generate('draft_knowledge', ['title' => 'Three'], $this->user()),
            'The platform AI usage limit for today has been reached.'
        );
    }

    public function test_user_quota_is_tracked_per_user(): void
    {
        config()->set('labgoto.ai.limits.daily_user', 1);

        $this->fakeAiProvider(new AiResponse(
            content: 'Draft.',
            provider: 'groq',
            model: 'test-model',
            promptTokens: 1,
            completionTokens: 1,
            latencyMs: 1,
        ));

        $service = app(AiContentService::class);
        $userA = $this->user();
        $userB = $this->user();

        $service->generate('draft_knowledge', ['title' => 'A'], $userA);

        $this->assertBlocks(fn () => $service->generate('draft_knowledge', ['title' => 'A again'], $userA));

        $service->generate('draft_knowledge', ['title' => 'B'], $userB);
        $this->assertSame(2, AiUsage::count());
    }
}
