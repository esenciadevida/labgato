<?php

namespace Database\Factories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    protected $model = Permission::class;

    public function definition(): array
    {
        $module = fake()->word();
        $action = fake()->randomElement(['view', 'create', 'edit', 'delete', 'manage']);
        $name = "{$module}.{$action}";

        return [
            'name' => ucfirst($module) . ' ' . ucfirst($action),
            'slug' => $name,
            'group' => Str::headline($module),
        ];
    }
}
