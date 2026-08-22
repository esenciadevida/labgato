<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ResourceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKnowledgeRequest;
use App\Http\Requests\Admin\UpdateKnowledgeRequest;
use App\Models\KnowledgeResource;
use App\Services\AuditLogService;
use App\Services\ContentImageService;
use Illuminate\Http\Request;

class KnowledgeController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->put('intended_listing_url', $request->fullUrl());

        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $categoryId = $request->query('category');

        $knowledge = KnowledgeResource::query()
            ->with(['categories', 'createdBy'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->whereHas('categories', fn ($q) => $q->whereKey($categoryId)))
            ->orderByDesc('created_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        return view('admin.knowledge.index', compact('knowledge', 'search', 'status', 'categoryId'));
    }

    public function create()
    {
        $statuses = ResourceStatus::cases();

        return view('admin.knowledge.create', compact('statuses'));
    }

    public function store(StoreKnowledgeRequest $request)
    {
        $data = collect($request->validated())->except(['featured_image', 'remove_featured_image'])->all();

        if ($request->hasFile('featured_image')) {
            $data['featured_image'] = app(ContentImageService::class)->store($request->file('featured_image'));
        }

        $knowledge = KnowledgeResource::create([
            ...$data,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'published_at' => $request->input('status') === ResourceStatus::PUBLISHED->value ? now() : null,
        ]);

        $knowledge->categories()->sync($request->input('categories', []));
        $knowledge->tags()->sync($request->input('tags', []));

        app(ContentImageService::class)->syncAttachments((string) $knowledge->content, 'knowledge', $knowledge->id);

        app(AuditLogService::class)->logWithSubject('knowledge.created', $knowledge, $knowledge->toArray(), $request);

        if ($request->input('save_action') === 'save_and_create') {
            return redirect()->route('admin.knowledge.create')->with('success', 'Knowledge resource created successfully. Add another.');
        }

        return redirect()->route('admin.knowledge.index')->with('success', 'Knowledge resource created successfully.');
    }

    public function edit(KnowledgeResource $knowledge)
    {
        $statuses = ResourceStatus::cases();

        return view('admin.knowledge.edit', compact('knowledge', 'statuses'));
    }

    public function update(UpdateKnowledgeRequest $request, KnowledgeResource $knowledge)
    {
        $targetStatus = ResourceStatus::from($request->input('status'));

        if ($knowledge->status !== $targetStatus && ! $knowledge->status->canTransitionTo($targetStatus)) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Status cannot change from {$knowledge->status->label()} to {$targetStatus->label()}.");
        }

        $previousStatus = $knowledge->status;

        $data = collect($request->validated())->except(['featured_image', 'remove_featured_image'])->all();

        if ($request->hasFile('featured_image')) {
            $oldImage = $knowledge->featured_image;
            $data['featured_image'] = app(ContentImageService::class)->store($request->file('featured_image'));
            if ($oldImage && $oldImage !== $data['featured_image']) {
                app(ContentImageService::class)->delete($oldImage);
            }
        } elseif ($request->boolean('remove_featured_image') && $knowledge->featured_image) {
            app(ContentImageService::class)->delete($knowledge->featured_image);
            $data['featured_image'] = null;
        }

        $knowledge->update([
            ...$data,
            'updated_by' => $request->user()->id,
            'published_at' => $targetStatus === ResourceStatus::PUBLISHED && ! $knowledge->published_at ? now() : $knowledge->published_at,
        ]);

        $knowledge->categories()->sync($request->input('categories', []));
        $knowledge->tags()->sync($request->input('tags', []));

        app(ContentImageService::class)->syncAttachments((string) $knowledge->content, 'knowledge', $knowledge->id);

        app(AuditLogService::class)->logWithSubject('knowledge.updated', $knowledge, $knowledge->getChanges(), $request);

        if ($previousStatus !== ResourceStatus::PUBLISHED && $targetStatus === ResourceStatus::PUBLISHED) {
            app(AuditLogService::class)->logWithSubject('knowledge.published', $knowledge, ['status' => $targetStatus->value], $request);
        }

        if ($targetStatus === ResourceStatus::ARCHIVED) {
            app(AuditLogService::class)->logWithSubject('knowledge.archived', $knowledge, ['status' => $targetStatus->value], $request);
        }

        return redirect()->route('admin.knowledge.edit', $knowledge)->with('success', 'Knowledge resource updated successfully.');
    }

    public function publish(Request $request, KnowledgeResource $knowledge)
    {
        if ($knowledge->status !== ResourceStatus::PUBLISHED && ! $knowledge->status->canTransitionTo(ResourceStatus::PUBLISHED)) {
            return redirect()->back()->with('error', "Cannot publish from {$knowledge->status->label()}.");
        }

        $knowledge->update([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => $knowledge->published_at ?? now(),
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('knowledge.published', $knowledge, ['status' => ResourceStatus::PUBLISHED->value], $request);

        return redirect()->back()->with('success', 'Knowledge resource published successfully.');
    }

    public function archive(Request $request, KnowledgeResource $knowledge)
    {
        if ($knowledge->status !== ResourceStatus::ARCHIVED && ! $knowledge->status->canTransitionTo(ResourceStatus::ARCHIVED)) {
            return redirect()->back()->with('error', "Cannot archive from {$knowledge->status->label()}.");
        }

        $knowledge->update([
            'status' => ResourceStatus::ARCHIVED,
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('knowledge.archived', $knowledge, ['status' => ResourceStatus::ARCHIVED->value], $request);

        return redirect()->back()->with('success', 'Knowledge resource archived successfully.');
    }

    public function destroy(Request $request, KnowledgeResource $knowledge)
    {
        $changes = ['name' => $knowledge->name, 'slug' => $knowledge->slug];

        app(ContentImageService::class)->delete($knowledge->featured_image);
        $knowledge->delete();

        app(AuditLogService::class)->logWithSubject('knowledge.deleted', $knowledge, $changes, $request);

        return $this->redirectToListing('admin.knowledge.index')->with('success', 'Knowledge resource deleted successfully.');
    }
}
