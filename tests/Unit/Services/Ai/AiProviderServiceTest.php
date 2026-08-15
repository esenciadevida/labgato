<?php

namespace Tests\Unit\Services\Ai;

use App\Exceptions\Ai\AiDisabledException;
use App\Exceptions\Ai\AiProviderNotConfiguredException;
use App\Exceptions\Ai\AiProviderNotFoundException;
use App\Services\Ai\AiProviderService;
use App\Services\Ai\Providers\GroqAdapter;
use Tests\TestCase;

class AiProviderServiceTest extends TestCase
{
    public function test_service_throws_disabled_exception_when_ai_is_disabled(): void
    {
        config()->set('labgoto.ai.enabled', false);

        $service = app(AiProviderService::class);

        $this->expectException(AiDisabledException::class);

        $service->provider();
    }

    public function test_service_throws_not_configured_exception_when_provider_is_empty(): void
    {
        config()->set('labgoto.ai.enabled', true);
        config()->set('labgoto.ai.provider', '');

        $service = app(AiProviderService::class);

        $this->expectException(AiProviderNotConfiguredException::class);

        $service->provider();
    }

    public function test_service_throws_not_found_exception_when_provider_is_unknown(): void
    {
        config()->set('labgoto.ai.enabled', true);
        config()->set('labgoto.ai.provider', 'unknown-provider');

        $service = app(AiProviderService::class);

        $this->expectException(AiProviderNotFoundException::class);

        $service->provider();
    }

    public function test_service_resolves_groq_adapter_when_provider_is_groq(): void
    {
        config()->set('labgoto.ai.enabled', true);
        config()->set('labgoto.ai.provider', 'groq');

        $service = app(AiProviderService::class);

        $provider = $service->provider();

        $this->assertInstanceOf(GroqAdapter::class, $provider);
    }
}
