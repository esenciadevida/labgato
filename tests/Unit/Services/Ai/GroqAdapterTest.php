<?php

namespace Tests\Unit\Services\Ai;

use App\Exceptions\Ai\AiAuthenticationException;
use App\Exceptions\Ai\AiRateLimitException;
use App\Exceptions\Ai\AiTimeoutException;
use App\Services\Ai\Providers\GroqAdapter;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GroqAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('labgoto.ai.providers.groq.api_key', 'test-api-key');
        config()->set('labgoto.ai.providers.groq.base_url', 'https://api.groq.com/openai/v1');
        config()->set('labgoto.ai.providers.groq.model', 'llama-3.1-70b-versatile');
    }

    public function test_groq_adapter_returns_ai_response_on_success(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'id' => 'chatcmpl-123',
                'model' => 'llama-3.1-70b-versatile',
                'choices' => [
                    [
                        'message' => ['role' => 'assistant', 'content' => 'Hello from Groq!'],
                        'finish_reason' => 'stop',
                    ],
                ],
                'usage' => [
                    'prompt_tokens' => 25,
                    'completion_tokens' => 12,
                ],
            ], 200),
        ]);

        $adapter = new GroqAdapter;

        $response = $adapter->generate([
            ['role' => 'system', 'content' => 'You are a helpful assistant.'],
            ['role' => 'user', 'content' => 'Say hello.'],
        ]);

        $this->assertSame('Hello from Groq!', $response->content);
        $this->assertSame('groq', $response->provider);
        $this->assertSame('llama-3.1-70b-versatile', $response->model);
        $this->assertSame(25, $response->promptTokens);
        $this->assertSame(12, $response->completionTokens);
        $this->assertGreaterThanOrEqual(0, $response->latencyMs);
    }

    public function test_groq_adapter_throws_timeout_exception_on_connection_timeout(): void
    {
        Http::fake(function () {
            throw new ConnectionException('Connection timed out');
        });

        $adapter = new GroqAdapter;

        $this->expectException(AiTimeoutException::class);

        $adapter->generate([
            ['role' => 'user', 'content' => 'Hello'],
        ]);
    }

    public function test_groq_adapter_throws_authentication_exception_on_401(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'error' => ['message' => 'Invalid API key'],
            ], 401),
        ]);

        $adapter = new GroqAdapter;

        $this->expectException(AiAuthenticationException::class);

        $adapter->generate([
            ['role' => 'user', 'content' => 'Hello'],
        ]);
    }

    public function test_groq_adapter_throws_rate_limit_exception_on_429(): void
    {
        Http::fake([
            'https://api.groq.com/openai/v1/chat/completions' => Http::response([
                'error' => ['message' => 'Rate limit exceeded'],
            ], 429),
        ]);

        $adapter = new GroqAdapter;

        $this->expectException(AiRateLimitException::class);

        $adapter->generate([
            ['role' => 'user', 'content' => 'Hello'],
        ]);
    }
}
