<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssessmentCertificatePreviewTest extends TestCase
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

    private function makeAssessment(array $overrides = []): Assessment
    {
        return Assessment::create([
            'title' => 'September ASCPI Challenge',
            'slug' => 'september-ascpi-challenge',
            'type' => 'challenge',
            'status' => 'scheduled',
            'duration_minutes' => 150,
            'total_questions' => 10,
            'max_attempts' => 1,
            'allow_practice' => true,
            'certificate_enabled' => true,
            'pass_percentage' => 70,
            ...$overrides,
        ]);
    }

    public function test_admin_can_view_assessment_certificate_preview(): void
    {
        $assessment = $this->makeAssessment();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.assessments.certificate-preview', $assessment));

        $response->assertStatus(200);
        $response->assertSee($assessment->title);
        $response->assertSee('Certificate Preview');
    }

    public function test_certificate_preview_uses_assessment_overrides(): void
    {
        $assessment = $this->makeAssessment([
            'cert_primary_color' => '#112233',
            'cert_signatory_name' => 'Dr. Jane Almonte',
            'cert_body_text' => 'for mastery of {assessment}',
        ]);

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.assessments.certificate-preview', $assessment));

        $response->assertStatus(200);
        $response->assertSee('#112233', false);
        $response->assertSee('Dr. Jane Almonte');
        $response->assertSee('for mastery of');
    }

    public function test_member_cannot_access_certificate_preview(): void
    {
        $assessment = $this->makeAssessment();
        $member = User::factory()->create(['is_active' => true, 'user_type' => 'member']);

        $response = $this->actingAs($member)
            ->get(route('admin.assessments.certificate-preview', $assessment));

        $response->assertForbidden();
    }

    public function test_edit_page_shows_preview_certificate_button_when_persisted(): void
    {
        $assessment = $this->makeAssessment();

        $response = $this->actingAs($this->adminUser())
            ->get(route('admin.assessments.edit', $assessment));

        $response->assertStatus(200);
        $response->assertSee('Preview certificate');
    }
}
