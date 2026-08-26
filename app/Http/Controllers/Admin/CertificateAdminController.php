<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Certificate;
use App\Services\CertificateService;
use App\Support\CertificateTemplateVariables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CertificateAdminController extends Controller
{
    public function __construct(private CertificateService $certificates)
    {
    }

    public function index(Request $request): View
    {
        $query = Certificate::query()
            ->with(['user.profile', 'assessment'])
            ->orderByDesc('issued_at');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim();
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('assessment', fn ($a) => $a->where('title', 'like', "%{$search}%"));
            });
        }

        if ($request->filled('assessment')) {
            $query->where('assessment_id', $request->integer('assessment'));
        }

        if (in_array($request->input('tier'), Certificate::TYPES, true)) {
            $query->where('type', $request->string('tier'));
        }

        if ($request->input('status') === 'revoked') {
            $query->whereNotNull('revoked_at');
        } elseif ($request->input('status') === 'valid') {
            $query->whereNull('revoked_at');
        }

        $stats = [
            'total' => Certificate::count(),
            'participation' => Certificate::where('type', 'participation')->count(),
            'achievement' => Certificate::where('type', 'achievement')->count(),
            'distinction' => Certificate::where('type', 'distinction')->count(),
            'revoked' => Certificate::whereNotNull('revoked_at')->count(),
        ];

        return view('admin.certificates.index', [
            'certificates' => $query->paginate(config('labgoto.pagination.per_page'))->withQueryString(),
            'assessments' => Assessment::orderBy('title')->get(['id', 'title']),
            'tiers' => Certificate::TYPES,
            'filters' => [
                'search' => $request->input('search'),
                'assessment' => $request->input('assessment'),
                'tier' => $request->input('tier'),
                'status' => $request->input('status'),
            ],
            'stats' => $stats,
        ]);
    }

    public function show(Certificate $certificate): View
    {
        $certificate->load(['user.profile', 'assessment']);

        return view('admin.certificates.show', [
            'certificate' => $certificate,
            'holderName' => $certificate->user?->profile?->display_name
                ?? $certificate->user?->name
                ?? 'Participant',
        ]);
    }

    public function template(): View
    {
        $signaturePath = (string) setting('certificate.signature_path', '');
        $hasSignature = $signaturePath !== '' && Storage::disk('public')->exists($signaturePath);

        return view('admin.certificates.template', [
            'hasSignature' => $hasSignature,
            'signatureUrl' => $hasSignature ? Storage::disk('public')->url($signaturePath) : null,
            'variables' => CertificateTemplateVariables::$available,
            'sampleVariables' => CertificateTemplateVariables::$sample,
        ]);
    }

    public function preview(Request $request)
    {
        $validated = $request->validate([
            'primary_color' => ['nullable', 'string', 'max:7'],
            'secondary_color' => ['nullable', 'string', 'max:7'],
            'body_text' => ['nullable', 'string', 'max:2000'],
            'disclaimer_text' => ['nullable', 'string', 'max:2000'],
            'show_logo' => ['nullable', 'boolean'],
            'show_qr' => ['nullable', 'boolean'],
        ]);

        $sample = CertificateTemplateVariables::$sample;

        $bodyText = $validated['body_text'] ?? (string) setting('certificate.body_text', '');
        $disclaimerText = $validated['disclaimer_text'] ?? (string) setting('certificate.disclaimer_text', '');

        if ($bodyText !== '') {
            $bodyHtml = nl2br(e(CertificateTemplateVariables::replace($bodyText, $sample)));
        } else {
            $bodyHtml = 'for successfully completing the <strong>' . e($sample['{assessment}']) . '</strong>';
        }

        if ($disclaimerText !== '') {
            $disclaimerHtml = nl2br(e(CertificateTemplateVariables::replace($disclaimerText, $sample)));
        } else {
            $disclaimerHtml = 'This certificate acknowledges achievement in the ' . e($sample['{assessment}']) . ' administered by LabGOTO. '
                . 'It is not an ASCP certification nor an official ASCP credential.';
        }

        return response()->json([
            'primaryColor' => $validated['primary_color'] ?? (string) setting('certificate.primary_color', '#1e3a8a'),
            'secondaryColor' => $validated['secondary_color'] ?? (string) setting('certificate.secondary_color', '#d4af37'),
            'bodyHtml' => $bodyHtml,
            'disclaimerHtml' => $disclaimerHtml,
            'showLogo' => ($validated['show_logo'] ?? setting_bool('certificate.show_logo', true)) === 'true' || ($validated['show_logo'] ?? null) === true,
            'showQr' => ($validated['show_qr'] ?? setting_bool('certificate.show_qr', true)) === 'true' || ($validated['show_qr'] ?? null) === true,
            'sample' => $sample,
            'signatoryName' => (string) setting('certificate.signatory_name', 'Program Director'),
            'signatoryTitle' => (string) setting('certificate.signatory_title', 'Program Director, LabGOTO'),
        ]);
    }

    public function regenerate(Request $request, Certificate $certificate)
    {
        $this->certificates->regenerate($certificate);

        return back()->with('success', "Certificate {$certificate->certificate_number} PDF will be regenerated on next access.");
    }

    public function download(Certificate $certificate)
    {
        if ($certificate->isRevoked()) {
            return back()->withErrors(['error' => 'Cannot download a revoked certificate.']);
        }

        $pdfPath = $this->certificates->ensurePdf($certificate);

        return Storage::disk('public')->download(
            $pdfPath,
            $this->certificates->filename($certificate)
        );
    }

    public function revoke(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->certificates->revoke($certificate, $validated['reason'] ?? null);

        return back()->with('success', "Certificate {$certificate->certificate_number} revoked.");
    }

    public function bulkRevoke(Request $request)
    {
        $validated = $request->validate([
            'certificate_ids' => ['required', 'array'],
            'certificate_ids.*' => ['integer', 'exists:certificates,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $count = 0;
        foreach ($validated['certificate_ids'] as $id) {
            $certificate = Certificate::find($id);
            if ($certificate && ! $certificate->isRevoked()) {
                $this->certificates->revoke($certificate, $validated['reason'] ?? null);
                $count++;
            }
        }

        return back()->with('success', "{$count} certificate(s) revoked.");
    }
}
