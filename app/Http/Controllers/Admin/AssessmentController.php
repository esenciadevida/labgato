<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssessmentRequest;
use App\Http\Requests\Admin\UpdateAssessmentRequest;
use App\Models\Assessment;
use App\Models\AssessmentBlueprint;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssessmentController extends Controller
{
    public function index()
    {
        $assessments = Assessment::query()
            ->select('assessments.*')
            ->withCount([
                'attempts as participants_count' => fn ($q) => $q->where('mode', 'official'),
                'attempts as completed_count' => fn ($q) => $q
                    ->where('mode', 'official')
                    ->whereIn('status', ['submitted', 'auto_submitted']),
            ])
            ->addSelect(DB::raw(
                '(select avg(percentage) from exam_attempts '
                ."where exam_attempts.assessment_id = assessments.id and exam_attempts.mode = 'official' "
                ."and exam_attempts.status in ('submitted', 'auto_submitted') and exam_attempts.percentage is not null) as avg_percentage"
            ))
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.assessments.index', [
            'assessments' => $assessments,
        ]);
    }

    public function create()
    {
        return view('admin.assessments.create', [
            'assessment' => new Assessment(['status' => 'scheduled', 'duration_minutes' => 60, 'total_questions' => 10]),
            'categories' => $this->activeCategories(),
        ]);
    }

    public function store(StoreAssessmentRequest $request)
    {
        $assessment = Assessment::create($request->assessmentAttributes());
        $this->syncBlueprints($assessment, $request->includedBlueprints());

        return redirect()
            ->route('admin.assessments.index')
            ->with('success', "Assessment \"{$assessment->title}\" created.");
    }

    public function edit(Assessment $assessment)
    {
        $assessment->load('blueprints');

        return view('admin.assessments.edit', [
            'assessment' => $assessment,
            'categories' => $this->activeCategories(),
        ]);
    }

    public function update(UpdateAssessmentRequest $request, Assessment $assessment)
    {
        $assessment->update($request->assessmentAttributes());
        $this->syncBlueprints($assessment, $request->includedBlueprints());

        return redirect()
            ->route('admin.assessments.index')
            ->with('success', "Assessment \"{$assessment->title}\" updated.");
    }

    public function status(Request $request, Assessment $assessment)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:scheduled,open,closed,archived'],
        ]);

        $assessment->update(['status' => $validated['status']]);

        return back()->with('success', "Assessment status set to {$validated['status']}.");
    }

    private function syncBlueprints(Assessment $assessment, array $rows): void
    {
        $assessment->blueprints()->delete();

        foreach ($rows as $row) {
            AssessmentBlueprint::create($row + ['assessment_id' => $assessment->id]);
        }
    }

    /** @return \Illuminate\Support\Collection<int, QuestionCategory> */
    private function activeCategories()
    {
        return QuestionCategory::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
    }
}
