<?php

namespace App\Services;

use App\Models\Assessment;
use App\Models\Certificate;
use App\Models\ExamAttempt;
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
     * Lazily render and cache the PDF on disk - no queue worker needed.
     *
     * @return string relative path on the public disk
     */
    public function ensurePdf(Certificate $certificate): string
    {
        if ($certificate->pdf_path !== null && Storage::disk('public')->exists($certificate->pdf_path)) {
            return $certificate->pdf_path;
        }

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

        $relativePath = 'certificates/'.$certificate->certificate_number.'.pdf';

        Storage::disk('public')->makeDirectory('certificates');

        \Barryvdh\DomPDF\Facade\Pdf::loadView('certificates.pdf', [
            'certificate' => $certificate,
            'holderName' => $this->holderName($certificate),
            'qrBase64' => $qrBase64,
            'signatureBase64' => $this->signatureBase64(),
            'signatoryName' => (string) setting('certificate.signatory_name', 'Program Director'),
            'signatoryTitle' => (string) setting('certificate.signatory_title', 'Program Director, LabGOTO'),
            'logoBase64' => $this->logoBase64(),
            'primaryColor' => (string) setting('certificate.primary_color', '#1e3a8a'),
            'secondaryColor' => (string) setting('certificate.secondary_color', '#d4af37'),
            'completionText' => $this->completionText($certificate),
            'disclaimerText' => $this->disclaimerText($certificate),
            'showLogo' => setting_bool('certificate.show_logo', true),
            'showQr' => setting_bool('certificate.show_qr', true),
        ])
            ->save(Storage::disk('public')->path($relativePath));

        $certificate->forceFill(['pdf_path' => $relativePath])->save();

        return $relativePath;
    }

    /**
     * @return array{status: string, certificate: ?Certificate}
     */
    public function verify(string $code): array
    {
        $certificate = Certificate::query()
            ->where('verification_code', strtoupper(trim($code)))
            ->with(['user.profile', 'assessment'])
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

    private function holderName(Certificate $certificate): string
    {
        $profile = $certificate->user?->profile;

        if ($profile !== null && filled($profile->display_name)) {
            return $profile->display_name;
        }

        return $certificate->user?->name ?? 'Participant';
    }

    /**
     * The completion text below the holder name.
     * Uses the admin-configured body text with {assessment} placeholder, or the default.
     */
    private function completionText(Certificate $certificate): string
    {
        $custom = (string) setting('certificate.body_text', '');

        if ($custom !== '') {
            $text = e($custom);
            $text = str_replace('{assessment}', '<strong>' . e($certificate->assessment->title) . '</strong>', $text);
            return $text;
        }

        return 'for successfully completing the <strong>' . e($certificate->assessment->title) . '</strong>';
    }

    /**
     * The disclaimer text at the bottom of the certificate.
     * Uses the admin-configured disclaimer, or the default.
     */
    private function disclaimerText(Certificate $certificate): string
    {
        $custom = (string) setting('certificate.disclaimer_text', '');

        if ($custom !== '') {
            return nl2br(e($custom));
        }

        return 'This certificate acknowledges achievement in the ' . e($certificate->assessment->title) . ' administered by LabGOTO. '
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
