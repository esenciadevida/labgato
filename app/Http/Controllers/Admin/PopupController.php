<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePopupRequest;
use App\Http\Requests\Admin\UpdatePopupRequest;
use App\Models\Popup;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class PopupController extends Controller
{
    public function index(Request $request)
    {
        $popups = Popup::query()
            ->with('creator')
            ->orderByDesc('created_at')
            ->paginate((int) config('labgoto.pagination.per_page', 20))
            ->withQueryString();

        return view('admin.popups.index', compact('popups'));
    }

    public function create()
    {
        return view('admin.popups.create');
    }

    public function store(StorePopupRequest $request)
    {
        $popup = Popup::create([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'is_dismissible' => $request->boolean('is_dismissible', true),
            'delay_seconds' => $request->input('delay_seconds', 0),
            'created_by' => $request->user()->id,
        ]);

        app(AuditLogService::class)->logWithSubject('popups.created', $popup, $popup->toArray(), $request);

        return redirect()->route('admin.popups.index')->with('success', 'Pop-up created successfully.');
    }

    public function edit(Popup $popup)
    {
        return view('admin.popups.edit', compact('popup'));
    }

    public function update(UpdatePopupRequest $request, Popup $popup)
    {
        $popup->update([
            ...$request->validated(),
            'is_active' => $request->boolean('is_active'),
            'is_dismissible' => $request->boolean('is_dismissible', true),
            'delay_seconds' => $request->input('delay_seconds', 0),
        ]);

        app(AuditLogService::class)->logWithSubject('popups.updated', $popup, $popup->getChanges(), $request);

        return redirect()->route('admin.popups.index')->with('success', 'Pop-up updated successfully.');
    }

    public function destroy(Request $request, Popup $popup)
    {
        $changes = ['title' => $popup->title];
        $popup->delete();

        app(AuditLogService::class)->logWithSubject('popups.deleted', $popup, $changes, $request);

        return redirect()->route('admin.popups.index')->with('success', 'Pop-up deleted successfully.');
    }
}
