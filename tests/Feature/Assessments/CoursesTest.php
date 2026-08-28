<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursesTest extends TestCase
{
    use RefreshDatabase;

    private CertificateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CertificateService::class);
    }

    private function makeAssessment(string $slug, bool $certificateEnabled = true): Assessment
    {
        return Assessment::create([
            'title' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 60,
            'total_questions' => 10,
            'max_attempts' => 1,
            'results_status' => 'available',
            'leaderboard_status' => 'locked',
            'pass_percentage' => 70,
            'certificate_enabled' => $certificateEnabled,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ]);
    }

    private function makeCourse(array $overrides = []): Course
    {
        return Course::create(array_merge([
            'title' => 'Chemistry Refresher',
            'slug' => 'chemistry-refresher',
            'summary' => 'Complete the chemistry series.',
            'status' => 'published',
            'sort_order' => 1,
            'certificate_enabled' => true,
            'certificate_requires_all' => true,
        ], $overrides));
    }

    private function makeAttempt(
        User $user,
        Assessment $assessment,
        float $percentage,
        string $status = 'submitted'
    ): ExamAttempt {
        return ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => 1,
            'mode' => 'official',
            'status' => $status,
            'question_ids' => [],
            'option_orders' => [],
            'started_at' => now()->subHour(),
            'ends_at' => now()->addMinutes(30),
            'submitted_at' => $status !== 'in_progress' ? now() : null,
            'score' => (int) $percentage,
            'percentage' => $percentage,
            'time_spent_seconds' => 600,
            'integrity_status' => 'normal',
        ]);
    }

    private function member(): User
    {
        return User::factory()->member()->create();
    }

    public function test_course_index_lists_only_published_courses(): void
    {
        $published = $this->makeCourse();
        $this->makeCourse(['title' => 'Draft Course', 'slug' => 'draft-course', 'status' => 'draft']);

        $content = $this->actingAs($this->member())
            ->get(route('courses.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($published->title, $content);
        $this->assertStringNotContainsString('Draft Course', $content);
    }

    public function test_course_show_blocked_for_non_published(): void
    {
        $draft = $this->makeCourse(['status' => 'draft']);

        $this->actingAs($this->member())
            ->get(route('courses.show', $draft))
            ->assertStatus(404);
    }

    public function test_course_issues_certificate_only_after_all_required_pass(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $b = $this->makeAssessment('chem-electrolytes');
        $course = $this->makeCourse();
        $course->assessments()->attach([
            $a->id => ['required' => true, 'sort_order' => 1],
            $b->id => ['required' => true, 'sort_order' => 2],
        ]);
        $user = $this->member();

        $this->makeAttempt($user, $a, 80);

        $this->actingAs($user)->get(route('courses.show', $course));

        $this->assertNull($this->service->issueForCourse($course, $user));
        $this->assertSame(0, Certificate::where('course_id', $course->id)->count());

        $this->makeAttempt($user, $b, 90);

        $certificate = $this->service->issueForCourse($course, $user);

        $this->assertNotNull($certificate);
        $this->assertSame($course->id, $certificate->course_id);
        $this->assertNull($certificate->assessment_id);
        $this->assertNull($certificate->exam_attempt_id);
        $this->assertSame('achievement', $certificate->type);
        $this->assertSame(85.0, (float) $certificate->score_percent);
        $this->assertNull($certificate->rank_at_issue);
    }

    public function test_course_certificate_requires_results_released(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $b = $this->makeAssessment('chem-electrolytes', true);
        $b->forceFill(['results_status' => 'locked'])->save();

        $course = $this->makeCourse();
        $course->assessments()->attach([
            $a->id => ['required' => true, 'sort_order' => 1],
            $b->id => ['required' => true, 'sort_order' => 2],
        ]);
        $user = $this->member();

        $this->makeAttempt($user, $a, 80);
        $this->makeAttempt($user, $b, 90);

        $this->assertFalse($this->service->courseComplete($course, $user));
        $this->assertNull($this->service->issueForCourse($course, $user));
    }

    public function test_course_issue_is_idempotent(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $course = $this->makeCourse();
        $course->assessments()->attach($a->id, ['required' => true, 'sort_order' => 1]);
        $user = $this->member();
        $this->makeAttempt($user, $a, 88);

        $first = $this->service->issueForCourse($course, $user);
        $second = $this->service->issueForCourse($course, $user);

        $this->assertNotNull($first);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Certificate::where('course_id', $course->id)->count());
    }

    public function test_optional_assessments_are_not_required_for_completion(): void
    {
        $required = $this->makeAssessment('chem-acids');
        $optional = $this->makeAssessment('chem-bonus');
        $course = $this->makeCourse();
        $course->assessments()->attach([
            $required->id => ['required' => true, 'sort_order' => 1],
            $optional->id => ['required' => false, 'sort_order' => 2],
        ]);
        $user = $this->member();

        $this->makeAttempt($user, $required, 85);
        $this->makeAttempt($user, $optional, 30);

        $this->assertTrue($this->service->courseComplete($course, $user));
    }

    public function test_course_download_issues_and_returns_pdf(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $course = $this->makeCourse();
        $course->assessments()->attach($a->id, ['required' => true, 'sort_order' => 1]);
        $user = $this->member();
        $this->makeAttempt($user, $a, 85);

        $response = $this->actingAs($user)->get(route('courses.certificate-download', $course));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $certificate = Certificate::where('course_id', $course->id)->where('user_id', $user->id)->firstOrFail();
        $this->assertNotNull($certificate->pdf_path);
    }

    public function test_course_download_redirects_when_not_complete(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $b = $this->makeAssessment('chem-electrolytes');
        $course = $this->makeCourse();
        $course->assessments()->attach([
            $a->id => ['required' => true, 'sort_order' => 1],
            $b->id => ['required' => true, 'sort_order' => 2],
        ]);
        $user = $this->member();
        $this->makeAttempt($user, $a, 80);

        $this->actingAs($user)
            ->from(route('courses.show', $course))
            ->get(route('courses.certificate-download', $course))
            ->assertRedirect(route('courses.show', $course))
            ->assertSessionHas('error');

        $this->assertSame(0, Certificate::where('course_id', $course->id)->count());
    }

    public function test_course_certificate_disabled_never_issues(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $course = $this->makeCourse(['certificate_enabled' => false]);
        $course->assessments()->attach($a->id, ['required' => true, 'sort_order' => 1]);
        $user = $this->member();
        $this->makeAttempt($user, $a, 90);

        $this->assertNull($this->service->issueForCourse($course, $user));
        $this->assertSame(0, Certificate::where('course_id', $course->id)->count());
    }

    public function test_certificate_pdf_uses_course_overrides_through_template_variables(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $course = $this->makeCourse([
            'cert_signatory_name' => 'Dr. Dean',
            'cert_signatory_title' => 'Dean of Laboratory Science',
            'cert_body_text' => 'for completing the {course} series',
            'cert_disclaimer_text' => 'Course-specific disclaimer.',
            'cert_primary_color' => '#123456',
            'cert_show_qr' => false,
        ]);
        $course->assessments()->attach($a->id, ['required' => true, 'sort_order' => 1]);
        $user = $this->member();
        $this->makeAttempt($user, $a, 85);

        $certificate = $this->service->issueForCourse($course, $user);
        $data = $this->service->viewData($certificate);

        $this->assertSame('Dr. Dean', $data['signatoryName']);
        $this->assertSame('Dean of Laboratory Science', $data['signatoryTitle']);
        $this->assertSame('#123456', $data['primaryColor']);
        $this->assertFalse($data['showQr']);
        $this->assertSame('for completing the Chemistry Refresher series', $data['completionText']);
        $this->assertStringContainsString('Course-specific disclaimer.', $data['disclaimerText']);
    }

    public function test_hub_page_shows_published_courses(): void
    {
        $course = $this->makeCourse();
        $course->assessments()->attach($this->makeAssessment('chem-acids')->id, ['required' => true, 'sort_order' => 1]);

        $content = $this->actingAs($this->member())
            ->get(route('assessments.index'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString($course->title, $content);
        $this->assertStringContainsString('View course', $content);
    }

    public function test_my_certificates_lists_course_certificates(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $course = $this->makeCourse();
        $course->assessments()->attach($a->id, ['required' => true, 'sort_order' => 1]);
        $user = $this->member();
        $this->makeAttempt($user, $a, 85);

        $certificate = $this->service->issueForCourse($course, $user);

        $content = $this->actingAs($user)->get(route('certificates.my'))->assertOk()->getContent();

        $this->assertStringContainsString($course->title, $content);
        $this->assertStringContainsString('Course', $content);
        $this->assertStringContainsString($certificate->certificate_number, $content);
        $this->assertStringContainsString(route('courses.certificate-download', $course), $content);
    }

    public function test_course_certificate_verify_and_share_pages_render(): void
    {
        $a = $this->makeAssessment('chem-acids');
        $course = $this->makeCourse();
        $course->assessments()->attach($a->id, ['required' => true, 'sort_order' => 1]);
        $user = $this->member();
        $this->makeAttempt($user, $a, 85);

        $certificate = $this->service->issueForCourse($course, $user);

        $verify = $this->get(route('certificates.verify', $certificate->verification_code))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString($course->title, $verify);

        $share = $this->get(route('certificates.share', $certificate->verification_code))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString($course->title, $share);
        $this->assertStringContainsString('Course Completion', $share);
    }
}