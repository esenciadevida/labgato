<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sop;
use App\Models\SopVersion;
use App\Services\AuditLogService;
use App\Services\SopVersionService;
use Illuminate\Http\Request;

class SopVersionController extends Controller
{
    public function index(Sop $sop)
    {
        $versions = app(SopVersionService::class)->getHistory($sop);

        return view('admin.sops.versions.index', compact('sop', 'versions'));
    }

    public function show(Sop $sop, SopVersion $version)
    {
        abort_unless($version->sop_id === $sop->id, 404);

        return view('admin.sops.versions.show', compact('sop', 'version'));
    }

    public function restore(Request $request, Sop $sop, SopVersion $version)
    {
        abort_unless($version->sop_id === $sop->id, 404);

        app(SopVersionService::class)->restore($sop, $version);

        app(AuditLogService::class)->logWithSubject('sops.version_restored', $sop, [
            'restored_from_version' => $version->version_number,
        ], $request);

        return redirect()
            ->route('admin.sops.versions.index', $sop)
            ->with('success', "Version {$version->version_number} restored as {$sop->version}.");
    }
}
