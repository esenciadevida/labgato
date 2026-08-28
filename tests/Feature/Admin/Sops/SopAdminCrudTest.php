<?php

namespace Tests\Feature\Admin\Sops;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Sop;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SopAdminCrudTest extends TestCase
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
            'name' => 'Daily Hematology Analyzer Quality Control',
            'slug' => 'daily-hematology-analyzer-quality-control',
            'short_description' => 'Daily quality control procedure for the hematology analyzer.',
            'content' => "# Daily Quality Control\n\nRun controls before patient samples.",
            'department' => 'Hematology',
            'version' => 'v2.1',
            'author_name' => 'Lab Education Team',
            'reviewed_date' => '2026-01-15',
            'mandatory_disclaimer' => 'Follow your institutional policy.',
            'status' => ResourceStatus::DRAFT->value,
            'categories' => [],
            'tags' => [],
            'seo_title' => 'Daily Hematology Analyzer Quality Control',
            'seo_description' => 'Daily quality control procedure for the hematology analyzer.',
        ], $overrides);
    }

    public function test_guest_cannot_access_sop_admin(): void
    {
        $response = $this->get(route('admin.sops.index'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_user_without_sop_permission_cannot_access_sop_admin(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.sops.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_sop_view_can_view_index(): void
    {
        $user = $this->createUserWithPermission('sops.view');
        Sop::factory()->create(['name' => 'Existing SOP']);

        $response = $this->actingAs($user)->get(route('admin.sops.index'));

        $response->assertStatus(200);
        $response->assertSee('Standard Operating Procedures');
        $response->assertSee('Existing SOP');
    }

    public function test_user_with_sop_create_can_view_create_page(): void
    {
        $user = $this->createUserWithPermission('sops.create');

        $response = $this->actingAs($user)->get(route('admin.sops.create'));

        $response->assertStatus(200);
        $response->assertSee('New SOP');
        $response->assertSee('Save and Create Another');
    }

    public function test_user_with_sop_edit_can_view_edit_page(): void
    {
        $sop = Sop::factory()->create();
        $user = $this->createUserWithPermission('sops.edit');

        $response = $this->actingAs($user)->get(route('admin.sops.edit', $sop));

        $response->assertStatus(200);
        $response->assertSee('Edit SOP');
    }

    public function test_user_with_sop_create_can_create_sop(): void
    {
        $user = $this->createUserWithPermission('sops.create');

        $response = $this->actingAs($user)->post(route('admin.sops.store'), $this->validPayload());

        $response->assertRedirect(route('admin.sops.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sops', [
            'name' => 'Daily Hematology Analyzer Quality Control',
            'slug' => 'daily-hematology-analyzer-quality-control',
            'department' => 'Hematology',
            'version' => 'v2.1',
            'status' => ResourceStatus::DRAFT->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_sop_featured_image_upload_is_persisted(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithPermission('sops.create');

        $this->actingAs($user)->post(route('admin.sops.store'), $this->validPayload([
            'featured_image' => UploadedFile::fake()->image('featured.jpg'),
        ]));

        $sop = Sop::query()->first();

        $this->assertNotNull($sop->featured_image);
        $this->assertStringEndsWith('.webp', $sop->featured_image);
        Storage::disk('public')->assertExists($sop->featured_image);
        $this->assertStringEndsWith('.webp', (string) $sop->featured_image_url);
    }

    public function test_sop_featured_image_url_is_persisted_and_used_for_rendering(): void
    {
        $user = $this->createUserWithPermission('sops.create');

        $this->actingAs($user)->post(route('admin.sops.store'), $this->validPayload([
            'featured_image_url' => 'https://media.licdn.com/dms/image/example.jpg',
        ]));

        $sop = Sop::query()->first();

        $this->assertSame('https://media.licdn.com/dms/image/example.jpg', $sop->featured_image);
        $this->assertSame($sop->featured_image, $sop->featured_image_url);
    }

    public function test_sop_uploaded_image_takes_precedence_over_url(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithPermission('sops.create');

        $this->actingAs($user)->post(route('admin.sops.store'), $this->validPayload([
            'featured_image' => UploadedFile::fake()->image('featured.jpg'),
            'featured_image_url' => 'https://example.com/remote.jpg',
        ]));

        $sop = Sop::query()->first();

        $this->assertNotNull($sop->featured_image);
        $this->assertStringEndsWith('.webp', (string) $sop->featured_image);
        Storage::disk('public')->assertExists($sop->featured_image);
    }

    public function test_sop_updating_featured_image_from_upload_to_url_deletes_local_image(): void
    {
        Storage::fake('public');

        $imagePath = 'content/featured.webp';
        Storage::disk('public')->put($imagePath, 'fake-content');

        $sop = Sop::factory()->create(['featured_image' => $imagePath]);
        $user = $this->createUserWithPermission('sops.edit');

        $this->actingAs($user)->put(route('admin.sops.update', $sop), $this->validPayload([
            'featured_image_url' => 'https://example.com/remote.jpg',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $sop->refresh();

        $this->assertSame('https://example.com/remote.jpg', $sop->featured_image);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_sop_featured_image_url_can_be_removed(): void
    {
        $sop = Sop::factory()->create(['featured_image' => 'https://example.com/remote.jpg']);
        $user = $this->createUserWithPermission('sops.edit');

        $this->actingAs($user)->put(route('admin.sops.update', $sop), $this->validPayload([
            'remove_featured_image' => '1',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $this->assertNull($sop->refresh()->featured_image);
    }

    public function test_sop_featured_image_url_rejects_invalid_urls(): void
    {
        $user = $this->createUserWithPermission('sops.create');

        $response = $this->actingAs($user)->post(route('admin.sops.store'), $this->validPayload([
            'featured_image_url' => 'ftp://example.com/image.jpg',
        ]));

        $response->assertSessionHasErrors('featured_image_url');
    }

    public function test_save_and_create_redirects_back_to_create(): void
    {
        $user = $this->createUserWithPermission('sops.create');

        $response = $this->actingAs($user)->post(route('admin.sops.store'), $this->validPayload([
            'save_action' => 'save_and_create',
        ]));

        $response->assertRedirect(route('admin.sops.create'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sops', [
            'name' => 'Daily Hematology Analyzer Quality Control',
        ]);
    }

    public function test_user_with_sop_edit_can_update_sop(): void
    {
        $sop = Sop::factory()->create();
        $user = $this->createUserWithPermission('sops.edit');

        $response = $this->actingAs($user)->put(route('admin.sops.update', $sop), $this->validPayload([
            'name' => 'Updated SOP',
            'slug' => 'updated-sop',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $response->assertRedirect(route('admin.sops.edit', $sop));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('sops', [
            'id' => $sop->id,
            'name' => 'Updated SOP',
            'slug' => 'updated-sop',
            'updated_by' => $user->id,
        ]);
    }

    public function test_user_with_sop_delete_can_delete_sop(): void
    {
        $sop = Sop::factory()->create();
        $user = $this->createUserWithPermission('sops.delete');

        $response = $this->actingAs($user)->delete(route('admin.sops.destroy', $sop));

        $response->assertRedirect(route('admin.sops.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('sops', ['id' => $sop->id]);
    }

    public function test_sop_creation_is_audited(): void
    {
        $user = $this->createUserWithPermission('sops.create');

        $this->actingAs($user)->post(route('admin.sops.store'), $this->validPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'sops.created',
            'resource_type' => Sop::class,
        ]);
    }

    public function test_sop_update_is_audited(): void
    {
        $sop = Sop::factory()->create();
        $user = $this->createUserWithPermission('sops.edit');

        $this->actingAs($user)->put(route('admin.sops.update', $sop), $this->validPayload([
            'name' => 'Updated SOP',
            'slug' => 'updated-sop',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'sops.updated',
            'resource_type' => Sop::class,
            'resource_id' => $sop->id,
        ]);
    }

    public function test_sop_published_at_is_set_when_publishing(): void
    {
        $user = $this->createUserWithPermission('sops.create');

        $this->actingAs($user)->post(route('admin.sops.store'), $this->validPayload([
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $sop = Sop::query()->first();

        $this->assertNotNull($sop->published_at);
    }

    public function test_sop_categories_and_tags_are_synced(): void
    {
        $user = $this->createUserWithPermission('sops.create');
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user)->post(route('admin.sops.store'), $this->validPayload([
            'categories' => [$category->id],
            'tags' => [$tag->id],
        ]));

        $sop = Sop::query()->first();

        $this->assertTrue($sop->categories->contains($category));
        $this->assertTrue($sop->tags->contains($tag));
    }
}
