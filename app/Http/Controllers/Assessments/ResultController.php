<?php

namespace App\Http\Controllers\Assessments;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use App\Services\ResultsService;
use Illuminate\Http\Request;

class ResultController extends Controller
{
    public function __construct(protected ResultsService $results)
    {
    }

    public function myResults(Request $request)
    {
        $attempts = ExamAttempt::query()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', ['submitted', 'auto_submitted'])
            ->with('assessment:id,title,slug,results_status')
            ->orderByDesc('id')
            ->get()
            ->map(function (ExamAttempt $attempt) {
                return (object) [
                    'attempt' => $attempt,
                    'released' => $this->results->isReleased($attempt->assessment),
                ];
            });

        return view('assessments.my-results', [
            'rows' => $attempts,
        ]);
    }

    public function show(Request $request, ExamAttempt $attempt)
    {
        abort_unless((int) $attempt->user_id === (int) $request->user()?->id, 403);

        if ($attempt->status === 'in_progress') {
            return redirect()->route('exams.show', $attempt);
        }

        abort_unless(in_array($attempt->status, ['submitted', 'auto_submitted'], true), 404);

        $attempt->load(['assessment']);

        $released = $this->results->isReleased($attempt->assessment);

        return view('assessments.result', [
            'attempt' => $attempt,
            'released' => $released,
            'summary' => $released ? $this->results->summary($attempt) : null,
            'breakdown' => $released ? $this->results->subjectBreakdown($attempt) : null,
            'reviewItems' => $released ? collect($this->results->review($attempt)) : null,
        ]);
    }
}
