<?php

namespace Tests\Feature\Admin;

use App\Enums\ResourceStatus;
use App\Models\Calculator;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\LaboratoryTool;
use App\Models\Permission;
use App\Models\ReferenceRange;
use App\Models\Role;
use App\Models\Sop;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class BulkActionTest extends TestCase
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

    private function postBulkAction(User $user, string $type, string $action, array $ids, string $from = '/admin'): TestResponse
    {
        return $this->actingAs($user)
            ->from($from)
            ->post(route('admin.bulk', ['type' => $type]), [
                'action' => $action,
                'ids' => $ids,
            ]);
    }

    public function test_bulk_publish_publishes_selected_draft_calculators(): void
    {
        $first = Calculator::factory()->draft()->create();
        $second = Calculator::factory()->draft()->create();
        Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.publish']);

        $response = $this->postBulkAction($user, 'calculator', 'publish', [$first->id, $second->id], '/admin/calculators');

        $response->assertRedirect('/admin/calculators');
        $response->assertSessionHas('success', '2 calculators published.');

        $this->assertDatabaseHas('calculators', ['id' => $first->id, 'status' => ResourceStatus::PUBLISHED->value]);
        $this->assertDatabaseHas('calculators', ['id' => $second->id, 'status' => ResourceStatus::PUBLISHED->value]);
        $this->assertNotNull($first->fresh()->published_at);
    }

    public function test_bulk_publish_skips_resources_already_published(): void
    {
        $draft = Calculator::factory()->draft()->create();
        $published = Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.publish']);

        $response = $this->postBulkAction($user, 'calculator', 'publish', [$draft->id, $published->id]);

        $response->assertSessionHas('success', '1 calculator published.');
        $this->assertDatabaseHas('calculators', ['id' => $published->id, 'status' => ResourceStatus::PUBLISHED->value]);
    }

    public function test_bulk_archive_archives_selected_published_resources(): void
    {
        $first = Calculator::factory()->create();
        $second = Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.edit']);

        $response = $this->postBulkAction($user, 'calculator', 'archive', [$first->id, $second->id], '/admin/calculators?status=published');

        $response->assertRedirect('/admin/calculators?status=published');
        $response->assertSessionHas('success', '2 calculators archived.');

        $this->assertDatabaseHas('calculators', ['id' => $first->id, 'status' => ResourceStatus::ARCHIVED->value]);
        $this->assertDatabaseHas('calculators', ['id' => $second->id, 'status' => ResourceStatus::ARCHIVED->value]);
    }

    public function test_bulk_delete_removes_selected_resources(): void
    {
        $first = Calculator::factory()->create();
        $second = Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.delete']);

        $response = $this->postBulkAction($user, 'calculator', 'delete', [$first->id, $second->id]);

        $response->assertSessionHas('success', '2 calculators deleted.');
        $this->assertDatabaseMissing('calculators', ['id' => $first->id]);
        $this->assertDatabaseMissing('calculators', ['id' => $second->id]);
    }

    public function test_bulk_publish_requires_publish_permission(): void
    {
        $calculator = Calculator::factory()->draft()->create();
        $user = $this->createUserWithPermissions(['calculators.edit']);

        $this->postBulkAction($user, 'calculator', 'publish', [$calculator->id])->assertForbidden();
        $this->assertDatabaseHas('calculators', ['id' => $calculator->id, 'status' => ResourceStatus::DRAFT->value]);
    }

    public function test_bulk_delete_requires_delete_permission(): void
    {
        $calculator = Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.edit']);

        $this->postBulkAction($user, 'calculator', 'delete', [$calculator->id])->assertForbidden();
        $this->assertDatabaseHas('calculators', ['id' => $calculator->id]);
    }

    public function test_bulk_action_requires_a_supported_action(): void
    {
        $calculator = Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.edit']);

        $this->postBulkAction($user, 'calculator', 'banish', [$calculator->id])
            ->assertSessionHasErrors('action');
    }

    public function test_bulk_action_requires_at_least_one_id(): void
    {
        $user = $this->createUserWithPermissions(['calculators.publish']);

        $this->postBulkAction($user, 'calculator', 'publish', [])
            ->assertSessionHasErrors('ids');
    }

    public function test_bulk_action_rejects_non_numeric_ids(): void
    {
        $user = $this->createUserWithPermissions(['calculators.publish']);

        $this->postBulkAction($user, 'calculator', 'publish', ['abc', '0', '-5'])
            ->assertSessionHasErrors('ids.0');
    }

    public function test_bulk_action_rejects_unsupported_resource_type(): void
    {
        $user = $this->createUserWithPermissions(['calculators.edit']);

        $this->postBulkAction($user, 'unicorns', 'publish', [1])->assertStatus(422);
    }

    public function test_bulk_publish_is_audit_logged(): void
    {
        $calculator = Calculator::factory()->draft()->create();
        $user = $this->createUserWithPermissions(['calculators.publish']);

        $this->postBulkAction($user, 'calculator', 'publish', [$calculator->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'calculators.published',
            'resource_type' => Calculator::class,
            'resource_id' => $calculator->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_bulk_delete_is_audit_logged(): void
    {
        $calculator = Calculator::factory()->create();
        $user = $this->createUserWithPermissions(['calculators.delete']);

        $this->postBulkAction($user, 'calculator', 'delete', [$calculator->id]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'calculators.deleted',
            'resource_type' => Calculator::class,
            'resource_id' => $calculator->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_bulk_actions_support_all_resource_types(): void
    {
        $resources = [
            'calculator' => Calculator::factory()->draft()->create(),
            'tool' => LaboratoryTool::factory()->draft()->create(),
            'reference-range' => ReferenceRange::factory()->draft()->create(),
            'sop' => Sop::factory()->draft()->create(),
            'interpretation' => InterpretationResource::factory()->draft()->create(),
            'knowledge' => KnowledgeResource::factory()->draft()->create(),
        ];

        $user = $this->createUserWithPermissions([
            'calculators.publish',
            'tools.publish',
            'reference-ranges.publish',
            'sops.publish',
            'interpretation.publish',
            'knowledge.publish',
        ]);

        foreach ($resources as $type => $resource) {
            $this->postBulkAction($user, $type, 'publish', [$resource->id])
                ->assertSessionHas('success', '1 '.str($type)->replace('-', ' ').' published.');
        }

        foreach ($resources as $resource) {
            $this->assertSame(ResourceStatus::PUBLISHED->value, $resource->fresh()->status->value);
        }
    }
}
