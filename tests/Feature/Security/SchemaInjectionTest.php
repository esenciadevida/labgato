<?php

namespace Tests\Feature\Security;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Calculators\Engines\McvCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchemaInjectionTest extends TestCase
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
            'name' => 'MCV Calculator',
            'slug' => 'mcv-calculator',
            'short_description' => 'Calculates the mean corpuscular volume.',
            'status' => 'draft',
            'input_schema' => json_encode([
                ['key' => 'hematocrit', 'label' => 'Hematocrit', 'type' => 'number'],
            ]),
            'output_schema' => json_encode([
                ['key' => 'mcv', 'label' => 'MCV', 'rounding' => 'round_half_up', 'precision' => 1],
            ]),
            'engine' => 'php_class',
            'engine_reference' => McvCalculator::class,
            'categories' => [],
            'tags' => [],
        ], $overrides);
    }

    public function test_calculator_schema_rejects_massive_json_payload(): void
    {
        $huge = '['.str_repeat(' ', 70000).']';

        $response = $this->actingAs($this->createUserWithPermission('calculators.create'))
            ->post(route('admin.calculators.store'), $this->validPayload(['input_schema' => $huge]));

        $response->assertSessionHasErrors('input_schema');
    }

    public function test_calculator_schema_rejects_invalid_keys(): void
    {
        $response = $this->actingAs($this->createUserWithPermission('calculators.create'))
            ->post(route('admin.calculators.store'), $this->validPayload([
                'input_schema' => json_encode([
                    ['key' => 'bad key!', 'label' => 'Bad', 'type' => 'number'],
                ]),
            ]));

        $response->assertSessionHasErrors('input_schema');
    }

    public function test_calculator_output_schema_rejects_invalid_keys(): void
    {
        $response = $this->actingAs($this->createUserWithPermission('calculators.create'))
            ->post(route('admin.calculators.store'), $this->validPayload([
                'output_schema' => json_encode([
                    ['key' => 'AnionGap', 'label' => 'Anion Gap', 'rounding' => 'round_half_up', 'precision' => 1],
                ]),
            ]));

        $response->assertSessionHasErrors('output_schema');
    }

    public function test_calculator_schema_rejects_non_array_json(): void
    {
        $response = $this->actingAs($this->createUserWithPermission('calculators.create'))
            ->post(route('admin.calculators.store'), $this->validPayload([
                'input_schema' => '{"sodium":{"label":"Sodium","type":"number"}}',
            ]));

        $response->assertSessionHasErrors('input_schema');
    }

    public function test_calculator_schema_rejects_duplicate_keys(): void
    {
        $response = $this->actingAs($this->createUserWithPermission('calculators.create'))
            ->post(route('admin.calculators.store'), $this->validPayload([
                'input_schema' => json_encode([
                    ['key' => 'sodium', 'label' => 'First', 'type' => 'number'],
                    ['key' => 'sodium', 'label' => 'Second', 'type' => 'number'],
                ]),
            ]));

        $response->assertSessionHasErrors('input_schema');
    }

    public function test_calculator_schema_rejects_too_many_fields(): void
    {
        $fields = [];

        for ($i = 0; $i < 51; $i++) {
            $fields[] = ['key' => 'field_'.$i, 'label' => 'Field '.$i, 'type' => 'number'];
        }

        $response = $this->actingAs($this->createUserWithPermission('calculators.create'))
            ->post(route('admin.calculators.store'), $this->validPayload([
                'input_schema' => json_encode($fields),
            ]));

        $response->assertSessionHasErrors('input_schema');
    }

    public function test_calculator_schema_rejects_excessive_nesting(): void
    {
        $nested = ['value' => 'x'];

        for ($i = 0; $i < 20; $i++) {
            $nested = ['options' => [$nested]];
        }

        $response = $this->actingAs($this->createUserWithPermission('calculators.create'))
            ->post(route('admin.calculators.store'), $this->validPayload([
                'input_schema' => json_encode([
                    ['key' => 'deep', 'label' => 'Deep', 'type' => 'select', 'options' => [$nested]],
                ]),
            ]));

        $response->assertSessionHasErrors('input_schema');
    }

    public function test_calculator_schema_accepts_valid_schema(): void
    {
        $response = $this->actingAs($this->createUserWithPermission('calculators.create'))
            ->post(route('admin.calculators.store'), $this->validPayload());

        $response->assertRedirect(route('admin.calculators.index'));
        $this->assertDatabaseHas('calculators', ['slug' => 'mcv-calculator']);
    }
}
