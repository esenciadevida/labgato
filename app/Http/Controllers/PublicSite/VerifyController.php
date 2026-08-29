<?php

namespace App\Http\Controllers\PublicSite;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\View\View;

class VerifyController extends Controller
{
    public function __construct(private CertificateService $certificates)
    {
    }

    public function verify(string $code): View
    {
        $result = $this->certificates->verify($code);

        $credentialSchema = $result['status'] === 'valid'
            ? json_encode([
                '@context' => 'https://schema.org',
                '@type' => 'EducationalOccupationalCredential',
                'name' => $result['certificate']->label(),
                'credentialCategory' => ucfirst($result['certificate']->type),
                'description' => 'LabGOTO certificate for '.$result['certificate']->assessment?->title.'.',
                'dateIssued' => $result['certificate']->issued_at?->toIso8601String(),
                'awardedBy' => ['@type' => 'Organization', 'name' => config('app.name')],
                '@id' => $result['certificate']->verifyUrl(),
                'identifier' => $result['certificate']->certificate_number,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
            : null;

        return view('certificates.verify', [
            'status' => $result['status'],
            'certificate' => $result['certificate'],
            'credentialSchema' => $credentialSchema,
            'holderName' => $result['status'] === 'valid'
            ? $this->certificates->publicHolderName($result['certificate'])
            : null,
        ]);
    }

    public function share(string $code): View
    {
        $certificate = Certificate::query()
            ->where('verification_code', strtoupper(trim($code)))
            ->with(['user.profile', 'assessment', 'course'])
            ->first();

        if ($certificate === null || $certificate->isRevoked()) {
            abort(404);
        }

        $results = app(\App\Services\ResultsService::class);

        $attempt = $certificate->attempt;
        $rank = $attempt !== null ? $results->rank($attempt) : null;
        $topPercent = $attempt !== null ? $results->topPercent($attempt) : 100;
        $participants = $attempt !== null ? $results->officialAttempts($attempt->assessment)->count() : 0;

        $subject = $certificate->course?->title ?? $certificate->assessment?->title;

        $shareText = sprintf(
            'I scored %.1f%% in the %s on LabGOTO',
            (float) $certificate->score_percent,
            $subject
        );

        $shareUrl = $certificate->shareUrl();

        $intents = [
            'whatsapp' => 'https://wa.me/?text='.rawurlencode($shareText.' - '.$shareUrl),
            'x' => 'https://twitter.com/intent/tweet?text='.rawurlencode($shareText).'&url='.rawurlencode($shareUrl),
            'linkedin' => 'https://www.linkedin.com/sharing/share-offsite/?url='.rawurlencode($shareUrl),
            'facebook' => 'https://www.facebook.com/sharer/sharer.php?u='.rawurlencode($shareUrl),
        ];

        return view('certificates.share', [
            'certificate' => $certificate,
            'holderName' => $this->certificates->publicHolderName($certificate),
            'rank' => $rank,
            'topPercent' => $topPercent,
            'participants' => $participants,
            'shareText' => $shareText,
            'shareUrl' => $shareUrl,
            'intents' => $intents,
            'pageTitle' => "I scored ".round((float) $certificate->score_percent)."% in the {$subject} - LabGOTO",
            'metaDescription' => $rank !== null
                ? "Rank #{$rank} of {$participants} · Top {$topPercent}% · Verified on LabGOTO"
                : "Verified on LabGOTO",
        ]);
    }
}
