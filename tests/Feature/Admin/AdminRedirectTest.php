<?php

namespace Tests\Feature\Admin;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Calculators\Engines\McvCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminRedirectTest extends TestCase
{
    use RefreshDatabase;

    private function createUserWithPermissions(array $permissionSlugs): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $role = Role::factory()->create();

        foreach ($permissionSlugs as $slug) {
            $role->permissions()->attach(Permission::factory()->create(['slug' => $slug]));
        }

        $user->roles()->attach($role);

        return $user;
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'MCV Calculator',
            'slug' => 'mcv-calculator',
            'short_description' => 'Calculates the mean corpuscular volume.',
            'formula_display' => 'MCV = (Hct × 10) / RBC',
            'status' => ResourceStatus::DRAFT->value,
            'input_schema' => json_encode([
                ['key' => 'hematocrit', 'label' => 'Hematocrit', 'type' => 'number', 'required' => true],
                ['key' => 'rbc', 'label' => 'RBC count', 'type' => 'number', 'required' => true],
            ]),
            'output_schema' => json_encode([
                ['key' => 'mcv', 'label' => 'MCV', 'rounding' => 'round_half_up', 'precision' => 1],
            ]),
            'engine' => 'php_class',
            'engine_reference' => McvCalculator::class,
            'interpretation_notes' => 'Interpretation notes.',
            'references' => 'Clinical reference.',
            'categories' => [],
            'tags' => [],
            'seo_title' => 'MCV Calculator',
            'seo_description' => 'Mean corpuscular volume calculator.',
        ], $overrides);
    }

    public function test_update_redirects_to_the_edit_page(): void
    {
        $calculator = Calculator::factory()->draft()->create();
        $user = $this->createUserWithPermissions(['calculators.edit']);

        $response = $this->actingAs($user)
            ->put(route('admin.calculators.update', $calculator), $this->validPayload([
                'name' => 'Updated Calculator',
                'slug' => 'updated-calculator',
            ]));

        $response->assertRedirect(route('admin.calculators.edit', $calculator));
        $response->assertSessionHas('success', 'Calculator updated successfully.');
    }

    public function test_store_redirects_to_the_listing_page(): void
    {
        $user = $this->createUserWithPermissions(['calculators.create']);

        $response = $this->actingAs($user)->post(route('admin.calculators.store'), $this->validPayload());

        $response->assertRedirect(route('admin.calculators.index'));
        $response->assertSessionHas('success', 'Calculator created successfully.');
    }

    public function test_publish_redirects_back_to_the_previous_page(): void
    {
        $calculator = Calculator::factory()->draft()->create();
        $user = $this->createUserWithPermissions(['calculators.edit']);

        $response = $this->actingAs($user)
            ->from(route('admin.calculators.edit', $calculator))
            ->patch(route('admin.calculators.publish', $calculator));

        $response->assertRedirect(route('admin.calculators.edit', $calculator));
        $response->assertSessionHas('success', 'Calculator published successfully.');
    }

    public function test_archive_redirects_back_to_the_previous_page(): void
    {
        $calculator = Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.edit']);

        $response = $this->actingAs($user)
            ->from('/admin/calculators?status=published')
            ->patch(route('admin.calculators.archive', $calculator));

        $response->assertRedirect('/admin/calculators?status=published');
        $response->assertSessionHas('success', 'Calculator archived successfully.');
    }

    public function test_destroy_redirects_to_the_intended_listing_url_preserving_filters(): void
    {
        $calculator = Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.view', 'calculators.delete']);

        $this->actingAs($user)->get('/admin/calculators?status=published');

        $response = $this->actingAs($user)
            ->from('/admin/calculators?status=published')
            ->delete(route('admin.calculators.destroy', $calculator));

        $response->assertRedirect('/admin/calculators?status=published');
        $response->assertSessionHas('success', 'Calculator deleted successfully.');
    }

    public function test_destroy_falls_back_to_the_listing_page_without_intended_url(): void
    {
        $calculator = Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.delete']);

        $response = $this->actingAs($user)->delete(route('admin.calculators.destroy', $calculator));

        $response->assertRedirect(route('admin.calculators.index'));
        $response->assertSessionHas('success', 'Calculator deleted successfully.');
    }
}
