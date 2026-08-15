<?php

namespace Tests\Feature\Admin\Tools;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\LaboratoryTool;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use App\Services\Tools\Engines\DilutionCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToolAdminCrudTest extends TestCase
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
            'name' => 'Dilution Calculator',
            'slug' => 'dilution-calculator',
            'short_description' => 'Calculate the volume of stock and diluent required.',
            'formula_display' => 'C1 × V1 = C2 × V2',
            'status' => ResourceStatus::DRAFT->value,
            'input_schema' => json_encode([
                ['key' => 'stock_concentration', 'label' => 'Stock Concentration', 'type' => 'number', 'unit' => 'mg/mL', 'required' => true],
                ['key' => 'final_concentration', 'label' => 'Final Concentration', 'type' => 'number', 'unit' => 'mg/mL', 'required' => true],
                ['key' => 'final_volume', 'label' => 'Final Volume', 'type' => 'number', 'unit' => 'mL', 'required' => true],
            ]),
            'output_schema' => json_encode([
                ['key' => 'stock_volume', 'label' => 'Stock Volume', 'unit' => 'mL'],
                ['key' => 'diluent_volume', 'label' => 'Diluent to Add', 'unit' => 'mL'],
            ]),
            'engine' => 'php_class',
            'engine_reference' => DilutionCalculator::class,
            'interpretation_notes' => 'Interpret with care.',
            'references' => 'Laboratory reference documentation.',
            'categories' => [],
            'tags' => [],
            'seo_title' => 'Dilution Calculator',
            'seo_description' => 'Calculate dilutions.',
        ], $overrides);
    }

    public function test_guest_cannot_access_tool_admin(): void
    {
        $response = $this->get(route('admin.tools.index'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_user_without_tool_permission_cannot_access_tool_admin(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.tools.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_tool_view_can_view_index(): void
    {
        $user = $this->createUserWithPermission('tools.view');
        LaboratoryTool::factory()->create(['name' => 'Existing Tool']);

        $response = $this->actingAs($user)->get(route('admin.tools.index'));

        $response->assertStatus(200);
        $response->assertSee('Laboratory Tools');
        $response->assertSee('Existing Tool');
    }

    public function test_user_with_tool_create_can_view_create_page(): void
    {
        $user = $this->createUserWithPermission('tools.create');

        $response = $this->actingAs($user)->get(route('admin.tools.create'));

        $response->assertStatus(200);
        $response->assertSee('New Tool');
    }

    public function test_user_with_tool_edit_can_view_edit_page(): void
    {
        $tool = LaboratoryTool::factory()->create();
        $user = $this->createUserWithPermission('tools.edit');

        $response = $this->actingAs($user)->get(route('admin.tools.edit', $tool));

        $response->assertStatus(200);
        $response->assertSee('Edit Tool');
    }

    public function test_user_with_tool_create_can_create_tool(): void
    {
        $user = $this->createUserWithPermission('tools.create');

        $response = $this->actingAs($user)->post(route('admin.tools.store'), $this->validPayload());

        $response->assertRedirect(route('admin.tools.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('laboratory_tools', [
            'name' => 'Dilution Calculator',
            'slug' => 'dilution-calculator',
            'engine_reference' => DilutionCalculator::class,
            'status' => ResourceStatus::DRAFT->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_user_with_tool_edit_can_update_tool(): void
    {
        $tool = LaboratoryTool::factory()->create();
        $user = $this->createUserWithPermission('tools.edit');

        $response = $this->actingAs($user)->put(route('admin.tools.update', $tool), $this->validPayload([
            'name' => 'Updated Tool',
            'slug' => 'updated-tool',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $response->assertRedirect(route('admin.tools.edit', $tool));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('laboratory_tools', [
            'id' => $tool->id,
            'name' => 'Updated Tool',
            'slug' => 'updated-tool',
            'updated_by' => $user->id,
        ]);
    }

    public function test_user_with_tool_edit_can_update_tool_without_changing_slug(): void
    {
        $tool = LaboratoryTool::factory()->create(['slug' => 'keep-this-slug']);
        $user = $this->createUserWithPermission('tools.edit');

        $response = $this->actingAs($user)->put(route('admin.tools.update', $tool), $this->validPayload([
            'slug' => 'keep-this-slug',
            'name' => 'Updated Tool',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $response->assertRedirect(route('admin.tools.edit', $tool));
        $response->assertSessionHas('success');
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('laboratory_tools', [
            'id' => $tool->id,
            'name' => 'Updated Tool',
            'slug' => 'keep-this-slug',
        ]);
    }

    public function test_user_with_tool_delete_can_delete_tool(): void
    {
        $tool = LaboratoryTool::factory()->create();
        $user = $this->createUserWithPermission('tools.delete');

        $response = $this->actingAs($user)->delete(route('admin.tools.destroy', $tool));

        $response->assertRedirect(route('admin.tools.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('laboratory_tools', ['id' => $tool->id]);
    }

    public function test_tool_creation_is_audited(): void
    {
        $user = $this->createUserWithPermission('tools.create');

        $this->actingAs($user)->post(route('admin.tools.store'), $this->validPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'tools.created',
            'resource_type' => LaboratoryTool::class,
        ]);
    }

    public function test_tool_published_at_is_set_when_publishing(): void
    {
        $user = $this->createUserWithPermission('tools.create');

        $this->actingAs($user)->post(route('admin.tools.store'), $this->validPayload([
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $tool = LaboratoryTool::query()->first();

        $this->assertNotNull($tool->published_at);
    }

    public function test_tool_categories_and_tags_are_synced(): void
    {
        $user = $this->createUserWithPermission('tools.create');
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user)->post(route('admin.tools.store'), $this->validPayload([
            'categories' => [$category->id],
            'tags' => [$tag->id],
        ]));

        $tool = LaboratoryTool::query()->first();

        $this->assertTrue($tool->categories->contains($category));
        $this->assertTrue($tool->tags->contains($tag));
    }
}
