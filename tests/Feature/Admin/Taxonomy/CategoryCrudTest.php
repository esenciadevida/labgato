<?php

namespace Tests\Feature\Admin\Taxonomy;

use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryCrudTest extends TestCase
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

    public function test_guest_cannot_access_categories(): void
    {
        $response = $this->get(route('admin.categories.index'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_user_without_taxonomy_permission_cannot_access_categories(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.categories.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_taxonomy_view_can_view_category_index(): void
    {
        $user = $this->createUserWithPermission('taxonomy.view');

        $response = $this->actingAs($user)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertSee('Categories');
    }

    public function test_user_with_taxonomy_manage_can_view_create_form(): void
    {
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->get(route('admin.categories.create'));

        $response->assertStatus(200);
        $response->assertSee('New Category');
        $response->assertSee('Parent category');
    }

    public function test_user_with_taxonomy_manage_can_view_edit_form(): void
    {
        $category = Category::factory()->create();
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->get(route('admin.categories.edit', $category));

        $response->assertStatus(200);
        $response->assertSee('Edit Category');
        $response->assertSee($category->name);
    }

    public function test_user_with_taxonomy_view_cannot_access_create_form(): void
    {
        $user = $this->createUserWithPermission('taxonomy.view');

        $response = $this->actingAs($user)->get(route('admin.categories.create'));

        $response->assertStatus(403);
    }

    public function test_user_with_taxonomy_manage_can_create_category(): void
    {
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->post(route('admin.categories.store'), [
            'name' => 'Hematology',
            'slug' => '',
            'description' => 'Studies of blood and blood-forming tissues.',
            'parent_id' => '',
            'sort_order' => 10,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('categories', [
            'name' => 'Hematology',
            'slug' => 'hematology',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'taxonomy.category.created',
            'resource_type' => Category::class,
        ]);
    }

    public function test_category_requires_name(): void
    {
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->post(route('admin.categories.store'), [
            'slug' => 'hematology',
            'sort_order' => 0,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_category_slug_must_be_unique(): void
    {
        Category::factory()->create(['slug' => 'hematology']);
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->post(route('admin.categories.store'), [
            'name' => 'Hematology Two',
            'slug' => 'hematology',
            'sort_order' => 0,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('slug');
    }

    public function test_category_parent_cannot_be_itself(): void
    {
        $category = Category::factory()->create();
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->put(route('admin.categories.update', $category), [
            'name' => $category->name,
            'slug' => $category->slug,
            'parent_id' => $category->id,
            'sort_order' => 0,
            'is_active' => 1,
        ]);

        $response->assertSessionHasErrors('parent_id');
    }

    public function test_category_with_children_cannot_be_deleted(): void
    {
        $parent = Category::factory()->create();
        Category::factory()->create(['parent_id' => $parent->id]);
        $user = $this->createUserWithPermission('taxonomy.manage');

        $response = $this->actingAs($user)->delete(route('admin.categories.destroy', $parent));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('error', 'Category cannot be deleted because it has child categories.');

        $this->assertDatabaseHas('categories', ['id' => $parent->id]);
    }

    public function test_category_deletion_is_audit_logged(): void
    {
        $category = Category::factory()->create();
        $user = $this->createUserWithPermission('taxonomy.manage');

        $this->actingAs($user)->delete(route('admin.categories.destroy', $category));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'taxonomy.category.deleted',
            'resource_type' => Category::class,
            'resource_id' => $category->id,
        ]);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
