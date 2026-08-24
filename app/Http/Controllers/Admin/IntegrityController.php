<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamAttempt;
use Illuminate\Http\Request;

class IntegrityController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->string('status')->toString();
        $search = $request->string('search')->toString();

        $attempts = ExamAttempt::query()
            ->with(['user', 'assessment'])
            ->withCount('integrityEvents')
            ->when(in_array($status, ['normal', 'review', 'high_risk'], true), fn ($q) => $q->where('integrity_status', $status))
            ->when($search !== '', function ($q) use ($search) {
                $q->whereHas('user', fn ($uq) => $uq->where(fn ($w) => $w
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")));
            })
            ->orderByRaw("CASE integrity_status WHEN 'high_risk' THEN 0 WHEN 'review' THEN 1 ELSE 2 END")
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.integrity.index', [
            'attempts' => $attempts,
            'status' => $status,
            'search' => $search,
        ]);
    }

    public function show(ExamAttempt $attempt)
    {
        $attempt->load(['user', 'assessment']);

        $events = $attempt->integrityEvents()->latest('id')->get();

        return view('admin.integrity.show', [
            'attempt' => $attempt,
            'events' => $events,
        ]);
    }

    public function updateStatus(Request $request, ExamAttempt $attempt)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:normal,review,high_risk'],
        ]);

        $attempt->forceFill([
            'integrity_status' => $validated['status'],
        ])->save();

        return redirect()
            ->route('admin.integrity.show', $attempt)
            ->with('success', 'Integrity status updated.');
    }
}
