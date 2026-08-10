<?php

namespace Database\Factories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AuditLog>
 */
class AuditLogFactory extends Factory
{
    protected $model = AuditLog::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'action' => fake()->randomElement(['created', 'updated', 'deleted', 'login', 'logout']),
            'resource_type' => fake()->randomElement(['App\\Models\\User', 'App\\Models\\Setting', null]),
            'resource_id' => fake()->optional()->numberBetween(1, 100),
            'changes' => fake()->optional()->passthrough(['name' => ['old' => 'Old Name', 'new' => 'New Name']]),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => fake()->dateTimeThisYear(),
        ];
    }
}
