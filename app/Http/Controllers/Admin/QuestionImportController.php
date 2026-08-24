<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ImportQuestionsRequest;
use App\Models\Question;
use App\Services\AuditLogService;
use App\Services\QuestionImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class QuestionImportController extends Controller
{
    public function __construct(protected QuestionImportService $importService)
    {
    }

    public function showImportForm()
    {
        return view('admin.questions.import');
    }

    public function template()
    {
        return response($this->importService->template())
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="questions_import_template.csv"');
    }

    public function preview(ImportQuestionsRequest $request)
    {
        $result = $this->importService->parse($request->file('file'));

        if ($result['header_error'] !== null) {
            return redirect()
                ->route('admin.questions.import')
                ->with('error', $result['header_error']);
        }

        Session::put('questions_import.valid_rows', $result['valid']);
        Session::put('questions_import.status', $request->input('import_as', 'draft'));

        return view('admin.questions.import-preview', [
            'rows' => $result['rows'],
            'validCount' => count($result['valid']),
            'invalidCount' => count($result['invalid']),
        ]);
    }

    public function import(Request $request)
    {
        $validRows = Session::get('questions_import.valid_rows');

        if (! is_array($validRows) || $validRows === []) {
            return redirect()
                ->route('admin.questions.import')
                ->with('error', 'Your import session has expired or contains no valid rows. Please upload the CSV file again.');
        }

        $status = Session::get('questions_import.status', 'draft');

        $result = $this->importService->import($validRows, $status);

        app(AuditLogService::class)->log(
            'import questions',
            Question::class,
            null,
            ['count' => $result['imported'], 'status' => $status],
            auth()->id()
        );

        Session::forget('questions_import');

        return redirect()
            ->route('admin.questions.index')
            ->with('success', "Imported {$result['imported']} question(s) as {$status}.");
    }
}
