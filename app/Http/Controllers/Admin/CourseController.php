<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCourseRequest;
use App\Http\Requests\Admin\UpdateCourseRequest;
use App\Models\Assessment;
use App\Models\Course;
use App\Services\CertificateService;
use Illuminate\Support\Str;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::query()
            ->withCount('assessments as assessments_count')
            ->orderBy('sort_order')
            ->orderBy('title')
            ->paginate(config('labgoto.pagination.per_page'));

        return view('admin.courses.index', ['courses' => $courses]);
    }

    public function create()
    {
        return view('admin.courses.create', [
            'course' => new Course(['status' => 'draft', 'certificate_enabled' => true, 'certificate_requires_all' => true]),
            'assessments' => $this->selectableAssessments(),
        ]);
    }

    public function store(StoreCourseRequest $request)
    {
        $course = Course::create($request->courseAttributes());
        $course->assessments()->sync($this->pivotRows($request->courseAssessments()));

        return redirect()
            ->route('admin.courses.index')
            ->with('success', "Course \"{$course->title}\" created.");
    }

    public function edit(Course $course)
    {
        $course->load('assessments');

        return view('admin.courses.edit', [
            'course' => $course,
            'assessments' => $this->selectableAssessments(),
        ]);
    }

    public function update(UpdateCourseRequest $request, Course $course)
    {
        $course->update($request->courseAttributes());
        $course->assessments()->sync($this->pivotRows($request->courseAssessments()));

        return redirect()
            ->route('admin.courses.index')
            ->with('success', "Course \"{$course->title}\" updated.");
    }

    public function certificatePreview(Course $course)
    {
        return view('admin.courses.certificate-preview', [
            'course' => $course,
            'preview' => app(CertificateService::class)->coursePreviewData($course),
        ]);
    }

    public function destroy(Course $course)
    {
        if ($course->certificates()->exists()) {
            return back()->with('error', 'This course already issued certificates. Archive it instead of deleting.');
        }

        $title = $course->title;
        $course->assessments()->detach();
        $course->delete();

        return redirect()
            ->route('admin.courses.index')
            ->with('success', "Course \"{$title}\" deleted.");
    }

    /**
     * Assessment options for the dashboard are non-draft, non-archived.
     */
    private function selectableAssessments()
    {
        return Assessment::query()
            ->whereNotIn('status', ['draft', 'archived'])
            ->orderBy('title')
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    private function pivotRows(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            $normalized[$row['assessment_id']] = [
                'required' => (bool) $row['required'],
                'sort_order' => (int) $row['sort_order'],
            ];
        }

        return $normalized;
    }
}