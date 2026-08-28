<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\ExamAttempt;
use App\Models\User;
use App\Support\CertificateTemplateVariables;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificateService
{
    private const CODE_CHARSET = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

    public function __construct(private ResultsService $results)
    {
    }

    /**
     * Highest tier the attempt qualifies for, or null when certificates
     * are not available for it yet.
     */
    public function eligibleType(ExamAttempt $attempt): ?string
    {
        if (! in_array($attempt->status, ['submitted', 'auto_submitted'], true)) {
            return null;
        }

        $assessment = $attempt->assessment;

        if ($assessment === null || ! $this->results->isReleased($assessment)) {
            return null;
        }

        if (! $assessment->certificate_enabled) {
            return null;
        }

        if ($this->results->topPercent($attempt) <= 10) {
            return 'distinction';
        }

        if ($assessment->pass_percentage !== null
            && (float) $attempt->percentage >= (float) $assessment->pass_percentage) {
            return 'achievement';
        }

        return 'participation';
    }

    /**
     * Issue a certificate once per attempt; repeated calls are no-ops.
     */
    public function issue(ExamAttempt $attempt): ?Certificate
    {
        $existing = Certificate::query()->where('exam_attempt_id', $attempt->id)->first();
        if ($existing !== null) {
            return $existing;
        }

        $type = $this->eligibleType($attempt);
        if ($type === null) {
            return null;
        }

        $number = $this->nextCertificateNumber();

        $certificate = Certificate::create([
            'exam_attempt_id' => $attempt->id,
            'user_id' => $attempt->user_id,
            'assessment_id' => $attempt->assessment_id,
            'course_id' => null,
            'certificate_number' => $number,
            'verification_code' => $this->generateVerificationCode(),
            'type' => $type,
            'score_percent' => (float) $attempt->percentage,
            'rank_at_issue' => $this->results->rank($attempt),
            'issued_at' => now(),
        ]);

        return $certificate;
    }

    /**
     * Issue a course completion certificate once a user has a passing
     * official attempt on every required assessment. Idempotent per
     * user + course.
     */
    public function issueForCourse(Course $course, User $user): ?Certificate
    {
        $existing = Certificate::query()
            ->where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        if (! $course->certificate_enabled) {
            return null;
        }

        $passing = $this->requiredPassingAttempts($course, $user);

        if ($passing === null) {
            return null;
        }

        $certificate = Certificate::create([
            'exam_attempt_id' => null,
            'user_id' => $user->id,
            'assessment_id' => null,
            'course_id' => $course->id,
            'certificate_number' => $this->nextCertificateNumber(),
            'verification_code' => $this->generateVerificationCode(),
            'type' => 'achievement',
            'score_percent' => round((float) $passing->avg('percentage'), 2),
            'rank_at_issue' => null,
            'issued_at' => now(),
        ]);

        return $certificate;
    }

    /**
     * Whether the user has completed (passed) every required assessment in a
     * course. Results must be released for each linked assessment.
     */
    public function courseComplete(Course $course, User $user): bool
    {
        return $this->requiredPassingAttempts($course, $user) !== null;
    }

    /**
     * Lazily render and cache the PDF on disk - no queue worker needed.
     *
     * @return string relative path on the public disk
     */
    public function ensurePdf(Certificate $certificate): string
    {
        if ($certificate->pdf_path !== null && Storage::disk('public')->exists($certificate->pdf_path)) {
            return $certificate->pdf_path;
        }

        $relativePath = 'certificates/'.$certificate->certificate_number.'.pdf';

        Storage::disk('public')->makeDirectory('certificates');

        \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf', $this->viewData($certificate))
            ->save(Storage::disk('public')->path($relativePath));

        $certificate->forceFill(['pdf_path' => $relativePath])->save();

        return $relativePath;
    }

    /**
     * Every variable the certificate PDF view needs. Exposed for tests so the
     * per-assessment override / global fallback chain can be asserted directly
     * without parsing the compressed PDF stream.
     *
     * @return array<string, mixed>
     */
    public function viewData(Certificate $certificate): array
    {
        $qrBase64 = null;

        try {
            $qrBase64 = base64_encode(
                QrCode::format('png')->size(300)->generate($certificate->verifyUrl())
            );
        } catch (\Throwable) {
            // GD or the QR backend is unavailable; the PDF falls back to
            // printing the verification URL as text.
            $qrBase64 = null;
        }

        return [
            'certificate' => $certificate,
            'holderName' => $this->holderName($certificate),
            'qrBase64' => $qrBase64,
            'signatureBase64' => $this->signatureBase64(),
            'signatoryName' => $this->signatoryName($certificate),
            'signatoryTitle' => $this->signatoryTitle($certificate),
            'logoBase64' => $this->logoBase64(),
            'primaryColor' => $this->primaryColor($certificate),
            'secondaryColor' => $this->secondaryColor($certificate),
            'completionText' => $this->completionText($certificate),
            'disclaimerText' => $this->disclaimerText($certificate),
            'showLogo' => $this->showLogo($certificate),
            'showQr' => $this->showQr($certificate),
        ];
    }

    /**
     * @return array{status: string, certificate: ?Certificate}
     */
    public function verify(string $code): array
    {
        $certificate = Certificate::query()
            ->where('verification_code', strtoupper(trim($code)))
            ->with(['user.profile', 'assessment', 'course'])
            ->first();

        if ($certificate === null) {
            return ['status' => 'not_found', 'certificate' => null];
        }

        return [
            'status' => $certificate->isRevoked() ? 'revoked' : 'valid',
            'certificate' => $certificate,
        ];
    }

    public function revoke(Certificate $certificate, ?string $reason = null): Certificate
    {
        $certificate->forceFill([
            'revoked_at' => now(),
            'revoke_reason' => $reason,
        ])->save();

        return $certificate;
    }

    /**
     * Delete the cached PDF so the next ensurePdf() call regenerates it.
     */
    public function regenerate(Certificate $certificate): void
    {
        if ($certificate->pdf_path !== null && Storage::disk('public')->exists($certificate->pdf_path)) {
            Storage::disk('public')->delete($certificate->pdf_path);
        }

        $certificate->forceFill(['pdf_path' => null])->save();
    }

    private function holderName(Certificate $certificate): string
    {
        $profile = $certificate->user?->profile;

        if ($profile !== null && filled($profile->display_name)) {
            return $profile->display_name;
        }

        return $certificate->user?->name ?? 'Participant';
    }

    /**
     * Course certificates draw their overrides from the course first, then the
     * assessment, then the global certificate template settings. Assessement
     * certificates fall back straight to the globals (course_id is null).
     */
    private function source(Certificate $certificate)
    {
        if ($certificate->course_id !== null) {
            return $certificate->course;
        }

        return $certificate->assessment;
    }

    private function primaryColor(Certificate $certificate): string
    {
        return (string) ($this->source($certificate)?->cert_primary_color
            ?? setting('certificate.primary_color', '#1e3a8a'));
    }

    private function secondaryColor(Certificate $certificate): string
    {
        return (string) ($this->source($certificate)?->cert_secondary_color
            ?? setting('certificate.secondary_color', '#d4af37'));
    }

    private function signatoryName(Certificate $certificate): string
    {
        return (string) ($this->source($certificate)?->cert_signatory_name
            ?? setting('certificate.signatory_name', 'Program Director'));
    }

    private function signatoryTitle(Certificate $certificate): string
    {
        return (string) ($this->source($certificate)?->cert_signatory_title
            ?? setting('certificate.signatory_title', 'Program Director, LabGOTO'));
    }

    private function showLogo(Certificate $certificate): bool
    {
        return $this->source($certificate)?->cert_show_logo
            ?? setting_bool('certificate.show_logo', true);
    }

    private function showQr(Certificate $certificate): bool
    {
        return $this->source($certificate)?->cert_show_qr
            ?? setting_bool('certificate.show_qr', true);
    }

    /**
     * The completion text below the holder name.
     * Uses the source-level body text (or the admin-configured global one)
     * with system insertion placeholders, or the default.
     */
    private function completionText(Certificate $certificate): string
    {
        $custom = $this->source($certificate)?->cert_body_text
            ?? (string) setting('certificate.body_text', '');

        if ($custom !== '') {
            $vars = CertificateTemplateVariables::resolve($certificate);
            $text = CertificateTemplateVariables::replace($custom, $vars);
            return nl2br(e($text));
        }

        if ($certificate->course !== null) {
            return 'for successfully completing the <strong>' . e($certificate->course->title) . '</strong> course';
        }

        return 'for successfully completing the <strong>' . e($certificate->assessment?->title ?? 'Assessment') . '</strong>';
    }

    /**
     * The disclaimer text at the bottom of the certificate.
     * Uses the source-level (or global) disclaimer with system insertion
     * placeholders, or the default.
     */
    private function disclaimerText(Certificate $certificate): string
    {
        $custom = $this->source($certificate)?->cert_disclaimer_text
            ?? (string) setting('certificate.disclaimer_text', '');

        if ($custom !== '') {
            $vars = CertificateTemplateVariables::resolve($certificate);
            $text = CertificateTemplateVariables::replace($custom, $vars);
            return nl2br(e($text));
        }

        $subject = $certificate->course?->title ?? $certificate->assessment?->title ?? 'Assessment';

        return 'This certificate acknowledges achievement in the ' . e($subject) . ' administered by LabGOTO. '
            . 'It is not an ASCP certification nor an official ASCP credential. Verify authenticity by scanning the QR code above '
            . 'or visiting ' . e($certificate->verifyUrl()) . '.';
    }

    /**
     * Base64-encoded signature image (if one is configured), else null.
     */
    private function signatureBase64(): ?string
    {
        $path = (string) setting('certificate.signature_path', '');

        if ($path === '' || ! Storage::disk('public')->exists($path)) {
            return null;
        }

        return base64_encode(Storage::disk('public')->get($path));
    }

    /**
     * A descriptive, industry-style download filename:
     * Certificate-of-{Type}-{Holder}-{Year}.pdf
     */
    public function filename(Certificate $certificate): string
    {
        $type = ucfirst($certificate->type);
        $holder = preg_replace('/[^A-Za-z0-9]+/', '-', $this->holderName($certificate));
        $holder = trim($holder ?: 'Participant', '-');

        return sprintf('Certificate-of-%s-%s-%d.pdf', $type, $holder, (int) $certificate->issued_at?->year ?? now()->year);
    }

    /**
     * PNG version of the LabGOTO logo, rasterized once from the SVG and
     * cached on the public disk (Dompdf cannot render SVG images reliably).
     * Returns null when the rasterizer is unavailable, so the view falls back
     * to a text wordmark.
     */
    private function logoBase64(): ?string
    {
        $disk = Storage::disk('public');
        $path = 'certificates/logo.png';

        if ($disk->exists($path)) {
            return base64_encode((string) $disk->get($path));
        }

        $png = $this->rasterizeLogo();

        if ($png === null) {
            return null;
        }

        $disk->put($path, $png);

        return base64_encode($png);
    }

    private function rasterizeLogo(): ?string
    {
        $path = public_path('assets/LabGoTo_logo.svg');

        if (! extension_loaded('imagick') || ! is_file($path)) {
            return null;
        }

        try {
            $img = new \Imagick();
            $img->readImageBlob((string) file_get_contents($path));
            $img->setImageFormat('png');
            $img->setBackgroundColor(new \ImagickPixel('transparent'));
            $img->setImageAlphaChannel(\Imagick::ALPHACHANNEL_SET);

            if ($img->getImageWidth() > 720) {
                $img->resizeImage(720, 0, \Imagick::FILTER_LANCZOS, 1);
            }

            return $img->getImageBlob();
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return Collection<int, ExamAttempt>|null the passing attempt per
     *         required assessment, or null when the course is not complete.
     */
    private function requiredPassingAttempts(Course $course, User $user): ?Collection
    {
        $required = $course->requiredAssessments()->get();

        if ($required->isEmpty()) {
            return null;
        }

        $passing = collect();

        foreach ($required as $assessment) {
            if (! $this->results->isReleased($assessment)) {
                return null;
            }

            $attempt = ExamAttempt::query()
                ->where('user_id', $user->id)
                ->where('assessment_id', $assessment->id)
                ->where('mode', 'official')
                ->whereIn('status', ['submitted', 'auto_submitted'])
                ->orderByDesc('percentage')
                ->first();

            if ($attempt === null || $attempt->percentage === null) {
                return null;
            }

            $pass = $assessment->pass_percentage === null
                || (float) $attempt->percentage >= (float) $assessment->pass_percentage;

            if (! $pass) {
                return null;
            }

            $passing->push($attempt);
        }

        return $passing;
    }

    private function nextCertificateNumber(): string
    {
        $year = now()->format('Y');

        for ($attempt = 0; $attempt < 3; $attempt++) {
            $sequence = Certificate::query()->count() + 1;
            $number = sprintf('LGT-%s-%05d', $year, $sequence);

            if (! Certificate::query()->where('certificate_number', $number)->exists()) {
                return $number;
            }
        }

        // Practically unreachable; fall back to a timestamped suffix.
        return sprintf('LGT-%s-%05d', $year, random_int(10000, 99999));
    }

    private function generateVerificationCode(): string
    {
        do {
            $code = '';
            for ($i = 0; $i < 10; $i++) {
                $code .= self::CODE_CHARSET[random_int(0, strlen(self::CODE_CHARSET) - 1)];
            }
        } while (Certificate::query()->where('verification_code', $code)->exists());

        return $code;
    }
}