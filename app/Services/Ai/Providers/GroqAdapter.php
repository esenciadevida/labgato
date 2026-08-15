<?php

namespace App\Services\Ai\Providers;

use App\Contracts\AiProviderInterface;
use App\Exceptions\Ai\AiAuthenticationException;
use App\Exceptions\Ai\AiContentFilterException;
use App\Exceptions\Ai\AiException;
use App\Exceptions\Ai\AiRateLimitException;
use App\Exceptions\Ai\AiTimeoutException;
use App\Support\AiResponse;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class GroqAdapter implements AiProviderInterface
{
    public function generate(array $messages, array $options = []): AiResponse
    {
        $apiKey = (string) config('labgoto.ai.providers.groq.api_key');
        $model = (string) ($options['model'] ?? config('labgoto.ai.providers.groq.model'));
        $maxTokens = (int) ($options['max_tokens'] ?? config('labgoto.ai.max_tokens'));
        $temperature = (float) ($options['temperature'] ?? config('labgoto.ai.temperature'));
        $timeoutSeconds = (int) config('labgoto.ai.timeout_seconds');

        $start = hrtime(true);

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout($timeoutSeconds)
                ->connectTimeout(5)
                ->post(rtrim((string) config('labgoto.ai.providers.groq.base_url'), '/').'/chat/completions', [
                    'model' => $model,
                    'messages' => $messages,
                    'max_tokens' => $maxTokens,
                    'temperature' => $temperature,
                ]);
        } catch (ConnectionException $e) {
            throw new AiTimeoutException('The AI service timed out while connecting.', 0, $e);
        }

        $latencyMs = (int) (hrtime(true) - $start) / 1_000_000;

        if ($response->failed()) {
            $this->handleErrorResponse($response);
        }

        $data = $response->json();

        $content = $data['choices'][0]['message']['content'] ?? '';
        $usage = $data['usage'] ?? [];

        return new AiResponse(
            content: is_string($content) ? $content : '',
            provider: 'groq',
            model: (string) ($data['model'] ?? $model),
            promptTokens: (int) ($usage['prompt_tokens'] ?? 0),
            completionTokens: (int) ($usage['completion_tokens'] ?? 0),
            latencyMs: $latencyMs,
            rawMetadata: array_filter([
                'finish_reason' => $data['choices'][0]['finish_reason'] ?? null,
                'id' => $data['id'] ?? null,
            ]),
        );
    }

    private function handleErrorResponse(Response $response): void
    {
        $status = $response->status();

        if ($status === 401 || $status === 403) {
            throw new AiAuthenticationException('The AI provider rejected the request credentials.');
        }

        if ($status === 429) {
            throw new AiRateLimitException('The AI provider rate limit was exceeded.');
        }

        if ($status === 400) {
            throw new AiContentFilterException('The AI provider rejected the request content.');
        }

        throw new AiException('The AI provider returned an unexpected error.');
    }
}
