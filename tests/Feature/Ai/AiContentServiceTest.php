<?php

namespace Tests\Feature\Ai;

use App\Exceptions\Ai\AiDisabledException;
use App\Exceptions\Ai\AiException;
use App\Exceptions\Ai\AiTimeoutException;
use App\Models\AiUsage;
use App\Models\User;
use App\Services\Ai\AiContentService;
use App\Support\AiResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Ai\Concerns\FakesAiProvider;
use Tests\TestCase;

class AiContentServiceTest extends TestCase
{
    use FakesAiProvider;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->enableAiFeature();
    }

    public function test_generate_returns_sanitized_content(): void
    {
        $this->fakeAiProvider(new AiResponse(
            content: "## Introduction\n\n**Safe** text.\n\n<script>alert('xss')</script>",
            provider: 'groq',
            model: 'test-model',
            promptTokens: 12,
            completionTokens: 8,
            latencyMs: 5,
        ));

        $response = app(AiContentService::class)->generate('draft_knowledge', [
            'title' => 'Hematology <b>Basics</b>',
            'outline' => 'RBC indices',
            'content' => 'Intro to hematology.',
        ]);

        $this->assertSame('groq', $response->provider);
        $this->assertSame(12, $response->promptTokens);
        $this->assertStringNotContainsString('<script', $response->content);
        $this->assertStringContainsString('Safe', $response->content);
    }

    public function test_generate_throws_when_feature_flag_is_disabled(): void
    {
        $this->fakeAiProvider(new AiResponse(
            content: 'Draft.',
            provider: 'groq',
            model: 'test-model',
            promptTokens: 1,
            completionTokens: 1,
            latencyMs: 1,
        ));

        config()->set('labgoto.features.ai', false);

        $this->expectException(AiDisabledException::class);

        app(AiContentService::class)->generate('draft_knowledge', ['title' => 'Hematology']);
    }

    public function test_generate_records_successful_usage(): void
    {
        $this->fakeAiProvider(new AiResponse(
            content: 'Draft.',
            provider: 'groq',
            model: 'test-model',
            promptTokens: 12,
            completionTokens: 8,
            latencyMs: 5,
        ));

        $user = User::factory()->create();

        app(AiContentService::class)->generate('draft_knowledge', [
            'title' => 'Hematology',
            'outline' => 'RBC indices',
        ], $user);

        $this->assertDatabaseHas('ai_usage', [
            'user_id' => $user->id,
            'action' => 'draft_knowledge',
            'provider' => 'groq',
            'model' => 'test-model',
            'prompt_tokens' => 12,
            'completion_tokens' => 8,
            'status' => 'success',
        ]);

        $this->assertSame(1, AiUsage::count());
    }

    public function test_generate_records_failed_usage_when_provider_throws(): void
    {
        $this->throwingAiProvider(AiTimeoutException::class);

        $user = User::factory()->create();

        try {
            app(AiContentService::class)->generate('summarize_sop', [
                'content' => str_repeat('procedure text ', 20),
            ], $user);
            $this->fail('Expected AiTimeoutException was not thrown.');
        } catch (AiTimeoutException $e) {
            $this->assertSame('provider failure', $e->getMessage());
        }

        $this->assertDatabaseHas('ai_usage', [
            'user_id' => $user->id,
            'action' => 'summarize_sop',
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'status' => 'failed',
        ]);
    }

    public function test_generate_rejects_unknown_use_case(): void
    {
        $this->fakeAiProvider(new AiResponse(
            content: 'Draft.',
            provider: 'groq',
            model: 'test-model',
            promptTokens: 1,
            completionTokens: 1,
            latencyMs: 1,
        ));

        $this->expectException(AiException::class);

        app(AiContentService::class)->generate('unknown_use_case', []);
    }
}
