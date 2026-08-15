<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ResourceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreReferenceRangeRequest;
use App\Http\Requests\Admin\UpdateReferenceRangeRequest;
use App\Models\ReferenceRange;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class ReferenceRangeController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->put('intended_listing_url', $request->fullUrl());

        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $categoryId = $request->query('category');

        $referenceRanges = ReferenceRange::query()
            ->with(['categories', 'createdBy'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->whereHas('categories', fn ($q) => $q->whereKey($categoryId)))
            ->orderByDesc('created_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        return view('admin.reference-ranges.index', compact('referenceRanges', 'search', 'status', 'categoryId'));
    }

    public function create()
    {
        $statuses = ResourceStatus::cases();

        return view('admin.reference-ranges.create', compact('statuses'));
    }

    public function store(StoreReferenceRangeRequest $request)
    {
        $referenceRange = ReferenceRange::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'published_at' => $request->input('status') === ResourceStatus::PUBLISHED->value ? now() : null,
        ]);

        $referenceRange->categories()->sync($request->input('categories', []));
        $referenceRange->tags()->sync($request->input('tags', []));

        app(AuditLogService::class)->logWithSubject('reference-ranges.created', $referenceRange, $referenceRange->toArray(), $request);

        if ($request->input('save_action') === 'save_and_create') {
            return redirect()->route('admin.reference-ranges.create')->with('success', 'Reference range created successfully. Add another demographic variation.');
        }

        return redirect()->route('admin.reference-ranges.index')->with('success', 'Reference range created successfully.');
    }

    public function edit(ReferenceRange $referenceRange)
    {
        $statuses = ResourceStatus::cases();

        return view('admin.reference-ranges.edit', compact('referenceRange', 'statuses'));
    }

    public function update(UpdateReferenceRangeRequest $request, ReferenceRange $referenceRange)
    {
        $targetStatus = ResourceStatus::from($request->input('status'));

        if ($referenceRange->status !== $targetStatus && ! $referenceRange->status->canTransitionTo($targetStatus)) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Status cannot change from {$referenceRange->status->label()} to {$targetStatus->label()}.");
        }

        $previousStatus = $referenceRange->status;

        $referenceRange->update([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
            'published_at' => $targetStatus === ResourceStatus::PUBLISHED && ! $referenceRange->published_at ? now() : $referenceRange->published_at,
        ]);

        $referenceRange->categories()->sync($request->input('categories', []));
        $referenceRange->tags()->sync($request->input('tags', []));

        app(AuditLogService::class)->logWithSubject('reference-ranges.updated', $referenceRange, $referenceRange->getChanges(), $request);

        if ($previousStatus !== ResourceStatus::PUBLISHED && $targetStatus === ResourceStatus::PUBLISHED) {
            app(AuditLogService::class)->logWithSubject('reference-ranges.published', $referenceRange, ['status' => $targetStatus->value], $request);
        }

        if ($targetStatus === ResourceStatus::ARCHIVED) {
            app(AuditLogService::class)->logWithSubject('reference-ranges.archived', $referenceRange, ['status' => $targetStatus->value], $request);
        }

        return redirect()->route('admin.reference-ranges.edit', $referenceRange)->with('success', 'Reference range updated successfully.');
    }

    public function publish(Request $request, ReferenceRange $referenceRange)
    {
        if ($referenceRange->status !== ResourceStatus::PUBLISHED && ! $referenceRange->status->canTransitionTo(ResourceStatus::PUBLISHED)) {
            return redirect()->back()->with('error', "Cannot publish from {$referenceRange->status->label()}.");
        }

        $referenceRange->update([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => $referenceRange->published_at ?? now(),
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('reference-ranges.published', $referenceRange, ['status' => ResourceStatus::PUBLISHED->value], $request);

        return redirect()->back()->with('success', 'Reference range published successfully.');
    }

    public function archive(Request $request, ReferenceRange $referenceRange)
    {
        if ($referenceRange->status !== ResourceStatus::ARCHIVED && ! $referenceRange->status->canTransitionTo(ResourceStatus::ARCHIVED)) {
            return redirect()->back()->with('error', "Cannot archive from {$referenceRange->status->label()}.");
        }

        $referenceRange->update([
            'status' => ResourceStatus::ARCHIVED,
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('reference-ranges.archived', $referenceRange, ['status' => ResourceStatus::ARCHIVED->value], $request);

        return redirect()->back()->with('success', 'Reference range archived successfully.');
    }

    public function destroy(Request $request, ReferenceRange $referenceRange)
    {
        $changes = ['name' => $referenceRange->name, 'slug' => $referenceRange->slug];
        $referenceRange->delete();

        app(AuditLogService::class)->logWithSubject('reference-ranges.deleted', $referenceRange, $changes, $request);

        return $this->redirectToListing('admin.reference-ranges.index')->with('success', 'Reference range deleted successfully.');
    }
}
