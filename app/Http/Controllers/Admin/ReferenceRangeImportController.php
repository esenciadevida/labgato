<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportReferenceRangesRequest;
use App\Models\ReferenceRange;
use App\Services\AuditLogService;
use App\Services\ReferenceRangeImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class ReferenceRangeImportController extends Controller
{
    protected $importService;

    public function __construct(ReferenceRangeImportService $importService)
    {
        $this->importService = $importService;
    }

    public function showImportForm()
    {
        return view('admin.reference-ranges.import');
    }

    public function template()
    {
        $headers = array_merge(
            ReferenceRangeImportService::REQUIRED_COLUMNS,
            ReferenceRangeImportService::OPTIONAL_COLUMNS,
        );

        $exampleRow = [
            'Hemoglobin - Adult Male', 'Hemoglobin', 'Whole Blood', '',
            'Adult', '', '', 'male', 'g/dL', 'g/L', '13.8', '17.2', '7.0', '20.0', '10', 'Tietz Textbook of Clinical Chemistry',
        ];

        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, $headers);
        fputcsv($stream, $exampleRow);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);

        return response($content)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="reference_ranges_template.csv"');
    }

    public function preview(ImportReferenceRangesRequest $request)
    {
        $file = $request->file('file');
        $result = $this->importService->parseAndValidate($file);

        if (! empty($result['header_error'])) {
            return redirect()
                ->route('admin.reference-ranges.import')
                ->with('error', $result['header_error']);
        }

        Session::put('import.valid_rows', $result['valid']);
        Session::put('import.errors', $result['errors']);
        Session::put('import.total', $result['total']);
        Session::put('import.status', $request->import_as ?? 'draft');
        Session::put('import.duplicate_handling', $request->duplicate_handling ?? 'skip');

        return view('admin.reference-ranges.import-preview', $result);
    }

    public function import(Request $request)
    {
        $validRows = Session::get('import.valid_rows');

        if (! is_array($validRows)) {
            return redirect()
                ->route('admin.reference-ranges.import')
                ->with('error', 'Your import session has expired. Please upload the CSV file again.');
        }

        $status = Session::get('import.status', 'draft');
        $duplicateHandling = Session::get('import.duplicate_handling', 'skip');

        $result = $this->importService->import($validRows, $status, $duplicateHandling);

        app(AuditLogService::class)->log(
            'import reference ranges',
            ReferenceRange::class,
            null,
            $result,
            auth()->id(),
        );

        Session::forget('import');

        return redirect()
            ->route('admin.reference-ranges.index')
            ->with('success', "Imported {$result['imported']} reference ranges as {$status}. {$result['skipped']} skipped, {$result['updated']} updated.");
    }
}