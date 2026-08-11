<?php

namespace Tests\Feature\Admin\Calculators;

use App\Models\Calculator;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Calculators\Engines\AnionGapCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorAdminValidationTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermission(string $permissionSlug): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();
        $permission = Permission::factory()->create(['slug' => $permissionSlug]);
        $role->permissions()->attach($permission);
        $user->roles()->attach($role);

        return $user;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Anion Gap Calculator',
            'slug' => 'anion-gap-calculator',
            'short_description' => 'Calculates the serum anion gap.',
            'formula_display' => 'Sodium - (Chloride + Bicarbonate)',
            'status' => 'draft',
            'input_schema' => json_encode([
                ['key' => 'sodium', 'label' => 'Serum Sodium', 'type' => 'number', 'required' => true],
                ['key' => 'chloride', 'label' => 'Serum Chloride', 'type' => 'number', 'required' => true],
                ['key' => 'bicarbonate', 'label' => 'Serum Bicarbonate', 'type' => 'number', 'required' => true],
            ]),
            'output_schema' => json_encode([
                ['key' => 'anion_gap', 'label' => 'Anion Gap', 'rounding' => 'round_half_up', 'precision' => 1],
            ]),
            'engine' => 'php_class',
            'engine_reference' => AnionGapCalculator::class,
            'categories' => [],
            'tags' => [],
        ], $overrides);
    }

    private function createUser(): User
    {
        return $this->createUserWithPermission('calculators.create');
    }

    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.calculators.store'),
            $this->validPayload(['name' => '', 'slug' => ''])
        );

        $response->assertSessionHasErrors('name');
    }

    public function test_slug_must_be_unique(): void
    {
        Calculator::factory()->create(['slug' => 'anion-gap-calculator']);
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(
            route('admin.calculators.store'),
            $this->validPayload(['name' => 'Another Calculator', 'slug' => 'anion-gap-calculator'])
        );

        $response->assertSessionHasErrors('slug');
    }

    public function test_input_schema_must_be_valid_json(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.calculators.store'),
            $this->validPayload(['input_schema' => 'not-json'])
        );

        $response->assertSessionHasErrors('input_schema');
    }

    public function test_output_schema_must_be_valid_json(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.calculators.store'),
            $this->validPayload(['output_schema' => 'not-json'])
        );

        $response->assertSessionHasErrors('output_schema');
    }

    public function test_input_schema_requires_fields(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.calculators.store'),
            $this->validPayload([
                'input_schema' => json_encode([
                    ['key' => 'sodium', 'type' => 'number'],
                ]),
            ])
        );

        $response->assertSessionHasErrors('input_schema');
    }

    public function test_output_schema_requires_fields(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.calculators.store'),
            $this->validPayload([
                'output_schema' => json_encode([
                    ['key' => 'anion_gap'],
                ]),
            ])
        );

        $response->assertSessionHasErrors('output_schema');
    }

    public function test_engine_reference_must_be_registered(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.calculators.store'),
            $this->validPayload(['engine_reference' => 'App\\Models\\User'])
        );

        $response->assertSessionHasErrors('engine_reference');
    }

    public function test_status_must_be_valid(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.calculators.store'),
            $this->validPayload(['status' => 'not-a-real-status'])
        );

        $response->assertSessionHasErrors('status');
    }
}
