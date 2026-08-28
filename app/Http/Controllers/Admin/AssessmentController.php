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
use Illuminate\Support\Str;

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
            ->paginate(config('labgoto.pagination.per_page'));

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
            'status' => ['required', 'in:draft,scheduled,open,closed,archived'],
        ]);

        $assessment->update(['status' => $validated['status']]);

        return back()->with('success', "Assessment status set to {$validated['status']}.");
    }

    public function duplicate(Assessment $assessment)
    {
        $copy = Assessment::create([
            ...$assessment->only([
                'type', 'duration_minutes', 'total_questions', 'pass_percentage',
                'opens_at', 'closes_at', 'max_attempts', 'allow_practice',
                'shuffle_questions', 'shuffle_options', 'fullscreen_required',
                'certificate_enabled', 'cert_body_text', 'cert_disclaimer_text',
                'cert_primary_color', 'cert_secondary_color', 'cert_signatory_name',
                'cert_signatory_title', 'cert_show_logo', 'cert_show_qr',
            ]),
            'title' => $assessment->title.' (Copy)',
            'slug' => $this->uniqueSlug($assessment->slug.'-copy'),
            'status' => 'draft',
        ]);

        foreach ($assessment->blueprints as $blueprint) {
            AssessmentBlueprint::create([
                ...$blueprint->only(['question_category_id', 'question_count', 'easy_percent', 'intermediate_percent', 'advanced_percent']),
                'assessment_id' => $copy->id,
            ]);
        }

        return redirect()
            ->route('admin.assessments.edit', $copy)
            ->with('success', "Duplicated as \"{$copy->title}\". It is a draft - adjust and open it when ready.");
    }

    public function destroy(Assessment $assessment)
    {
        if ($assessment->attempts()->exists()) {
            return back()->with('error', 'This assessment already has attempts. Archive it instead of deleting.');
        }

        if ($assessment->status === 'open') {
            return back()->with('error', 'Close this assessment before deleting it.');
        }

        $title = $assessment->title;
        $assessment->blueprints()->delete();
        $assessment->delete();

        return redirect()
            ->route('admin.assessments.index')
            ->with('success', "Assessment \"{$title}\" deleted.");
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base);
        $candidate = $slug;
        $suffix = 2;

        while (Assessment::where('slug', $candidate)->exists()) {
            $candidate = $slug.'-'.$suffix++;
        }

        return $candidate;
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
