<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ResourceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreInterpretationRequest;
use App\Http\Requests\Admin\UpdateInterpretationRequest;
use App\Models\InterpretationResource;
use App\Services\AuditLogService;
use App\Services\ContentImageService;
use Illuminate\Http\Request;

class InterpretationController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->put('intended_listing_url', $request->fullUrl());

        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $categoryId = $request->query('category');

        $interpretations = InterpretationResource::query()
            ->with(['categories', 'createdBy'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->whereHas('categories', fn ($q) => $q->whereKey($categoryId)))
            ->orderByDesc('created_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        return view('admin.interpretation.index', compact('interpretations', 'search', 'status', 'categoryId'));
    }

    public function create()
    {
        $statuses = ResourceStatus::cases();

        return view('admin.interpretation.create', compact('statuses'));
    }

    public function store(StoreInterpretationRequest $request)
    {
        $data = collect($request->validated())->except(['featured_image', 'remove_featured_image'])->all();

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = app(ContentImageService::class)->store($request->file('featured_image'));
        }

        $interpretation = InterpretationResource::create([
            ...$data,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'published_at' => $request->input('status') === ResourceStatus::PUBLISHED->value ? now() : null,
        ]);

        $interpretation->categories()->sync($request->input('categories', []));
        $interpretation->tags()->sync($request->input('tags', []));

        app(AuditLogService::class)->logWithSubject('interpretation.created', $interpretation, $interpretation->toArray(), $request);

        if ($request->input('save_action') === 'save_and_create') {
            return redirect()->route('admin.interpretation.create')->with('success', 'Interpretation resource created successfully. Add another.');
        }

        return redirect()->route('admin.interpretation.index')->with('success', 'Interpretation resource created successfully.');
    }

    public function edit(InterpretationResource $interpretation)
    {
        $statuses = ResourceStatus::cases();

        return view('admin.interpretation.edit', compact('interpretation', 'statuses'));
    }

    public function update(UpdateInterpretationRequest $request, InterpretationResource $interpretation)
    {
        $targetStatus = ResourceStatus::from($request->input('status'));

        if ($interpretation->status !== $targetStatus && ! $interpretation->status->canTransitionTo($targetStatus)) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Status cannot change from {$interpretation->status->label()} to {$targetStatus->label()}.");
        }

        $previousStatus = $interpretation->status;

        $data = collect($request->validated())->except(['featured_image', 'remove_featured_image'])->all();

        if ($request->hasFile('featured_image')) {
            $oldImage = $interpretation->featured_image;
            $data['featured_image'] = app(ContentImageService::class)->store($request->file('featured_image'));
            if ($oldImage && $oldImage !== $data['featured_image']) {
                app(ContentImageService::class)->delete($oldImage);
            }
        } elseif ($request->boolean('remove_featured_image') && $interpretation->featured_image) {
            app(ContentImageService::class)->delete($interpretation->featured_image);
            $data['featured_image'] = null;
        }

        $interpretation->update([
            ...$data,
            'updated_by' => $request->user()->id,
            'published_at' => $targetStatus === ResourceStatus::PUBLISHED && ! $interpretation->published_at ? now() : $interpretation->published_at,
        ]);

        $interpretation->categories()->sync($request->input('categories', []));
        $interpretation->tags()->sync($request->input('tags', []));

        app(AuditLogService::class)->logWithSubject('interpretation.updated', $interpretation, $interpretation->getChanges(), $request);

        if ($previousStatus !== ResourceStatus::PUBLISHED && $targetStatus === ResourceStatus::PUBLISHED) {
            app(AuditLogService::class)->logWithSubject('interpretation.published', $interpretation, ['status' => $targetStatus->value], $request);
        }

        if ($targetStatus === ResourceStatus::ARCHIVED) {
            app(AuditLogService::class)->logWithSubject('interpretation.archived', $interpretation, ['status' => $targetStatus->value], $request);
        }

        return redirect()->route('admin.interpretation.edit', $interpretation)->with('success', 'Interpretation resource updated successfully.');
    }

    public function publish(Request $request, InterpretationResource $interpretation)
    {
        if ($interpretation->status !== ResourceStatus::PUBLISHED && ! $interpretation->status->canTransitionTo(ResourceStatus::PUBLISHED)) {
            return redirect()->back()->with('error', "Cannot publish from {$interpretation->status->label()}.");
        }

        $interpretation->update([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => $interpretation->published_at ?? now(),
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('interpretation.published', $interpretation, ['status' => ResourceStatus::PUBLISHED->value], $request);

        return redirect()->back()->with('success', 'Interpretation resource published successfully.');
    }

    public function archive(Request $request, InterpretationResource $interpretation)
    {
        if ($interpretation->status !== ResourceStatus::ARCHIVED && ! $interpretation->status->canTransitionTo(ResourceStatus::ARCHIVED)) {
            return redirect()->back()->with('error', "Cannot archive from {$interpretation->status->label()}.");
        }

        $interpretation->update([
            'status' => ResourceStatus::ARCHIVED,
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('interpretation.archived', $interpretation, ['status' => ResourceStatus::ARCHIVED->value], $request);

        return redirect()->back()->with('success', 'Interpretation resource archived successfully.');
    }

    public function destroy(Request $request, InterpretationResource $interpretation)
    {
        $changes = ['name' => $interpretation->name, 'slug' => $interpretation->slug];

        app(ContentImageService::class)->delete($interpretation->featured_image);
        $interpretation->delete();

        app(AuditLogService::class)->logWithSubject('interpretation.deleted', $interpretation, $changes, $request);

        return $this->redirectToListing('admin.interpretation.index')->with('success', 'Interpretation resource deleted successfully.');
    }
}
