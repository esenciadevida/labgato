<?php

namespace App\Services\Ai;

use App\Contracts\AiProviderInterface;
use App\Exceptions\Ai\AiDisabledException;
use App\Exceptions\Ai\AiProviderNotConfiguredException;
use App\Exceptions\Ai\AiProviderNotFoundException;
use App\Services\Ai\Providers\GroqAdapter;

class AiProviderService
{
    /**
     * Map of configured provider keys to their adapter implementations.
     *
     * @var array<string, class-string<AiProviderInterface>>
     */
    private const PROVIDERS = [
        'groq' => GroqAdapter::class,
    ];

    public function provider(): AiProviderInterface
    {
        if (! (bool) config('labgoto.ai.enabled')) {
            throw new AiDisabledException('AI features are currently disabled.');
        }

        $providerName = (string) config('labgoto.ai.provider');

        if ($providerName === '') {
            throw new AiProviderNotConfiguredException('The AI provider is not configured.');
        }

        $adapterClass = self::PROVIDERS[$providerName] ?? null;

        if ($adapterClass === null) {
            throw new AiProviderNotFoundException("The AI provider '{$providerName}' is not supported.");
        }

        return app($adapterClass);
    }
}
