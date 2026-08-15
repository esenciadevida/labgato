<?php

namespace Tests\Feature\Admin\Knowledge;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\KnowledgeResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgeAdminCrudTest extends TestCase
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
            'short_description' => 'Foundational concepts in hematology.',
            'content' => "# Introduction to Hematology\n\nHematology is the study of blood.",
            'topic_category' => 'Hematology',
            'difficulty_level' => 'Beginner',
            'learning_objectives' => "By the end you should be able to:\n\n- Describe the components of blood.",
            'author_name' => 'Lab Education Team',
            'reading_time_minutes' => '8',
            'status' => ResourceStatus::DRAFT->value,
            'categories' => [],
            'tags' => [],
            'seo_title' => 'Introduction to Hematology',
            'seo_description' => 'Foundational concepts in hematology.',
        ], $overrides);
    }

    public function test_guest_cannot_access_knowledge_admin(): void
    {
        $response = $this->get(route('admin.knowledge.index'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_user_without_knowledge_permission_cannot_access_knowledge_admin(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.knowledge.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_knowledge_view_can_view_index(): void
    {
        $user = $this->createUserWithPermission('knowledge.view');
        KnowledgeResource::factory()->create(['name' => 'Existing Knowledge Resource']);

        $response = $this->actingAs($user)->get(route('admin.knowledge.index'));

        $response->assertStatus(200);
        $response->assertSee('Knowledge Resources');
        $response->assertSee('Existing Knowledge Resource');
    }

    public function test_user_with_knowledge_create_can_view_create_page(): void
    {
        $user = $this->createUserWithPermission('knowledge.create');

        $response = $this->actingAs($user)->get(route('admin.knowledge.create'));

        $response->assertStatus(200);
        $response->assertSee('New Knowledge Resource');
        $response->assertSee('Save and Create Another');
    }

    public function test_user_with_knowledge_edit_can_view_edit_page(): void
    {
        $knowledge = KnowledgeResource::factory()->create();
        $user = $this->createUserWithPermission('knowledge.edit');

        $response = $this->actingAs($user)->get(route('admin.knowledge.edit', $knowledge));

        $response->assertStatus(200);
        $response->assertSee('Edit Knowledge Resource');
    }

    public function test_user_with_knowledge_create_can_create_knowledge_resource(): void
    {
        $user = $this->createUserWithPermission('knowledge.create');

        $response = $this->actingAs($user)->post(route('admin.knowledge.store'), $this->validPayload());

        $response->assertRedirect(route('admin.knowledge.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('knowledge_resources', [
            'name' => 'Introduction to Hematology',
            'slug' => 'introduction-to-hematology',
            'difficulty_level' => 'Beginner',
            'learning_objectives' => "By the end you should be able to:\n\n- Describe the components of blood.",
            'status' => ResourceStatus::DRAFT->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_save_and_create_redirects_back_to_create(): void
    {
        $user = $this->createUserWithPermission('knowledge.create');

        $response = $this->actingAs($user)->post(route('admin.knowledge.store'), $this->validPayload([
            'save_action' => 'save_and_create',
        ]));

        $response->assertRedirect(route('admin.knowledge.create'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('knowledge_resources', [
            'name' => 'Introduction to Hematology',
        ]);
    }

    public function test_user_with_knowledge_edit_can_update_knowledge_resource(): void
    {
        $knowledge = KnowledgeResource::factory()->create();
        $user = $this->createUserWithPermission('knowledge.edit');

        $response = $this->actingAs($user)->put(route('admin.knowledge.update', $knowledge), $this->validPayload([
            'name' => 'Updated Knowledge Resource',
            'slug' => 'updated-knowledge-resource',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $response->assertRedirect(route('admin.knowledge.edit', $knowledge));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('knowledge_resources', [
            'id' => $knowledge->id,
            'name' => 'Updated Knowledge Resource',
            'slug' => 'updated-knowledge-resource',
            'updated_by' => $user->id,
        ]);
    }

    public function test_user_with_knowledge_delete_can_delete_knowledge_resource(): void
    {
        $knowledge = KnowledgeResource::factory()->create();
        $user = $this->createUserWithPermission('knowledge.delete');

        $response = $this->actingAs($user)->delete(route('admin.knowledge.destroy', $knowledge));

        $response->assertRedirect(route('admin.knowledge.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('knowledge_resources', ['id' => $knowledge->id]);
    }

    public function test_knowledge_creation_is_audited(): void
    {
        $user = $this->createUserWithPermission('knowledge.create');

        $this->actingAs($user)->post(route('admin.knowledge.store'), $this->validPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'knowledge.created',
            'resource_type' => KnowledgeResource::class,
        ]);
    }

    public function test_knowledge_update_is_audited(): void
    {
        $knowledge = KnowledgeResource::factory()->create();
        $user = $this->createUserWithPermission('knowledge.edit');

        $this->actingAs($user)->put(route('admin.knowledge.update', $knowledge), $this->validPayload([
            'name' => 'Updated Knowledge Resource',
            'slug' => 'updated-knowledge-resource',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'knowledge.updated',
            'resource_type' => KnowledgeResource::class,
            'resource_id' => $knowledge->id,
        ]);
    }

    public function test_knowledge_published_at_is_set_when_publishing(): void
    {
        $user = $this->createUserWithPermission('knowledge.create');

        $this->actingAs($user)->post(route('admin.knowledge.store'), $this->validPayload([
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $knowledge = KnowledgeResource::query()->first();

        $this->assertNotNull($knowledge->published_at);
    }

    public function test_knowledge_categories_and_tags_are_synced(): void
    {
        $user = $this->createUserWithPermission('knowledge.create');
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user)->post(route('admin.knowledge.store'), $this->validPayload([
            'categories' => [$category->id],
            'tags' => [$tag->id],
        ]));

        $knowledge = KnowledgeResource::query()->first();

        $this->assertTrue($knowledge->categories->contains($category));
        $this->assertTrue($knowledge->tags->contains($tag));
    }

    public function test_featured_image_upload_stores_image_and_returns_path(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithPermission('knowledge.create');

        $this->actingAs($user)->post(route('admin.knowledge.store'), $this->validPayload([
            'featured_image' => UploadedFile::fake()->image('featured.jpg'),
        ]));

        $knowledge = KnowledgeResource::query()->first();

        $this->assertNotNull($knowledge->featured_image);
        Storage::disk('public')->assertExists($knowledge->featured_image);
    }

    public function test_knowledge_deletion_removes_featured_image(): void
    {
        Storage::fake('public');

        $imagePath = 'content/featured.jpg';
        Storage::disk('public')->put($imagePath, 'fake-content');

        $knowledge = KnowledgeResource::factory()->create(['featured_image' => $imagePath]);
        $user = $this->createUserWithPermission('knowledge.delete');

        $this->actingAs($user)->delete(route('admin.knowledge.destroy', $knowledge));

        Storage::disk('public')->assertMissing($imagePath);
    }
}
