<?php

namespace App\Services;

use App\Models\Sop;
use App\Models\SopVersion;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

class SopVersionService
{
    public function createSnapshot(Sop $sop, ?string $changeSummary = null): SopVersion
    {
        return SopVersion::create([
            'sop_id' => $sop->id,
            'version_number' => $sop->version ?: '1.0',
            'document_code' => $sop->getAttribute('document_code'),
            'purpose' => $sop->purpose,
            'scope' => $sop->scope,
            'procedure_content' => $sop->procedure_content,
            'quality_control' => $sop->quality_control,
            'safety_information' => $sop->safety_information,
            'references' => $sop->references,
            'content' => $sop->content,
            'change_summary' => $changeSummary,
            'created_by' => Auth::id(),
        ]);
    }

    public function getHistory(Sop $sop): Collection
    {
        return SopVersion::query()
            ->where('sop_id', $sop->id)
            ->with('creator:id,name,email')
            ->orderByDesc('created_at')
            ->get();
    }

    public function suggestNextVersion(?string $currentVersion): string
    {
        $trimmed = trim((string) $currentVersion);

        if ($trimmed === '') {
            return '1.1';
        }

        $prefix = preg_match('/^[vV]/', $trimmed) ? $trimmed[0] : '';
        $numeric = preg_replace('/^[vV]/', '', $trimmed);

        if (preg_match('/^(\d+)(?:\.(\d+))?$/', $numeric, $matches)) {
            $major = (int) $matches[1];
            $minor = isset($matches[2]) ? (int) $matches[2] : 0;

            return $prefix.$major.'.'.($minor + 1);
        }

        return $prefix.$numeric.'.1';
    }

    public function restore(Sop $sop, SopVersion $version): Sop
    {
        $sop->update([
            'purpose' => $version->purpose,
            'scope' => $version->scope,
            'procedure_content' => $version->procedure_content,
            'quality_control' => $version->quality_control,
            'safety_information' => $version->safety_information,
            'references' => $version->references,
            'content' => $version->content,
            'version' => $this->suggestNextVersion($sop->version),
            'updated_by' => Auth::id(),
        ]);

        $this->createSnapshot($sop, "Restored from version {$version->version_number}");

        return $sop->fresh();
    }
}
