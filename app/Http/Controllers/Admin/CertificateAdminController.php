<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\Certificate;
use App\Services\CertificateService;
use Illuminate\Http\Request;
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
            'certificates' => $query->paginate(25)->withQueryString(),
            'assessments' => Assessment::orderBy('title')->get(['id', 'title']),
            'tiers' => Certificate::TYPES,
            'filters' => [
                'assessment' => $request->input('assessment'),
                'tier' => $request->input('tier'),
                'status' => $request->input('status'),
            ],
            'stats' => $stats,
        ]);
    }

    public function revoke(Request $request, Certificate $certificate)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $this->certificates->revoke($certificate, $validated['reason'] ?? null);

        return back()->with('success', "Certificate {$certificate->certificate_number} revoked.");
    }
}
