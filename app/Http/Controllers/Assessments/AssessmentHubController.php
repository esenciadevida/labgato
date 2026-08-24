<?php

namespace App\Http\Controllers\Assessments;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\ExamAttempt;
use App\Models\LaunchSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AssessmentHubController extends Controller
{
    public function index(Request $request)
    {
        $assessments = Assessment::query()
            ->where('status', '!=', 'draft')
            ->with('blueprints.category')
            ->get()
            ->sort($this->ordering())
            ->values();

        $states = [];

        if ($user = $request->user()) {
            $attempts = ExamAttempt::query()
                ->where('user_id', $user->id)
                ->where('mode', 'official')
                ->whereIn('assessment_id', $assessments->modelKeys())
                ->orderBy('id')
                ->get()
                ->groupBy('assessment_id');

            foreach ($assessments as $assessment) {
                $latest = $attempts->get($assessment->id)?->last();

                $states[$assessment->id] = [
                    'attempt' => $latest,
                    'status' => $latest?->status,
                    'can_practice' => LaunchSetting::current()->challenge_open
                        && $assessment->isOpen()
                        && $assessment->allow_practice
                        && in_array($latest?->status, ['submitted', 'auto_submitted'], true),
                ];
            }
        }

        return view('assessments.index', [
            'assessments' => $assessments,
            'states' => $states,
        ]);
    }

    /**
     * Open first (soonest closing first), then scheduled (soonest starting),
     * then closed/archived (most recently closed first).
     */
    private function ordering(): \Closure
    {
        $rank = ['open' => 0, 'scheduled' => 1];

        return function (Assessment $a, Assessment $b) use ($rank) {
            $groupA = $rank[$a->status] ?? 2;
            $groupB = $rank[$b->status] ?? 2;

            if ($groupA !== $groupB) {
                return $groupA <=> $groupB;
            }

            return match ($groupA) {
                0 => ($a->closes_at?->timestamp ?? PHP_INT_MAX) <=> ($b->closes_at?->timestamp ?? PHP_INT_MAX),
                1 => ($a->opens_at?->timestamp ?? PHP_INT_MAX) <=> ($b->opens_at?->timestamp ?? PHP_INT_MAX),
                default => ($b->closes_at?->timestamp ?? 0) <=> ($a->closes_at?->timestamp ?? 0),
            };
        };
    }
}
