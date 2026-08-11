<?php

namespace Tests\Feature\Admin\Calculators;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Calculators\Engines\AnionGapCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculatorAdminCrudTest extends TestCase
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
            'status' => ResourceStatus::DRAFT->value,
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
            'interpretation_notes' => 'Interpretation notes.',
            'references' => 'Clinical reference.',
            'categories' => [],
            'tags' => [],
            'seo_title' => 'Anion Gap Calculator',
            'seo_description' => 'Clinical anion gap calculator.',
        ], $overrides);
    }

    public function test_guest_cannot_access_calculator_admin(): void
    {
        $response = $this->get(route('admin.calculators.index'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_user_without_calculator_permission_cannot_access_calculator_admin(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.calculators.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_calculator_view_can_view_index(): void
    {
        $user = $this->createUserWithPermission('calculators.view');

        $response = $this->actingAs($user)->get(route('admin.calculators.index'));

        $response->assertStatus(200);
        $response->assertSee('Calculators');
    }

    public function test_user_with_calculator_create_can_create_calculator(): void
    {
        $user = $this->createUserWithPermission('calculators.create');

        $response = $this->actingAs($user)->post(route('admin.calculators.store'), $this->validPayload());

        $response->assertRedirect(route('admin.calculators.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('calculators', [
            'name' => 'Anion Gap Calculator',
            'slug' => 'anion-gap-calculator',
            'status' => ResourceStatus::DRAFT->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_user_with_calculator_edit_can_update_calculator(): void
    {
        $calculator = Calculator::factory()->create();
        $user = $this->createUserWithPermission('calculators.edit');

        $response = $this->actingAs($user)->put(route('admin.calculators.update', $calculator), $this->validPayload([
            'name' => 'Updated Calculator',
            'slug' => 'updated-calculator',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $response->assertRedirect(route('admin.calculators.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('calculators', [
            'id' => $calculator->id,
            'name' => 'Updated Calculator',
            'slug' => 'updated-calculator',
            'updated_by' => $user->id,
        ]);
    }

    public function test_user_with_calculator_delete_can_delete_calculator(): void
    {
        $calculator = Calculator::factory()->create();
        $user = $this->createUserWithPermission('calculators.delete');

        $response = $this->actingAs($user)->delete(route('admin.calculators.destroy', $calculator));

        $response->assertRedirect(route('admin.calculators.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('calculators', ['id' => $calculator->id]);
    }

    public function test_calculator_creation_is_audited(): void
    {
        $user = $this->createUserWithPermission('calculators.create');

        $this->actingAs($user)->post(route('admin.calculators.store'), $this->validPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'calculators.created',
            'resource_type' => Calculator::class,
        ]);
    }

    public function test_calculator_update_is_audited(): void
    {
        $calculator = Calculator::factory()->create();
        $user = $this->createUserWithPermission('calculators.edit');

        $this->actingAs($user)->put(route('admin.calculators.update', $calculator), $this->validPayload([
            'name' => 'Updated Calculator',
            'slug' => 'updated-calculator',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'calculators.updated',
            'resource_type' => Calculator::class,
            'resource_id' => $calculator->id,
        ]);
    }
}
