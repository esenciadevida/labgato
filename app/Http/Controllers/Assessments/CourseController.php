<?php

namespace App\Http\Controllers\Assessments;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Support\Facades\Storage;

class CourseController extends Controller
{
    public function __construct(private CertificateService $certificates)
    {
    }

    public function index()
    {
        $courses = Course::query()
            ->published()
            ->withCount('assessments as assessments_count')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get();

        return view('courses.index', ['courses' => $courses]);
    }

    public function show(Course $course)
    {
        abort_unless($course->isPublished(), 404);

        $course->load(['assessments' => fn ($q) => $q->orderBy('title')]);

        $attempts = \App\Models\ExamAttempt::query()
            ->where('user_id', auth()->id())
            ->where('mode', 'official')
            ->whereIn('assessment_id', $course->assessments->modelKeys())
            ->orderBy('id')
            ->get()
            ->groupBy('assessment_id');

        $state = [];

        foreach ($course->assessments as $assessment) {
            $latest = $attempts->get($assessment->id)?->last();

            $passed = false;

            if ($latest !== null && in_array($latest->status, ['submitted', 'auto_submitted'], true)) {
                $passed = $assessment->pass_percentage === null
                    || $latest->percentage !== null && (float) $latest->percentage >= (float) $assessment->pass_percentage;
            }

            $state[$assessment->id] = [
                'attempt' => $latest,
                'status' => $latest?->status,
                'passed' => $passed,
            ];
        }

        $complete = $this->certificates->courseComplete($course, auth()->user());

        $certificate = null;

        if ($complete && $course->certificate_enabled) {
            $certificate = $this->certificates->issueForCourse($course, auth()->user());
        }

        return view('courses.show', [
            'course' => $course,
            'state' => $state,
            'complete' => $complete,
            'certificate' => $certificate,
        ]);
    }

    public function download(Course $course): \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\RedirectResponse
    {
        abort_unless($course->isPublished(), 404);

        $user = auth()->user();

        $certificate = $this->certificates->issueForCourse($course, $user);

        if ($certificate === null) {
            return back()->with('error', 'Complete every required assessment to earn your course certificate.');
        }

        if ($certificate->isRevoked()) {
            abort(410, 'Certificate revoked');
        }

        $path = $this->certificates->ensurePdf($certificate);

        return Storage::disk('public')->download($path, $this->certificates->filename($certificate));
    }
}