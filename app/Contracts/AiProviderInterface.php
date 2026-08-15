<?php

namespace App\Contracts;

use App\Exceptions\Ai\AiException;
use App\Support\AiResponse;

interface AiProviderInterface
{
    /**
     * Generate content from the AI provider.
     *
     * @param  array  $messages  Array of message arrays: [['role' => 'system'|'user', 'content' => '...']]
     * @param  array  $options  Optional provider-specific overrides (e.g., max_tokens, temperature)
     *
     * @throws AiException
     */
    public function generate(array $messages, array $options = []): AiResponse;
}
