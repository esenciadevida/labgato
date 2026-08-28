<?php

namespace Tests\Feature\Admin\Interpretation;

use App\Enums\ResourceStatus;
use App\Models\InterpretationResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class InterpretationAdminValidationTest extends TestCase
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
            'short_description' => 'A practical guide.',
            'content' => '# Guide content',
            'clinical_context' => 'Correlate with clinical picture.',
            'related_analytes' => 'ALT, AST',
            'mandatory_disclaimer' => self::DEFAULT_DISCLAIMER,
            'status' => ResourceStatus::DRAFT->value,
            'categories' => [],
            'tags' => [],
        ], $overrides);
    }

    private function createUser(): User
    {
        return $this->createUserWithPermission('interpretation.create');
    }

    public function test_name_is_required(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['name' => '', 'slug' => ''])
        );

        $response->assertSessionHasErrors('name');
    }

    public function test_slug_must_be_unique(): void
    {
        InterpretationResource::factory()->create(['slug' => 'interpreting-elevated-liver-enzymes']);
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(
            route('admin.interpretation.store'),
            $this->validPayload(['name' => 'Another Guide', 'slug' => 'interpreting-elevated-liver-enzymes'])
        );

        $response->assertSessionHasErrors('slug');
    }

    public function test_content_is_required(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['content' => ''])
        );

        $response->assertSessionHasErrors('content');
    }

    public function test_mandatory_disclaimer_is_required(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['mandatory_disclaimer' => ''])
        );

        $response->assertSessionHasErrors('mandatory_disclaimer');
    }

    public function test_status_must_be_valid(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['status' => 'not-a-real-status'])
        );

        $response->assertSessionHasErrors('status');
    }

    public function test_reading_time_must_be_positive(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['reading_time_minutes' => '0'])
        );

        $response->assertSessionHasErrors('reading_time_minutes');
    }

    public function test_featured_image_rejects_disallowed_types(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['featured_image' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')])
        );

        $response->assertSessionHasErrors('featured_image');
    }

    public function test_featured_image_url_accepts_valid_http_url(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['featured_image_url' => 'https://media.example.com/path/to/image.jpg'])
        );

        $response->assertRedirect(route('admin.interpretation.index'));
        $response->assertSessionHasNoErrors('featured_image_url');
    }

    public function test_featured_image_url_rejects_invalid_urls(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['featured_image_url' => 'ftp://example.com/image.jpg'])
        );

        $response->assertSessionHasErrors('featured_image_url');
    }

    public function test_seo_title_has_max_length(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['seo_title' => str_repeat('a', 71)])
        );

        $response->assertSessionHasErrors('seo_title');
    }

    public function test_seo_description_has_max_length(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['seo_description' => str_repeat('a', 161)])
        );

        $response->assertSessionHasErrors('seo_description');
    }

    public function test_invalid_category_fails_validation(): void
    {
        $response = $this->actingAs($this->createUser())->post(
            route('admin.interpretation.store'),
            $this->validPayload(['categories' => [99999]])
        );

        $response->assertSessionHasErrors('categories.0');
    }
}
