<?php

namespace Tests\Feature\Admin;

use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\ExamAttempt;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseAdminTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $this->seed(RolePermissionSeeder::class);

        $admin = User::factory()->create(['user_type' => 'admin']);
        $admin->roles()->attach(
            Role::where('slug', 'super-administrator')->firstOrFail()
        );

        return $admin;
    }

    private function makeAssessment(string $slug): Assessment
    {
        return Assessment::create([
            'title' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 60,
            'total_questions' => 10,
            'max_attempts' => 1,
            'pass_percentage' => 70,
            'certificate_enabled' => true,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ]);
    }

    public function test_index_requires_permissions(): void
    {
        $this->actingAs(User::factory()->create(['user_type' => 'member']))
            ->get(route('admin.courses.index'))
            ->assertStatus(403);

        $this->actingAs($this->admin())
            ->get(route('admin.courses.index'))
            ->assertOk()
            ->assertSee('New Course');
    }

    public function test_create_page_renders_assessment_picker(): void
    {
        $this->makeAssessment('chem-acids');

        $content = $this->actingAs($this->admin())
            ->get(route('admin.courses.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Chem Acids', $content);
        $this->assertStringContainsString('Include', $content);
    }

    public function test_store_creates_course_and_links_assessments(): void
    {
        $this->makeAssessment('chem-acids');
        $this->makeAssessment('chem-electrolytes');

        $this->actingAs($this->admin())
            ->post(route('admin.courses.store'), [
                'title' => 'Chemistry Refresher',
                'slug' => 'existing-custom-slug',
                'summary' => 'Summer series.',
                'status' => 'published',
                'sort_order' => 3,
                'certificate_enabled' => '1',
                'certificate_requires_all' => '1',
                'assessments' => [
                    [
                        'assessment_id' => 1,
                        'include' => '1',
                        'required' => '1',
                        'sort_order' => '1',
                    ],
                    [
                        'assessment_id' => 2,
                        'include' => '1',
                        'required' => '0',
                        'sort_order' => '2',
                    ],
                ],
            ])
            ->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success');

        $course = Course::where('slug', 'existing-custom-slug')->firstOrFail();

        $this->assertSame('Chemistry Refresher', $course->title);
        $this->assertSame('published', $course->status);
        $this->assertSame(3, $course->sort_order);
        $this->assertTrue($course->certificate_enabled);

        $links = $course->assessments()->get()->keyBy('id');

        $this->assertSame(2, $links->count());
        $this->assertSame(true, (bool) $links[1]->pivot->required);
        $this->assertSame(false, (bool) $links[2]->pivot->required);
        $this->assertSame(2, $links[2]->pivot->sort_order);
    }

    public function test_store_excludes_unchecked_assessments(): void
    {
        $this->makeAssessment('chem-acids');
        $this->makeAssessment('chem-electrolytes');

        $this->actingAs($this->admin())
            ->post(route('admin.courses.store'), [
                'title' => 'Single Assessment Course',
                'slug' => 'single-assessment-course',
                'status' => 'draft',
                'assessments' => [
                    ['assessment_id' => 1, 'include' => '1', 'required' => '1', 'sort_order' => '1'],
                    ['assessment_id' => 2, 'include' => '0', 'required' => '0', 'sort_order' => '2'],
                ],
            ])
            ->assertRedirect(route('admin.courses.index'));

        $course = Course::where('slug', 'single-assessment-course')->firstOrFail();

        $this->assertSame(1, $course->assessments()->count());
        $this->assertSame(1, $course->assessments()->first()->id);
    }

    public function test_store_blank_certificate_fields_inherit_globals(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.courses.store'), [
                'title' => 'Default Cert Course',
                'slug' => 'default-cert-course',
                'status' => 'draft',
                'certificate_enabled' => '1',
                'certificate_requires_all' => '1',
                'cert_body_text' => '',
                'cert_disclaimer_text' => '',
                'cert_primary_color' => '',
                'cert_secondary_color' => '',
                'cert_signatory_name' => '',
                'cert_signatory_title' => '',
                'cert_show_logo' => '',
                'cert_show_qr' => '',
            ])
            ->assertRedirect(route('admin.courses.index'));

        $this->assertDatabaseHas('courses', [
            'slug' => 'default-cert-course',
            'cert_body_text' => null,
            'cert_disclaimer_text' => null,
            'cert_primary_color' => null,
            'cert_secondary_color' => null,
            'cert_signatory_name' => null,
            'cert_signatory_title' => null,
            'cert_show_logo' => null,
            'cert_show_qr' => null,
        ]);
    }

    public function test_store_rejects_invalid_certificate_color(): void
    {
        $this->actingAs($this->admin())
            ->post(route('admin.courses.store'), [
                'title' => 'Bad Color Course',
                'slug' => 'bad-color-course',
                'status' => 'draft',
                'certificate_enabled' => '1',
                'cert_primary_color' => 'red',
            ])
            ->assertSessionHasErrors('cert_primary_color');

        $this->assertDatabaseMissing('courses', ['slug' => 'bad-color-course']);
    }

    public function test_store_rejects_duplicate_slug(): void
    {
        $this->makeCourseInstance('chemistry-refresher');

        $this->actingAs($this->admin())
            ->post(route('admin.courses.store'), [
                'title' => 'Chemistry Refresher Two',
                'slug' => 'chemistry-refresher',
                'status' => 'draft',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_update_syncs_assessment_links_and_allows_own_slug(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $b = $this->makeAssessment('chem-electrolytes');
        $course = $this->makeCourseInstance('target-course');
        $course->assessments()->attach($a->id, ['required' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin())
            ->put(route('admin.courses.update', $course), [
                'title' => 'Renamed Course',
                'slug' => 'target-course',
                'status' => 'published',
                'sort_order' => 9,
                'certificate_enabled' => '1',
                'certificate_requires_all' => '1',
                'assessments' => [
                    ['assessment_id' => $b->id, 'include' => '1', 'required' => '1', 'sort_order' => '1'],
                ],
            ])
            ->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success');

        $course->refresh();

        $this->assertSame('Renamed Course', $course->title);
        $this->assertSame(9, $course->sort_order);
        $this->assertSame([$b->id], $course->assessments()->pluck('assessments.id')->all());
    }

    public function test_destroy_blocks_when_certificates_exist(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $course = $this->makeCourseInstance('certified-course');
        $course->assessments()->attach($a->id, ['required' => true, 'sort_order' => 1]);
        $member = User::factory()->member()->create();

        Certificate::create([
            'user_id' => $member->id,
            'course_id' => $course->id,
            'certificate_number' => 'LGT-'.now()->format('Y').'-00001',
            'verification_code' => 'TESTCODE01',
            'type' => 'achievement',
            'score_percent' => 100.0,
            'issued_at' => now(),
        ]);

        $this->actingAs($this->admin())
            ->delete(route('admin.courses.destroy', $course))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_destroy_removes_course_and_links(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $course = $this->makeCourseInstance('deletable-course');
        $course->assessments()->attach($a->id, ['required' => true, 'sort_order' => 1]);

        $this->actingAs($this->admin())
            ->delete(route('admin.courses.destroy', $course))
            ->assertRedirect(route('admin.courses.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('courses', ['id' => $course->id]);
        $this->assertDatabaseMissing('course_assessment', ['course_id' => $course->id]);
    }

    private function makeCourseInstance(string $slug): Course
    {
        return Course::create([
            'title' => 'Helper Course',
            'slug' => $slug,
            'status' => 'draft',
            'sort_order' => 1,
            'certificate_enabled' => true,
            'certificate_requires_all' => true,
        ]);
    }
}