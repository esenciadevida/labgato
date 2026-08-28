<?php

namespace Tests\Feature\Admin\Knowledge;

use App\Enums\ResourceStatus;
use App\Models\KnowledgeResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class KnowledgeAdminValidationTest extends TestCase
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
            'status' => ResourceStatus::DRAFT->value,
            'categories' => [],
            'tags' => [],
        ], $overrides);
    }

    private function createUser(): User
    {
        return $this->createUserWithPermission('knowledge.create');
    }

    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['name' => '', 'slug' => ''])
        );

        $response->assertSessionHasErrors('name');
    }

    public function test_slug_must_be_unique(): void
    {
        KnowledgeResource::factory()->create(['slug' => 'introduction-to-hematology']);
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(
            route('admin.knowledge.store'),
            $this->validPayload(['name' => 'Another Guide', 'slug' => 'introduction-to-hematology'])
        );

        $response->assertSessionHasErrors('slug');
    }

    public function test_content_is_required(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['content' => ''])
        );

        $response->assertSessionHasErrors('content');
    }

    public function test_difficulty_level_must_be_valid(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['difficulty_level' => 'Expert'])
        );

        $response->assertSessionHasErrors('difficulty_level');
    }

    public function test_status_must_be_valid(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['status' => 'not-a-real-status'])
        );

        $response->assertSessionHasErrors('status');
    }

    public function test_reading_time_must_be_positive(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['reading_time_minutes' => '0'])
        );

        $response->assertSessionHasErrors('reading_time_minutes');
    }

    public function test_featured_image_rejects_disallowed_types(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['featured_image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')])
        );

        $response->assertSessionHasErrors('featured_image');
    }

    public function test_featured_image_url_accepts_valid_http_url(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['featured_image_url' => 'https://media.example.com/path/to/image.jpg'])
        );

        $response->assertRedirect(route('admin.knowledge.index'));
        $response->assertSessionHasNoErrors('featured_image_url');
    }

    public function test_featured_image_url_rejects_invalid_urls(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['featured_image_url' => 'ftp://example.com/image.jpg'])
        );

        $response->assertSessionHasErrors('featured_image_url');
    }

    public function test_seo_title_has_max_length(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['seo_title' => str_repeat('a', 71)])
        );

        $response->assertSessionHasErrors('seo_title');
    }

    public function test_seo_description_has_max_length(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.knowledge.store'),
            $this->validPayload(['seo_description' => str_repeat('a', 161)])
        );

        $response->assertSessionHasErrors('seo_description');
    }
}
