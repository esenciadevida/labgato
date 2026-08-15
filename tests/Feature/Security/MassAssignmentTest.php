<?php

namespace Tests\Feature\Security;

use App\Models\KnowledgeResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MassAssignmentTest extends TestCase
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
            'name' => 'Introduction to Hematology',
            'slug' => 'introduction-to-hematology',
            'short_description' => 'Foundational concepts.',
            'content' => '# Guide content',
            'topic_category' => 'Hematology',
            'difficulty_level' => 'Beginner',
            'learning_objectives' => 'Understand the CBC.',
            'status' => 'draft',
            'categories' => [],
            'tags' => [],
        ], $overrides);
    }

    public function test_user_cannot_spoof_created_by_field_via_mass_assignment(): void
    {
        $attacker = User::factory()->create();
        $actor = $this->createUserWithPermission('knowledge.create');

        $this->actingAs($actor)->post(route('admin.knowledge.store'), $this->validPayload([
            'created_by' => $attacker->id,
            'updated_by' => $attacker->id,
        ]));

        $knowledge = KnowledgeResource::query()->first();

        $this->assertNotNull($knowledge);
        $this->assertSame($actor->id, $knowledge->created_by);
        $this->assertSame($actor->id, $knowledge->updated_by);
        $this->assertNotSame($attacker->id, $knowledge->created_by);
    }

    public function test_user_cannot_spoof_published_at_via_mass_assignment(): void
    {
        $actor = $this->createUserWithPermission('knowledge.create');

        $this->actingAs($actor)->post(route('admin.knowledge.store'), $this->validPayload([
            'status' => 'draft',
            'published_at' => now()->addDay()->toDateTimeString(),
        ]));

        $knowledge = KnowledgeResource::query()->first();

        $this->assertNull($knowledge->published_at);
    }

    public function test_user_cannot_spoof_status_transition_via_update(): void
    {
        $actor = $this->createUserWithPermission('knowledge.update');
        $knowledge = KnowledgeResource::factory()->create(['status' => 'published']);

        $this->actingAs($actor)->put(route('admin.knowledge.update', $knowledge), $this->validPayload([
            'status' => 'draft',
        ]));

        $this->assertSame('published', $knowledge->fresh()->status->value);
    }

    public function test_model_rejects_non_fillable_fields(): void
    {
        $actor = User::factory()->create();

        $knowledge = KnowledgeResource::create([
            'name' => 'Untitled',
            'slug' => 'untitled',
            'content' => 'Content.',
            'status' => 'draft',
            'created_by' => $actor->id,
            'is_public' => true,
            'is_featured' => true,
        ]);

        $this->assertFalse(property_exists($knowledge, 'is_public'));
        $this->assertFalse(property_exists($knowledge, 'is_featured'));
        $this->assertSame($actor->id, $knowledge->created_by);
    }
}
