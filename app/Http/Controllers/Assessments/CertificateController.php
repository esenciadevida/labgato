<?php

namespace App\Http\Controllers\Assessments;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Storage;

class CertificateController extends Controller
{
    public function __construct(private CertificateService $certificates)
    {
    }

    public function myCertificates(): \Illuminate\View\View
    {
        $certificates = auth()->user()
            ->certificates()
            ->with(['assessment', 'attempt', 'course'])
            ->orderByDesc('issued_at')
            ->get();

        return view('certificates.my-certificates', ['certificates' => $certificates]);
    }

    public function download(ExamAttempt $attempt): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        $this->authorizeOwner($attempt);

        $certificate = $this->certificates->issue($attempt);

        if ($certificate === null) {
            return back()->with('error', 'Certificates unlock when results are revealed.');
        }

        if ($certificate->isRevoked()) {
            abort(410, 'Certificate revoked');
        }

        $path = $this->certificates->ensurePdf($certificate);

        return Storage::disk('public')->download($path, $this->certificates->filename($certificate));
    }

    public function shareRedirect(ExamAttempt $attempt): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeOwner($attempt);

        $certificate = $this->certificates->issue($attempt);

        if ($certificate === null) {
            return back()->with('error', 'Certificates unlock when results are revealed.');
        }

        if ($certificate->isRevoked()) {
            abort(410, 'Certificate revoked');
        }

        return redirect()->to($certificate->shareUrl());
    }

    private function authorizeOwner(ExamAttempt $attempt): void
    {
        abort_unless((int) $attempt->user_id === (int) auth()->id(), 403);
    }
}
