<?php

namespace Database\Factories;

use App\Models\AiUsage;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AiUsage>
 */
class AiUsageFactory extends Factory
{
    protected $model = AiUsage::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['generate', 'summarize', 'explain', 'chat']),
            'provider' => fake()->randomElement(['groq', 'openai']),
            'model' => fake()->randomElement(['llama-3.1-8b-instant', 'gpt-4o-mini']),
            'prompt_tokens' => fake()->numberBetween(10, 500),
            'completion_tokens' => fake()->numberBetween(10, 200),
            'latency_ms' => fake()->numberBetween(100, 5000),
            'status' => fake()->randomElement(['success', 'failed', 'filtered']),
            'created_at' => now(),
        ];
    }

    public function success(): static
    {
        return $this->state(fn () => [
            'status' => 'success',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
        ]);
    }

    public function filtered(): static
    {
        return $this->state(fn () => [
            'status' => 'filtered',
        ]);
    }
}
