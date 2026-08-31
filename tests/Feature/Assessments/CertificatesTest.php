<?php

namespace Tests\Feature\Assessments;

use App\Models\Assessment;
use App\Models\Certificate;
use Database\Seeders\RolePermissionSeeder;
use App\Models\ExamAttempt;
use App\Models\Role;
use App\Models\User;
use App\Services\CertificateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CertificatesTest extends TestCase
{
    use RefreshDatabase;

    private CertificateService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(CertificateService::class);
    }

    private function makeAssessment(array $overrides = []): Assessment
    {
        return Assessment::create(array_merge([
            'title' => 'Certificate Challenge',
            'slug' => 'certificate-challenge',
            'type' => 'challenge',
            'status' => 'open',
            'duration_minutes' => 60,
            'total_questions' => 10,
            'max_attempts' => 1,
            'results_status' => 'available',
            'leaderboard_status' => 'locked',
            'pass_percentage' => 70,
            'opens_at' => now()->subHour(),
            'closes_at' => now()->addDay(),
        ], $overrides));
    }

    private function makeAttempt(
        User $user,
        Assessment $assessment,
        int $score,
        float $percentage,
        int $number = 1,
        string $status = 'submitted'
    ): ExamAttempt {
        static $sequence = 0;
        $sequence++;

        return ExamAttempt::create([
            'user_id' => $user->id,
            'assessment_id' => $assessment->id,
            'attempt_number' => $number,
            'mode' => 'official',
            'status' => $status,
            'question_ids' => [],
            'option_orders' => [],
            'started_at' => now()->subHour(),
            'ends_at' => now()->addMinutes(30),
            'submitted_at' => $status !== 'in_progress' ? now()->addSeconds($sequence) : null,
            'score' => $score,
            'percentage' => $percentage,
            'time_spent_seconds' => 600,
            'integrity_status' => 'normal',
        ]);
    }

    public function test_eligibility_tiers(): void
    {
        $released = $this->makeAssessment();

        // Distinction requires being in the top 10 percent of many attempts.
        $topUser = User::factory()->member()->create();
        $topAttempt = $this->makeAttempt($topUser, $released, 100, 100);
        foreach (range(1, 11) as $i) {
            $this->makeAttempt(User::factory()->member()->create(), $released, 10 - $i * 0, max(5, 60 - $i), $i + 1);
        }
        $this->assertSame('distinction', $this->service->eligibleType($topAttempt));

        // Achievement at or above pass mark when not top-10.
        $crowded = $this->makeAssessment(['slug' => 'cert-crowded', 'pass_percentage' => 70]);
        $achiever = User::factory()->member()->create();
        $achieverAttempt = $this->makeAttempt($achiever, $crowded, 80, 80);
        $this->makeAttempt(User::factory()->member()->create(), $crowded, 60, 60);
        $this->assertSame('achievement', $this->service->eligibleType($achieverAttempt));

        // Participation below the pass mark.
        $struggler = User::factory()->member()->create();
        $participationAttempt = $this->makeAttempt($struggler, $crowded, 40, 40, 3);
        $this->assertSame('participation', $this->service->eligibleType($participationAttempt));

        // Locked results block everything.
        $locked = $this->makeAssessment(['slug' => 'cert-locked', 'results_status' => 'locked']);
        $lockedAttempt = $this->makeAttempt(User::factory()->member()->create(), $locked, 90, 90);
        $this->assertNull($this->service->eligibleType($lockedAttempt));

        // Unsubmitted attempts are never eligible.
        $pending = $this->makeAttempt(
            User::factory()->member()->create(),
            $released,
            90,
            90,
            20,
            'in_progress'
        );
        $this->assertNull($this->service->eligibleType($pending));
    }

    public function test_issue_is_idempotent(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create();
        $attempt = $this->makeAttempt($user, $assessment, 80, 80);

        $first = $this->service->issue($attempt);
        $second = $this->service->issue($attempt);

        $this->assertNotNull($first);
        $this->assertSame($first->id, $second->id);
        $this->assertSame(1, Certificate::count());
    }

    public function test_certificate_number_format_and_uniqueness(): void
    {
        $assessment = $this->makeAssessment(['slug' => 'numbered']);

        $first = $this->service->issue(
            $this->makeAttempt(User::factory()->member()->create(), $assessment, 80, 80)
        );
        $second = $this->service->issue(
            $this->makeAttempt(User::factory()->member()->create(), $assessment, 70, 70, 2)
        );

        $this->assertSame('LGT-'.now()->format('Y').'-00001', $first->certificate_number);
        $this->assertSame('LGT-'.now()->format('Y').'-00002', $second->certificate_number);
        $this->assertNotSame($first->verification_code, $second->verification_code);
    }

    public function test_verification_code_charset_and_length(): void
    {
        $assessment = $this->makeAssessment(['slug' => 'charset']);

        foreach (range(1, 12) as $i) {
            $certificate = $this->service->issue(
                $this->makeAttempt(User::factory()->member()->create(), $assessment, 80 - $i, 80 - $i, $i)
            );
            $code = $certificate->verification_code;

            $this->assertSame(10, strlen($code));
            $this->assertSame(0, preg_match('/[0O1I]/', $code));
            $this->assertMatchesRegularExpression('/^[A-HJ-NP-Z2-9]{10}$/', $code);
        }
    }

    public function test_download_returns_pdf_and_caches_path(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create();
        $attempt = $this->makeAttempt($user, $assessment, 85, 85);

        $response = $this->actingAs($user)->get(route('certificates.download', $attempt));
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');

        $certificate = Certificate::where('exam_attempt_id', $attempt->id)->firstOrFail();
        $this->assertNotNull($certificate->pdf_path);

        // Second request reuses the cached file.
        $this->actingAs($user)->get(route('certificates.download', $attempt))->assertOk();
    }

    public function test_download_blocked_for_non_owner(): void
    {
        $assessment = $this->makeAssessment();
        $owner = User::factory()->member()->create();
        $intruder = User::factory()->member()->create();
        $attempt = $this->makeAttempt($owner, $assessment, 85, 85);

        $this->actingAs($intruder)
            ->get(route('certificates.download', $attempt))
            ->assertStatus(403);
    }

    public function test_verify_page_states(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create();
        $attempt = $this->makeAttempt($user, $assessment, 88, 88);
        $certificate = $this->service->issue($attempt);

        $valid = $this->get(route('certificates.verify', $certificate->verification_code))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Certificate Verified', $valid);
        $this->assertStringContainsString($user->name, $valid);
        $this->assertStringContainsString($certificate->certificate_number, $valid);

        $this->get(route('certificates.verify', 'ZZZZZZZZ99'))
            ->assertOk()
            ->assertSee('No certificate matches this code.');
    }

    public function test_revoked_certificate_behavior(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create();
        $attempt = $this->makeAttempt($user, $assessment, 75, 75);
        $certificate = $this->service->issue($attempt);

        $this->service->revoke($certificate, 'Suspected misconduct');

        $this->get(route('certificates.verify', $certificate->verification_code))
            ->assertOk()
            ->assertSee('This certificate has been revoked.');

        $this->actingAs($user)
            ->get(route('certificates.download', $attempt))
            ->assertStatus(410);

        $this->get(route('certificates.share', $certificate->verification_code))
            ->assertStatus(404);
    }

    public function test_share_page_renders_score_and_og_title(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create();
        $attempt = $this->makeAttempt($user, $assessment, 85, 85);
        $certificate = $this->service->issue($attempt);

        $content = $this->get(route('certificates.share', $certificate->verification_code))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('85.0%', $content);
        $this->assertStringContainsString('og:title', $content);
        $this->assertStringContainsString('wa.me/?text=', $content);
        $this->assertStringContainsString('sharer.php?u=', $content);
    }

    public function test_certificates_use_real_name_not_display_name(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create(['name' => 'Maria Santos']);
        $attempt = $this->makeAttempt($user, $assessment, 85, 85);
        $certificate = $this->service->issue($attempt);

        $user->profile()->updateOrCreate([], [
            'display_name' => 'MedNinja42',
            'show_on_leaderboard' => true,
        ]);

        $verify = $this->get(route('certificates.verify', $certificate->verification_code))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('Maria Santos', $verify);
        $this->assertStringNotContainsString('MedNinja42', $verify);

        $share = $this->get(route('certificates.share', $certificate->verification_code))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('Maria Santos', $share);
        $this->assertStringNotContainsString('MedNinja42', $share);

        $this->assertSame('Maria Santos', $this->service->viewData($certificate)['holderName']);
        $this->assertSame('Maria Santos', \App\Support\CertificateTemplateVariables::resolve($certificate)['{name}']);
    }

    public function test_public_pages_hide_holder_name_when_leaderboard_privacy_opt_out(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create(['name' => 'Private Patel']);
        $attempt = $this->makeAttempt($user, $assessment, 85, 85);
        $certificate = $this->service->issue($attempt);

        $user->profile()->updateOrCreate([], [
            'show_on_leaderboard' => false,
        ]);

        $verify = $this->get(route('certificates.verify', $certificate->verification_code))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('A LabGOTO participant', $verify);
        $this->assertStringNotContainsString('Private Patel', $verify);

        $share = $this->get(route('certificates.share', $certificate->verification_code))
            ->assertOk()
            ->getContent();
        $this->assertStringContainsString('A LabGOTO participant', $share);
        $this->assertStringNotContainsString('Private Patel', $share);

        $this->assertSame('Private Patel', $this->service->viewData($certificate)['holderName']);
    }

    public function test_verify_and_share_pages_are_never_publicly_cached(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create();
        $attempt = $this->makeAttempt($user, $assessment, 85, 85);
        $certificate = $this->service->issue($attempt);

        $this->get(route('certificates.verify', $certificate->verification_code))
            ->assertHeader('Cache-Control', 'no-store, private');

        $this->get(route('certificates.share', $certificate->verification_code))
            ->assertHeader('Cache-Control', 'no-store, private');
    }

    public function test_admin_revoke_sets_fields_and_enforces_permission(): void
    {
        $assessment = $this->makeAssessment();
        $participant = User::factory()->member()->create();
        $attempt = $this->makeAttempt($participant, $assessment, 66, 66);
        $certificate = $this->service->issue($attempt);

        $this->seed(RolePermissionSeeder::class);

        $superAdmin = User::factory()->create();
        $superAdmin->roles()->attach(
            Role::where('slug', 'super-administrator')->firstOrFail()
        );

        $limitedRole = Role::factory()->create(['slug' => 'support-staff']);
        $limitedAdmin = User::factory()->create(['user_type' => 'admin']);
        $limitedAdmin->roles()->attach($limitedRole->id);

        $this->actingAs($limitedAdmin)
            ->post(route('admin.certificates.revoke', $certificate), ['reason' => 'nope'])
            ->assertStatus(403);

        $this->assertNull($certificate->fresh()->revoked_at);

        $this->actingAs($superAdmin)
            ->post(route('admin.certificates.revoke', $certificate), ['reason' => 'Confirmed breach'])
            ->assertRedirect();

        $certificate->refresh();
        $this->assertNotNull($certificate->revoked_at);
        $this->assertSame('Confirmed breach', $certificate->revoke_reason);
    }

    public function test_pdf_uses_assessment_certificate_overrides(): void
    {
        $assessment = $this->makeAssessment([
            'cert_signatory_name' => 'Dr. Custom Signatory',
            'cert_signatory_title' => 'Course Lead, LabGOTO',
            'cert_body_text' => 'for completing the {assessment} beta trial',
            'cert_disclaimer_text' => 'Custom disclaimer for the beta trial.',
            'cert_primary_color' => '#ff0000',
            'cert_secondary_color' => '#00ff00',
            'cert_show_logo' => false,
            'cert_show_qr' => false,
        ]);
        $user = User::factory()->member()->create();
        $attempt = $this->makeAttempt($user, $assessment, 85, 85);

        $certificate = $this->service->issue($attempt);
        $data = $this->service->viewData($certificate);

        $this->assertSame('Dr. Custom Signatory', $data['signatoryName']);
        $this->assertSame('Course Lead, LabGOTO', $data['signatoryTitle']);
        $this->assertSame('#ff0000', $data['primaryColor']);
        $this->assertSame('#00ff00', $data['secondaryColor']);
        $this->assertFalse($data['showLogo']);
        $this->assertFalse($data['showQr']);
        $this->assertSame('for completing the Certificate Challenge beta trial', $data['completionText']);
        $this->assertStringContainsString('Custom disclaimer for the beta trial.', $data['disclaimerText']);
    }

    public function test_pdf_falls_back_to_global_template_settings(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create();
        $attempt = $this->makeAttempt($user, $assessment, 85, 85);

        $certificate = $this->service->issue($attempt);
        $data = $this->service->viewData($certificate);

        $this->assertSame('Program Director', $data['signatoryName']);
        $this->assertSame('#1e3a8a', $data['primaryColor']);
        $this->assertSame('#d4af37', $data['secondaryColor']);
        $this->assertTrue($data['showLogo']);
        $this->assertTrue($data['showQr']);
        $this->assertSame(
            'for successfully completing the <strong>Certificate Challenge</strong>',
            $data['completionText']
        );
    }

    public function test_certificate_pdf_does_not_display_score(): void
    {
        $assessment = $this->makeAssessment();
        $user = User::factory()->member()->create();
        $attempt = $this->makeAttempt($user, $assessment, 85, 85);

        $certificate = $this->service->issue($attempt);
        $html = view('certificates.pdf', $this->service->viewData($certificate))->render();

        $this->assertStringNotContainsString('Score', $html);
        $this->assertStringNotContainsString('92.5%', $html);
        $this->assertStringContainsString('for successfully completing', $html);
    }

    public function test_my_certificates_lists_own_certificates_only(): void
    {
        $assessment = $this->makeAssessment(['slug' => 'mine-only']);
        $other = $this->makeAssessment(['slug' => 'other-assessment']);

        $user = User::factory()->member()->create();
        $stranger = User::factory()->member()->create();

        $own = $this->service->issue($this->makeAttempt($user, $assessment, 80, 80));
        $foreign = $this->service->issue($this->makeAttempt($stranger, $other, 81, 81));

        $content = $this->actingAs($user)->get(route('certificates.my'))->assertOk()->getContent();

        $this->assertStringContainsString($own->certificate_number, $content);
        $this->assertStringNotContainsString($foreign->certificate_number, $content);
    }
}
