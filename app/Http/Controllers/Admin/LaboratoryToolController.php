<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ResourceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreLaboratoryToolRequest;
use App\Http\Requests\Admin\UpdateLaboratoryToolRequest;
use App\Models\LaboratoryTool;
use App\Services\AuditLogService;
use App\Services\Tools\ToolService;
use Illuminate\Http\Request;

class LaboratoryToolController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->put('intended_listing_url', $request->fullUrl());

        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $categoryId = $request->query('category');

        $tools = LaboratoryTool::query()
            ->with(['categories', 'createdBy'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->whereHas('categories', fn ($q) => $q->whereKey($categoryId)))
            ->orderByDesc('created_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        return view('admin.tools.index', compact('tools', 'search', 'status', 'categoryId'));
    }

    public function create(ToolService $toolService)
    {
        $engines = $toolService->availableEngines();
        $statuses = ResourceStatus::cases();

        return view('admin.tools.create', compact('engines', 'statuses'));
    }

    public function store(StoreLaboratoryToolRequest $request)
    {
        $tool = LaboratoryTool::create([
            ...$request->validated(),
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'published_at' => $request->input('status') === ResourceStatus::PUBLISHED->value ? now() : null,
        ]);

        $tool->categories()->sync($request->input('categories', []));
        $tool->tags()->sync($request->input('tags', []));

        app(AuditLogService::class)->logWithSubject('tools.created', $tool, $tool->toArray(), $request);

        return redirect()->route('admin.tools.index')->with('success', 'Tool created successfully.');
    }

    public function edit(LaboratoryTool $tool, ToolService $toolService)
    {
        $engines = $toolService->availableEngines();
        $statuses = ResourceStatus::cases();

        return view('admin.tools.edit', compact('tool', 'engines', 'statuses'));
    }

    public function update(UpdateLaboratoryToolRequest $request, LaboratoryTool $tool)
    {
        $targetStatus = ResourceStatus::from($request->input('status'));

        if ($tool->status !== $targetStatus && ! $tool->status->canTransitionTo($targetStatus)) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Status cannot change from {$tool->status->label()} to {$targetStatus->label()}.");
        }

        $previousStatus = $tool->status;

        $tool->update([
            ...$request->validated(),
            'updated_by' => $request->user()->id,
            'published_at' => $targetStatus === ResourceStatus::PUBLISHED && ! $tool->published_at ? now() : $tool->published_at,
        ]);

        $tool->categories()->sync($request->input('categories', []));
        $tool->tags()->sync($request->input('tags', []));

        app(AuditLogService::class)->logWithSubject('tools.updated', $tool, $tool->getChanges(), $request);

        if ($previousStatus !== ResourceStatus::PUBLISHED && $targetStatus === ResourceStatus::PUBLISHED) {
            app(AuditLogService::class)->logWithSubject('tools.published', $tool, ['status' => $targetStatus->value], $request);
        }

        if ($targetStatus === ResourceStatus::ARCHIVED) {
            app(AuditLogService::class)->logWithSubject('tools.archived', $tool, ['status' => $targetStatus->value], $request);
        }

        return redirect()->route('admin.tools.edit', $tool)->with('success', 'Tool updated successfully.');
    }

    public function publish(Request $request, LaboratoryTool $tool)
    {
        if ($tool->status !== ResourceStatus::PUBLISHED && ! $tool->status->canTransitionTo(ResourceStatus::PUBLISHED)) {
            return redirect()->back()->with('error', "Cannot publish from {$tool->status->label()}.");
        }

        $tool->update([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => $tool->published_at ?? now(),
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('tools.published', $tool, ['status' => ResourceStatus::PUBLISHED->value], $request);

        return redirect()->back()->with('success', 'Tool published successfully.');
    }

    public function archive(Request $request, LaboratoryTool $tool)
    {
        if ($tool->status !== ResourceStatus::ARCHIVED && ! $tool->status->canTransitionTo(ResourceStatus::ARCHIVED)) {
            return redirect()->back()->with('error', "Cannot archive from {$tool->status->label()}.");
        }

        $tool->update([
            'status' => ResourceStatus::ARCHIVED,
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('tools.archived', $tool, ['status' => ResourceStatus::ARCHIVED->value], $request);

        return redirect()->back()->with('success', 'Tool archived successfully.');
    }

    public function destroy(Request $request, LaboratoryTool $tool)
    {
        $changes = ['name' => $tool->name, 'slug' => $tool->slug];
        $tool->delete();

        app(AuditLogService::class)->logWithSubject('tools.deleted', $tool, $changes, $request);

        return $this->redirectToListing('admin.tools.index')->with('success', 'Tool deleted successfully.');
    }
}
