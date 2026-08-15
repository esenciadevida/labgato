<?php

namespace Tests\Feature\Admin\ReferenceRanges;

use App\Enums\ResourceStatus;
use App\Models\Permission;
use App\Models\ReferenceRange;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferenceRangeAdminValidationTest extends TestCase
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
            'population_group' => 'Adult Male',
            'age_min' => '18',
            'age_max' => '65',
            'sex' => 'male',
            'unit' => 'g/dL',
            'lower_limit' => '13.0',
            'upper_limit' => '17.0',
            'critical_low' => '7.0',
            'critical_high' => '22.0',
            'source_citation' => 'Clinical reference.',
            'status' => ResourceStatus::DRAFT->value,
            'categories' => [],
            'tags' => [],
        ], $overrides);
    }

    private function createUser(): User
    {
        return $this->createUserWithPermission('reference-ranges.create');
    }

    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['name' => '', 'slug' => ''])
        );

        $response->assertSessionHasErrors('name');
    }

    public function test_slug_must_be_unique(): void
    {
        ReferenceRange::factory()->create(['slug' => 'hemoglobin-adult-male']);
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['name' => 'Another Range', 'slug' => 'hemoglobin-adult-male'])
        );

        $response->assertSessionHasErrors('slug');
    }

    public function test_analyte_name_is_required(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['analyte_name' => ''])
        );

        $response->assertSessionHasErrors('analyte_name');
    }

    public function test_unit_is_required(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['unit' => ''])
        );

        $response->assertSessionHasErrors('unit');
    }

    public function test_status_must_be_valid(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['status' => 'not-a-real-status'])
        );

        $response->assertSessionHasErrors('status');
    }

    public function test_sex_must_be_valid(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['sex' => 'unknown'])
        );

        $response->assertSessionHasErrors('sex');
    }

    public function test_lower_limit_must_be_less_than_upper_limit(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['lower_limit' => '20', 'upper_limit' => '10'])
        );

        $response->assertSessionHasErrors('unit');
    }

    public function test_critical_low_must_be_less_than_lower_limit(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['critical_low' => '14', 'lower_limit' => '13'])
        );

        $response->assertSessionHasErrors('unit');
    }

    public function test_critical_high_must_be_greater_than_upper_limit(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['critical_high' => '16', 'upper_limit' => '17'])
        );

        $response->assertSessionHasErrors('unit');
    }

    public function test_age_min_must_be_less_than_or_equal_to_age_max(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['age_min' => '65', 'age_max' => '18'])
        );

        $response->assertSessionHasErrors('unit');
    }

    public function test_seo_title_has_max_length(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['seo_title' => str_repeat('a', 71)])
        );

        $response->assertSessionHasErrors('seo_title');
    }

    public function test_seo_description_has_max_length(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.reference-ranges.store'),
            $this->validPayload(['seo_description' => str_repeat('a', 161)])
        );

        $response->assertSessionHasErrors('seo_description');
    }
}
