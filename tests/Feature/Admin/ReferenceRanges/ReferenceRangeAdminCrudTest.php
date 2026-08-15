<?php

namespace Tests\Feature\Admin\ReferenceRanges;

use App\Enums\ResourceStatus;
use App\Models\Permission;
use App\Models\ReferenceRange;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceRangeAdminCrudTest extends TestCase
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
            'name' => 'Hemoglobin - Adult Male',
            'slug' => 'hemoglobin-adult-male',
            'short_description' => 'Reference range for adult males.',
            'analyte_name' => 'Hemoglobin',
            'specimen_type' => 'Whole Blood',
            'methodology' => '',
            'population_group' => 'Adult Male',
            'age_min' => '18',
            'age_max' => '65',
            'sex' => 'male',
            'unit' => 'g/dL',
            'lower_limit' => '13.0',
            'upper_limit' => '17.0',
            'critical_low' => '7.0',
            'critical_high' => '22.0',
            'conversion_factor' => '10',
            'source_citation' => 'Clinical reference.',
            'effective_date' => '2026-01-01',
            'status' => ResourceStatus::DRAFT->value,
            'categories' => [],
            'tags' => [],
            'seo_title' => 'Hemoglobin Reference Range',
            'seo_description' => 'Hemoglobin reference range for adult males.',
        ], $overrides);
    }

    public function test_guest_cannot_access_reference_range_admin(): void
    {
        $response = $this->get(route('admin.reference-ranges.index'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_user_without_reference_range_permission_cannot_access_reference_range_admin(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.reference-ranges.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_reference_range_view_can_view_index(): void
    {
        $user = $this->createUserWithPermission('reference-ranges.view');

        $response = $this->actingAs($user)->get(route('admin.reference-ranges.index'));

        $response->assertStatus(200);
        $response->assertSee('Reference Ranges');
    }

    public function test_user_with_reference_range_create_can_view_create_page(): void
    {
        $user = $this->createUserWithPermission('reference-ranges.create');

        $response = $this->actingAs($user)->get(route('admin.reference-ranges.create'));

        $response->assertStatus(200);
        $response->assertSee('New Reference Range');
        $response->assertSee('Save and Create Another');
    }

    public function test_user_with_reference_range_edit_can_view_edit_page(): void
    {
        $referenceRange = ReferenceRange::factory()->create();
        $user = $this->createUserWithPermission('reference-ranges.edit');

        $response = $this->actingAs($user)->get(route('admin.reference-ranges.edit', $referenceRange));

        $response->assertStatus(200);
        $response->assertSee('Edit Reference Range');
    }

    public function test_user_with_reference_range_create_can_create_reference_range(): void
    {
        $user = $this->createUserWithPermission('reference-ranges.create');

        $response = $this->actingAs($user)->post(route('admin.reference-ranges.store'), $this->validPayload());

        $response->assertRedirect(route('admin.reference-ranges.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reference_ranges', [
            'name' => 'Hemoglobin - Adult Male',
            'slug' => 'hemoglobin-adult-male',
            'status' => ResourceStatus::DRAFT->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_save_and_create_redirects_back_to_create(): void
    {
        $user = $this->createUserWithPermission('reference-ranges.create');

        $response = $this->actingAs($user)->post(route('admin.reference-ranges.store'), $this->validPayload([
            'save_action' => 'save_and_create',
        ]));

        $response->assertRedirect(route('admin.reference-ranges.create'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reference_ranges', [
            'name' => 'Hemoglobin - Adult Male',
        ]);
    }

    public function test_user_with_reference_range_edit_can_update_reference_range(): void
    {
        $referenceRange = ReferenceRange::factory()->create();
        $user = $this->createUserWithPermission('reference-ranges.edit');

        $response = $this->actingAs($user)->put(route('admin.reference-ranges.update', $referenceRange), $this->validPayload([
            'name' => 'Updated Reference Range',
            'slug' => 'updated-reference-range',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $response->assertRedirect(route('admin.reference-ranges.edit', $referenceRange));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reference_ranges', [
            'id' => $referenceRange->id,
            'name' => 'Updated Reference Range',
            'slug' => 'updated-reference-range',
            'updated_by' => $user->id,
        ]);
    }

    public function test_user_with_reference_range_delete_can_delete_reference_range(): void
    {
        $referenceRange = ReferenceRange::factory()->create();
        $user = $this->createUserWithPermission('reference-ranges.delete');

        $response = $this->actingAs($user)->delete(route('admin.reference-ranges.destroy', $referenceRange));

        $response->assertRedirect(route('admin.reference-ranges.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('reference_ranges', ['id' => $referenceRange->id]);
    }

    public function test_reference_range_creation_is_audited(): void
    {
        $user = $this->createUserWithPermission('reference-ranges.create');

        $this->actingAs($user)->post(route('admin.reference-ranges.store'), $this->validPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'reference-ranges.created',
            'resource_type' => ReferenceRange::class,
        ]);
    }

    public function test_reference_range_update_is_audited(): void
    {
        $referenceRange = ReferenceRange::factory()->create();
        $user = $this->createUserWithPermission('reference-ranges.edit');

        $this->actingAs($user)->put(route('admin.reference-ranges.update', $referenceRange), $this->validPayload([
            'name' => 'Updated Reference Range',
            'slug' => 'updated-reference-range',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'reference-ranges.updated',
            'resource_type' => ReferenceRange::class,
            'resource_id' => $referenceRange->id,
        ]);
    }
}
