<?php

namespace Tests\Feature\Admin\Taxonomy;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagCrudTest extends TestCase
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

    public function test_guest_cannot_access_tags(): void
    {
        $response = $this->get(route('admin.tags.index'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_user_without_taxonomy_permission_cannot_access_tags(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.tags.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_taxonomy_view_can_view_tag_index(): void
    {
        $user = $this->createUserWithPermission('taxonomy.view');

        $response = $this->actingAs($user)->get(route('admin.tags.index'));

        $response->assertStatus(200);
        $response->assertSee('Tags');
    }

    public function test_user_with_taxonomy_manage_can_view_create_form(): void
    {
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->get(route('admin.tags.create'));

        $response->assertStatus(200);
        $response->assertSee('New Tag');
    }

    public function test_user_with_taxonomy_manage_can_view_edit_form(): void
    {
        $tag = Tag::factory()->create();
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->get(route('admin.tags.edit', $tag));

        $response->assertStatus(200);
        $response->assertSee('Edit Tag');
        $response->assertSee($tag->name);
    }

    public function test_user_with_taxonomy_manage_can_create_tag(): void
    {
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->post(route('admin.tags.store'), [
            'name' => 'Urgent',
            'slug' => '',
        ]);

        $response->assertRedirect(route('admin.tags.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tags', [
            'name' => 'urgent',
            'slug' => 'urgent',
        ]);
    }

    public function test_tag_name_is_normalized_to_lowercase(): void
    {
        $user = $this->createUserWithPermission('taxonomy.manage');

        $this->actingAs($user)->post(route('admin.tags.store'), [
            'name' => 'Hematology',
            'slug' => '',
        ]);

        $this->assertDatabaseHas('tags', ['name' => 'hematology']);
        $this->assertDatabaseMissing('tags', ['name' => 'Hematology']);
    }

    public function test_duplicate_tag_names_are_rejected_case_insensitively(): void
    {
        Tag::factory()->create(['name' => 'hematology', 'slug' => 'hematology']);
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->post(route('admin.tags.store'), [
            'name' => 'HEMATOLOGY',
            'slug' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_tag_requires_name(): void
    {
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->post(route('admin.tags.store'), [
            'slug' => 'urgent',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_tag_deletion_is_audit_logged(): void
    {
        $tag = Tag::factory()->create();
        $user = $this->createUserWithPermission('taxonomy.manage');

        $this->actingAs($user)->delete(route('admin.tags.destroy', $tag));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'taxonomy.tag.deleted',
            'resource_type' => Tag::class,
            'resource_id' => $tag->id,
        ]);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}
