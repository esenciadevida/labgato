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

        return view('certificates.verify', [
            'status' => $result['status'],
            'certificate' => $result['certificate'],
        ]);
    }

    public function share(string $code): View
    {
        $certificate = Certificate::query()
            ->where('verification_code', strtoupper(trim($code)))
            ->with(['user.profile', 'assessment'])
            ->first();

        if ($certificate === null || $certificate->isRevoked()) {
            abort(404);
        }

        $topPercent = app(\App\Services\ResultsService::class)->topPercent($certificate->attempt);

        $shareText = sprintf(
            'I scored %.1f%% in the %s on LabGOTO',
            (float) $certificate->score_percent,
            $certificate->assessment->title
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
            'holderName' => $this->publicHolderName($certificate),
            'topPercent' => $topPercent,
            'shareText' => $shareText,
            'shareUrl' => $shareUrl,
            'intents' => $intents,
            'pageTitle' => "I scored ".round((float) $certificate->score_percent)."% in the {$certificate->assessment->title} - LabGOTO",
            'metaDescription' => "Rank #{$certificate->rank_at_issue} · Top {$topPercent}% · Verified on LabGOTO",
        ]);
    }

    private function publicHolderName(Certificate $certificate): string
    {
        $profile = $certificate->user?->profile;

        // Privacy opt-out hides the real identity on public share pages.
        if ($profile !== null && $profile->show_on_leaderboard === false) {
            return 'A LabGOTO participant';
        }

        if ($profile !== null && filled($profile->display_name)) {
            return $profile->display_name;
        }

        return $certificate->user?->name ?? 'Participant';
    }
}
