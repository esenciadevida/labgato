<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $action = $request->query('action');
        $resourceType = $request->query('resource_type');
        $from = $request->query('from');
        $to = $request->query('to');

        $logs = AuditLog::query()
            ->with('user')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('action', 'like', "%{$search}%")
                        ->orWhereHas('user', function ($q2) use ($search) {
                            $q2->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($action !== null && $action !== '', fn ($query) => $query->where('action', $action))
            ->when($resourceType !== null && $resourceType !== '', fn ($query) => $query->where('resource_type', $resourceType))
            ->when($from !== null && $from !== '', fn ($query) => $query->whereDate('created_at', '>=', $from))
            ->when($to !== null && $to !== '', fn ($query) => $query->whereDate('created_at', '<=', $to))
            ->orderByDesc('created_at')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        $actions = AuditLog::select('action')->distinct()->orderBy('action')->pluck('action');
        $resourceTypes = AuditLog::select('resource_type')->distinct()->whereNotNull('resource_type')->orderBy('resource_type')->pluck('resource_type');

        return view('admin.audit-logs.index', compact('logs', 'search', 'action', 'resourceType', 'from', 'to', 'actions', 'resourceTypes'));
    }

    public function show(AuditLog $auditLog)
    {
        $log = $auditLog->load('user');

        return view('admin.audit-logs.show', compact('log'));
    }

    public function export(Request $request): StreamedResponse
    {
        $search = trim((string) $request->query('search'));
        $action = $request->query('action');
        $resourceType = $request->query('resource_type');
        $from = $request->query('from');
        $to = $request->query('to');

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="audit-logs-'.now()->format('Y-m-d-His').'.csv"',
        ];

        return new StreamedResponse(function () use ($search, $action, $resourceType, $from, $to) {
            $out = fopen('php://output', 'w');

            fputcsv($out, [
                'created_at', 'user', 'email', 'action', 'ip_address', 'user_agent', 'resource_type', 'resource_id', 'changes',
            ]);

            AuditLog::query()
                ->with('user')
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('action', 'like', "%{$search}%")
                            ->orWhereHas('user', function ($q2) use ($search) {
                                $q2->where('name', 'like', "%{$search}%");
                            });
                    });
                })
                ->when($action !== null && $action !== '', fn ($query) => $query->where('action', $action))
                ->when($resourceType !== null && $resourceType !== '', fn ($query) => $query->where('resource_type', $resourceType))
                ->when($from !== null && $from !== '', fn ($query) => $query->whereDate('created_at', '>=', $from))
                ->when($to !== null && $to !== '', fn ($query) => $query->whereDate('created_at', '<=', $to))
                ->orderByDesc('created_at')
                ->chunk(500, function ($logs) use ($out) {
                    foreach ($logs as $log) {
                        fputcsv($out, [
                            $log->created_at?->format('Y-m-d H:i:s'),
                            $log->user?->name ?? 'System',
                            $log->user?->email ?? '',
                            $log->action,
                            $log->ip_address ?? '',
                            $log->user_agent ?? '',
                            $log->resource_type ?? '',
                            $log->resource_id ?? '',
                            json_encode($log->changes, JSON_UNESCAPED_SLASHES) ?: '',
                        ]);
                    }
                });

            fclose($out);
        }, 200, $headers);
    }
}
