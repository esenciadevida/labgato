<?php

namespace Tests\Feature\Ai\Concerns;

use App\Contracts\AiProviderInterface;
use App\Services\Ai\AiProviderService;
use App\Support\AiResponse;
use Mockery\MockInterface;

trait FakesAiProvider
{
    private function enableAiFeature(): void
    {
        config()->set('labgoto.features.ai', true);
        config()->set('labgoto.ai.enabled', true);
        config()->set('labgoto.ai.provider', 'groq');
        config()->set('labgoto.ai.limits.cooldown_seconds', 0);
        config()->set('labgoto.ai.limits.daily_user', 50);
        config()->set('labgoto.ai.limits.daily_global', 1000);
    }

    private function fakeAiProvider(AiResponse $response): void
    {
        $this->mock(AiProviderService::class, function (MockInterface $mock) use ($response) {
            $mock->shouldReceive('provider')->andReturn(
                new class($response) implements AiProviderInterface
                {
                    public function __construct(private readonly AiResponse $response) {}

                    public function generate(array $messages, array $options = []): AiResponse
                    {
                        return $this->response;
                    }
                }
            );
        });
    }

    private function throwingAiProvider(string $exceptionClass): void
    {
        $this->mock(AiProviderService::class, function (MockInterface $mock) use ($exceptionClass) {
            $mock->shouldReceive('provider')->andReturn(
                new class($exceptionClass) implements AiProviderInterface
                {
                    public function __construct(private readonly string $exceptionClass) {}

                    public function generate(array $messages, array $options = []): AiResponse
                    {
                        $class = $this->exceptionClass;

                        throw new $class('provider failure');
                    }
                }
            );
        });
    }
}
