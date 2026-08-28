<?php

namespace Tests\Feature\Admin\Interpretation;

use App\Enums\ResourceStatus;
use App\Models\Category;
use App\Models\InterpretationResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class InterpretationAdminCrudTest extends TestCase
{
    use RefreshDatabase;

    private const DEFAULT_DISCLAIMER = 'Educational Interpretation Only: This content is for educational purposes and explains common laboratory result patterns. It does not constitute medical advice or diagnosis. Always interpret laboratory results in the context of the patient\'s clinical condition and consult with a qualified healthcare professional.';

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
            'name' => 'Interpreting Elevated Liver Enzymes',
            'slug' => 'interpreting-elevated-liver-enzymes',
            'short_description' => 'A practical guide to interpreting elevated liver enzymes.',
            'content' => "# Interpreting Elevated Liver Enzymes\n\nElevated ALT and AST indicate liver cell injury.",
            'clinical_context' => 'Correlate with the full clinical picture.',
            'related_analytes' => 'ALT, AST, ALP, GGT',
            'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
            'author_name' => 'Lab Education Team',
            'reading_time_minutes' => '6',
            'status' => ResourceStatus::DRAFT->value,
            'categories' => [],
            'tags' => [],
            'seo_title' => 'Interpreting Elevated Liver Enzymes',
            'seo_description' => 'A practical guide to interpreting elevated liver enzymes.',
        ], $overrides);
    }

    public function test_guest_cannot_access_interpretation_admin(): void
    {
        $response = $this->get(route('admin.interpretation.index'));

        $response->assertRedirect(route('auth.login'));
    }

    public function test_user_without_interpretation_permission_cannot_access_interpretation_admin(): void
    {
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->get(route('admin.interpretation.index'));

        $response->assertStatus(403);
    }

    public function test_user_with_interpretation_view_can_view_index(): void
    {
        $user = $this->createUserWithPermission('interpretation.view');
        InterpretationResource::factory()->create(['name' => 'Existing Interpretation']);

        $response = $this->actingAs($user)->get(route('admin.interpretation.index'));

        $response->assertStatus(200);
        $response->assertSee('Interpretation Resources');
        $response->assertSee('Existing Interpretation');
    }

    public function test_user_with_interpretation_create_can_view_create_page(): void
    {
        $user = $this->createUserWithPermission('interpretation.create');

        $response = $this->actingAs($user)->get(route('admin.interpretation.create'));

        $response->assertStatus(200);
        $response->assertSee('New Interpretation');
        $response->assertSee('Save and Create Another');
    }

    public function test_user_with_interpretation_edit_can_view_edit_page(): void
    {
        $interpretation = InterpretationResource::factory()->create();
        $user = $this->createUserWithPermission('interpretation.edit');

        $response = $this->actingAs($user)->get(route('admin.interpretation.edit', $interpretation));

        $response->assertStatus(200);
        $response->assertSee('Edit Interpretation');
    }

    public function test_user_with_interpretation_create_can_create_interpretation(): void
    {
        $user = $this->createUserWithPermission('interpretation.create');

        $response = $this->actingAs($user)->post(route('admin.interpretation.store'), $this->validPayload());

        $response->assertRedirect(route('admin.interpretation.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('interpretation_resources', [
            'name' => 'Interpreting Elevated Liver Enzymes',
            'slug' => 'interpreting-elevated-liver-enzymes',
            'status' => ResourceStatus::DRAFT->value,
            'created_by' => $user->id,
        ]);
    }

    public function test_save_and_create_redirects_back_to_create(): void
    {
        $user = $this->createUserWithPermission('interpretation.create');

        $response = $this->actingAs($user)->post(route('admin.interpretation.store'), $this->validPayload([
            'save_action' => 'save_and_create',
        ]));

        $response->assertRedirect(route('admin.interpretation.create'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('interpretation_resources', [
            'name' => 'Interpreting Elevated Liver Enzymes',
        ]);
    }

    public function test_related_analytes_are_saved_as_json_array(): void
    {
        $user = $this->createUserWithPermission('interpretation.create');

        $this->actingAs($user)->post(route('admin.interpretation.store'), $this->validPayload());

        $this->assertDatabaseHas('interpretation_resources', [
            'name' => 'Interpreting Elevated Liver Enzymes',
            'related_analytes' => json_encode(['ALT', 'AST', 'ALP', 'GGT']),
        ]);
    }

    public function test_user_with_interpretation_edit_can_update_interpretation(): void
    {
        $interpretation = InterpretationResource::factory()->create();
        $user = $this->createUserWithPermission('interpretation.edit');

        $response = $this->actingAs($user)->put(route('admin.interpretation.update', $interpretation), $this->validPayload([
            'name' => 'Updated Interpretation',
            'slug' => 'updated-interpretation',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $response->assertRedirect(route('admin.interpretation.edit', $interpretation));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('interpretation_resources', [
            'id' => $interpretation->id,
            'name' => 'Updated Interpretation',
            'slug' => 'updated-interpretation',
            'updated_by' => $user->id,
        ]);
    }

    public function test_user_with_interpretation_delete_can_delete_interpretation(): void
    {
        $interpretation = InterpretationResource::factory()->create();
        $user = $this->createUserWithPermission('interpretation.delete');

        $response = $this->actingAs($user)->delete(route('admin.interpretation.destroy', $interpretation));

        $response->assertRedirect(route('admin.interpretation.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('interpretation_resources', ['id' => $interpretation->id]);
    }

    public function test_interpretation_creation_is_audited(): void
    {
        $user = $this->createUserWithPermission('interpretation.create');

        $this->actingAs($user)->post(route('admin.interpretation.store'), $this->validPayload());

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'interpretation.created',
            'resource_type' => InterpretationResource::class,
        ]);
    }

    public function test_interpretation_update_is_audited(): void
    {
        $interpretation = InterpretationResource::factory()->create();
        $user = $this->createUserWithPermission('interpretation.edit');

        $this->actingAs($user)->put(route('admin.interpretation.update', $interpretation), $this->validPayload([
            'name' => 'Updated Interpretation',
            'slug' => 'updated-interpretation',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'interpretation.updated',
            'resource_type' => InterpretationResource::class,
            'resource_id' => $interpretation->id,
        ]);
    }

    public function test_interpretation_published_at_is_set_when_publishing(): void
    {
        $user = $this->createUserWithPermission('interpretation.create');

        $this->actingAs($user)->post(route('admin.interpretation.store'), $this->validPayload([
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $interpretation = InterpretationResource::query()->first();

        $this->assertNotNull($interpretation->published_at);
    }

    public function test_interpretation_categories_and_tags_are_synced(): void
    {
        $user = $this->createUserWithPermission('interpretation.create');
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        $this->actingAs($user)->post(route('admin.interpretation.store'), $this->validPayload([
            'categories' => [$category->id],
            'tags' => [$tag->id],
        ]));

        $interpretation = InterpretationResource::query()->first();

        $this->assertTrue($interpretation->categories->contains($category));
        $this->assertTrue($interpretation->tags->contains($tag));
    }

    public function test_featured_image_upload_stores_image_and_returns_path(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithPermission('interpretation.create');

        $this->actingAs($user)->post(route('admin.interpretation.store'), $this->validPayload([
            'featured_image' => UploadedFile::fake()->image('featured.jpg'),
        ]));

        $interpretation = InterpretationResource::query()->first();

        $this->assertNotNull($interpretation->featured_image);
        Storage::disk('public')->assertExists($interpretation->featured_image);
    }

    public function test_interpretation_featured_image_url_is_persisted_and_used_for_rendering(): void
    {
        $user = $this->createUserWithPermission('interpretation.create');

        $this->actingAs($user)->post(route('admin.interpretation.store'), $this->validPayload([
            'featured_image_url' => 'https://media.licdn.com/dms/image/example.jpg',
        ]));

        $interpretation = InterpretationResource::query()->first();

        $this->assertSame('https://media.licdn.com/dms/image/example.jpg', $interpretation->featured_image);
        $this->assertSame($interpretation->featured_image, $interpretation->featured_image_url);
    }

    public function test_interpretation_uploaded_image_takes_precedence_over_url(): void
    {
        Storage::fake('public');

        $user = $this->createUserWithPermission('interpretation.create');

        $this->actingAs($user)->post(route('admin.interpretation.store'), $this->validPayload([
            'featured_image' => UploadedFile::fake()->image('featured.jpg'),
            'featured_image_url' => 'https://example.com/remote.jpg',
        ]));

        $interpretation = InterpretationResource::query()->first();

        $this->assertNotNull($interpretation->featured_image);
        Storage::disk('public')->assertExists($interpretation->featured_image);
        $this->assertNotSame('https://example.com/remote.jpg', $interpretation->featured_image);
    }

    public function test_interpretation_updating_featured_image_from_upload_to_url_deletes_local_image(): void
    {
        Storage::fake('public');

        $imagePath = 'content/featured.jpg';
        Storage::disk('public')->put($imagePath, 'fake-content');

        $interpretation = InterpretationResource::factory()->create(['featured_image' => $imagePath]);
        $user = $this->createUserWithPermission('interpretation.edit');

        $this->actingAs($user)->put(route('admin.interpretation.update', $interpretation), $this->validPayload([
            'featured_image_url' => 'https://example.com/remote.jpg',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $interpretation->refresh();

        $this->assertSame('https://example.com/remote.jpg', $interpretation->featured_image);
        Storage::disk('public')->assertMissing($imagePath);
    }

    public function test_interpretation_featured_image_url_can_be_removed(): void
    {
        $interpretation = InterpretationResource::factory()->create(['featured_image' => 'https://example.com/remote.jpg']);
        $user = $this->createUserWithPermission('interpretation.edit');

        $this->actingAs($user)->put(route('admin.interpretation.update', $interpretation), $this->validPayload([
            'remove_featured_image' => '1',
            'status' => ResourceStatus::PUBLISHED->value,
        ]));

        $this->assertNull($interpretation->refresh()->featured_image);
    }

    public function test_interpretation_deletion_removes_featured_image(): void
    {
        Storage::fake('public');

        $imagePath = 'content/featured.jpg';
        Storage::disk('public')->put($imagePath, 'fake-content');

        $interpretation = InterpretationResource::factory()->create(['featured_image' => $imagePath]);
        $user = $this->createUserWithPermission('interpretation.delete');

        $this->actingAs($user)->delete(route('admin.interpretation.destroy', $interpretation));

        Storage::disk('public')->assertMissing($imagePath);
    }
}
