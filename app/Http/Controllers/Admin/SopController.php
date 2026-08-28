<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ResourceStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSopRequest;
use App\Http\Requests\Admin\UpdateSopRequest;
use App\Models\Sop;
use App\Services\AuditLogService;
use App\Services\ContentImageService;
use App\Services\SopVersionService;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index(Request $request)
    {
        $request->session()->put('intended_listing_url', $request->fullUrl());

        $search = trim((string) $request->query('search'));
        $status = trim((string) $request->query('status'));
        $categoryId = $request->query('category');

        $sops = Sop::query()
            ->with(['categories', 'createdBy'])
            ->when($search !== '', fn ($query) => $query->search($search))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->whereHas('categories', fn ($q) => $q->whereKey($categoryId)))
            ->orderByDesc('created_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        return view('admin.sops.index', compact('sops', 'search', 'status', 'categoryId'));
    }

    public function create()
    {
        $statuses = ResourceStatus::cases();

        return view('admin.sops.create', compact('statuses'));
    }

    public function store(StoreSopRequest $request)
    {
        $data = collect($request->validated())
            ->except(['featured_image', 'featured_image_url', 'remove_featured_image'])
            ->all();
        $data['version'] = ($data['version'] ?? '') ?: '1.0';

        $featuredImage = trim((string) $request->validated('featured_image_url'));

        if ($request->hasFile('featured_image')) {
            $featuredImage = app(ContentImageService::class)->store($request->file('featured_image'));
        }

        if ($featuredImage !== '') {
            $data['featured_image'] = $featuredImage;
        }

        $sop = Sop::create([
            ...$data,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
            'published_at' => $request->input('status') === ResourceStatus::PUBLISHED->value ? now() : null,
        ]);

        $sop->categories()->sync($request->input('categories', []));
        $sop->tags()->sync($request->input('tags', []));

        app(ContentImageService::class)->syncAttachments((string) $sop->content, 'sop', $sop->id);

        app(SopVersionService::class)->createSnapshot($sop, 'Initial version');

        app(AuditLogService::class)->logWithSubject('sops.created', $sop, $sop->toArray(), $request);

        if ($request->input('save_action') === 'save_and_create') {
            return redirect()->route('admin.sops.create')->with('success', 'SOP created successfully. Add another.');
        }

        return redirect()->route('admin.sops.index')->with('success', 'SOP created successfully.');
    }

    public function edit(Sop $sop)
    {
        $statuses = ResourceStatus::cases();

        return view('admin.sops.edit', compact('sop', 'statuses'));
    }

    public function update(UpdateSopRequest $request, Sop $sop)
    {
        $targetStatus = ResourceStatus::from($request->input('status'));

        if ($sop->status !== $targetStatus && ! $sop->status->canTransitionTo($targetStatus)) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Status cannot change from {$sop->status->label()} to {$targetStatus->label()}.");
        }

        $previousStatus = $sop->status;

        $versionService = app(SopVersionService::class);
        $isNewVersion = $request->boolean('is_new_version');

        $data = collect($request->validated())
            ->except(['is_new_version', 'change_summary', 'featured_image', 'featured_image_url', 'remove_featured_image'])
            ->all();

        $changedFeatured = false;
        $newImage = trim((string) $request->validated('featured_image_url'));

        if ($request->hasFile('featured_image')) {
            $newImage = app(ContentImageService::class)->store($request->file('featured_image'));

            if ($sop->featured_image && $sop->featured_image !== $newImage) {
                app(ContentImageService::class)->delete($sop->featured_image);
            }

            $changedFeatured = true;
        } elseif ($newImage !== '' && $newImage !== $sop->featured_image) {
            if ($sop->featured_image) {
                app(ContentImageService::class)->delete($sop->featured_image);
            }

            $changedFeatured = true;
        } elseif ($request->boolean('remove_featured_image') && $sop->featured_image) {
            app(ContentImageService::class)->delete($sop->featured_image);
            $newImage = null;
            $changedFeatured = true;
        }

        if ($changedFeatured) {
            $data['featured_image'] = $newImage === '' ? null : $newImage;
        }

        if ($isNewVersion) {
            $newVersion = trim((string) ($data['version'] ?? ''));
            if ($newVersion === '' || $newVersion === $sop->version) {
                $newVersion = $versionService->suggestNextVersion($sop->version ?: '1.0');
            }
            $data['version'] = $newVersion;
        }

        $sop->update([
            ...$data,
            'updated_by' => $request->user()->id,
            'published_at' => $targetStatus === ResourceStatus::PUBLISHED && ! $sop->published_at ? now() : $sop->published_at,
        ]);

        $sop->categories()->sync($request->input('categories', []));
        $sop->tags()->sync($request->input('tags', []));

        app(ContentImageService::class)->syncAttachments((string) $sop->content, 'sop', $sop->id);

        if ($isNewVersion) {
            $versionService->createSnapshot($sop, $request->input('change_summary'));
        }

        app(AuditLogService::class)->logWithSubject('sops.updated', $sop, $sop->getChanges(), $request);

        if ($previousStatus !== ResourceStatus::PUBLISHED && $targetStatus === ResourceStatus::PUBLISHED) {
            app(AuditLogService::class)->logWithSubject('sops.published', $sop, ['status' => $targetStatus->value], $request);
        }

        if ($targetStatus === ResourceStatus::ARCHIVED) {
            app(AuditLogService::class)->logWithSubject('sops.archived', $sop, ['status' => $targetStatus->value], $request);
        }

        return redirect()->route('admin.sops.edit', $sop)->with('success', 'SOP updated successfully.');
    }

    public function publish(Request $request, Sop $sop)
    {
        if ($sop->status !== ResourceStatus::PUBLISHED && ! $sop->status->canTransitionTo(ResourceStatus::PUBLISHED)) {
            return redirect()->back()->with('error', "Cannot publish from {$sop->status->label()}.");
        }

        $sop->update([
            'status' => ResourceStatus::PUBLISHED,
            'published_at' => $sop->published_at ?? now(),
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('sops.published', $sop, ['status' => ResourceStatus::PUBLISHED->value], $request);

        return redirect()->back()->with('success', 'SOP published successfully.');
    }

    public function archive(Request $request, Sop $sop)
    {
        if ($sop->status !== ResourceStatus::ARCHIVED && ! $sop->status->canTransitionTo(ResourceStatus::ARCHIVED)) {
            return redirect()->back()->with('error', "Cannot archive from {$sop->status->label()}.");
        }

        $sop->update([
            'status' => ResourceStatus::ARCHIVED,
            'updated_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('sops.archived', $sop, ['status' => ResourceStatus::ARCHIVED->value], $request);

        return redirect()->back()->with('success', 'SOP archived successfully.');
    }

    public function destroy(Request $request, Sop $sop)
    {
        $changes = ['name' => $sop->name, 'slug' => $sop->slug];

        app(ContentImageService::class)->delete($sop->featured_image);
        $sop->delete();

        app(AuditLogService::class)->logWithSubject('sops.deleted', $sop, $changes, $request);

        return $this->redirectToListing('admin.sops.index')->with('success', 'SOP deleted successfully.');
    }
}
