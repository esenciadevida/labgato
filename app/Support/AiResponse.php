<?php

namespace App\Support;

class AiResponse
{
    public function __construct(
        public readonly string $content,
        public readonly string $provider,
        public readonly string $model,
        public readonly int $promptTokens,
        public readonly int $completionTokens,
        public readonly int $latencyMs,
        public readonly array $rawMetadata = [],
    ) {}
}
