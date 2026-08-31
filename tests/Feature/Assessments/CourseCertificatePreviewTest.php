<?php

namespace Tests\Feature\Assessments;

use App\Models\Course;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCertificatePreviewTest extends TestCase
{
    use RefreshDatabase;

    private function adminUser(): User
    {
        $user = User::factory()->create(['is_active' => true, 'user_type' => 'admin']);
        $role = Role::factory()->create();

        foreach (['assessments.manage', 'certificates.view'] as $slug) {
            $role->permissions()->attach(Permission::factory()->create([
                'name' => ucfirst(str_replace('.', ' ', $slug)),
                'slug' => $slug,
            ]));
        }

        $user->roles()->attach($role);

        return $user;
    }

    private function makeCourse(array $overrides = []): Course
    {
        return Course::create([
            'title' => 'Clinical Chemistry Refresher',
            'slug' => 'clinical-chemistry-refresher',
            'status' => 'published',
            'certificate_enabled' => true,
            'certificate_requires_all' => true,
            ...$overrides,
        ]);
    }

    public function test_admin_can_view_course_certificate_preview(): void
    {
        $course = $this->makeCourse();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.courses.certificate-preview', $course));

        $response->assertStatus(200);
        $response->assertSee($course->title);
        $response->assertSee('Certificate Preview');
    }

    public function test_certificate_preview_uses_course_overrides(): void
    {
        $course = $this->makeCourse([
            'cert_primary_color' => '#203040',
            'cert_signatory_name' => 'Dr. Carla Reyes',
            'cert_body_text' => 'for completing {course}',
        ]);

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.courses.certificate-preview', $course));

        $response->assertStatus(200);
        $response->assertSee('#203040', false);
        $response->assertSee('Dr. Carla Reyes');
        $response->assertSee('for completing');
    }

    public function test_member_cannot_access_course_certificate_preview(): void
    {
        $course = $this->makeCourse();
        $member = User::factory()->create(['is_active' => true, 'user_type' => 'member']);

        $response = $this->actingAs($member)
            ->get(route('admin.courses.certificate-preview', $course));

        $response->assertForbidden();
    }

    public function test_edit_page_shows_preview_certificate_button_when_persisted(): void
    {
        $course = $this->makeCourse();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.courses.edit', $course));

        $response->assertStatus(200);
        $response->assertSee('Preview certificate');
    }
}
