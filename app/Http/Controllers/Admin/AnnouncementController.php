<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAnnouncementRequest;
use App\Http\Requests\Admin\UpdateAnnouncementRequest;
use App\Models\Announcement;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $announcements = Announcement::query()
            ->with('creator')
            ->orderByDesc('created_at')
            ->paginate((int) config('labgoto.pagination.per_page', 20))
            ->withQueryString();

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(StoreAnnouncementRequest $request)
    {
        $announcement = Announcement::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'is_dismissible' => $request->boolean('is_dismissible', true),
            'created_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('announcements.created', $announcement, $announcement->toArray(), $request);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement created successfully.');
    }

    public function edit(Announcement $announcement)
    {
        return view('admin.announcements.edit', compact('announcement'));
    }

    public function update(UpdateAnnouncementRequest $request, Announcement $announcement)
    {
        $announcement->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'is_dismissible' => $request->boolean('is_dismissible', true),
        ]);

        app(AuditLogService::class)->logWithSubject('announcements.updated', $announcement, $announcement->getChanges(), $request);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement updated successfully.');
    }

    public function destroy(Request $request, Announcement $announcement)
    {
        $changes = ['message' => $announcement->message];
        $announcement->delete();

        app(AuditLogService::class)->logWithSubject('announcements.deleted', $announcement, $changes, $request);

        return redirect()->route('admin.announcements.index')->with('success', 'Announcement deleted successfully.');
    }
}
