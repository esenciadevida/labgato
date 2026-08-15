<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkActionRequest;
use App\Services\Admin\BulkActionService;
use Illuminate\Http\RedirectResponse;

class BulkActionController extends Controller
{
    public function process(BulkActionRequest $request, string $type, BulkActionService $service): RedirectResponse
    {
        $action = $request->validated('action');
        $ids = $request->validated('ids');

        $count = match ($action) {
            'publish' => $service->publish($type, $ids, $request->user(), $request),
            'archive' => $service->archive($type, $ids, $request->user(), $request),
            'delete' => $service->delete($type, $ids, $request->user(), $request),
        };

        $pastTense = match ($action) {
            'publish' => 'published',
            'archive' => 'archived',
            'delete' => 'deleted',
        };

        $typeLabel = str($type)->replace('-', ' ')->plural($count);

        return redirect()->back()->with('success', "{$count} {$typeLabel} {$pastTense}.");
    }
}
