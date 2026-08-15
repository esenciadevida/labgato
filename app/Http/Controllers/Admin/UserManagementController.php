<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search'));
        $status = $request->query('status');

        $users = User::users()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($status === 'active', function ($query) {
                $query->where('is_active', true);
            })
            ->when($status === 'inactive', function ($query) {
                $query->where('is_active', false);
            })
            ->withCount([
                'sops as contributions_count' => fn ($query) => $query->where('status', '!=', 'rejected'),
            ])
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'status'));
    }

    public function edit(User $user)
    {
        abort_unless($user->isRegularUser(), 404);

        $contributions = $user->sops()
            ->select(['id', 'name', 'slug', 'status', 'updated_at'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        $knowledgeCount = KnowledgeResource::where('contributed_by', $user->id)->count();
        $interpretationCount = InterpretationResource::where('contributed_by', $user->id)->count();

        return view('admin.users.edit', compact('user', 'contributions', 'knowledgeCount', 'interpretationCount'));
    }

    public function toggleActive(Request $request, User $user)
    {
        abort_unless($user->isRegularUser(), 404);

        $user->update(['is_active' => ! $user->is_active]);

        app(AuditLogService::class)->logWithSubject(
            $user->is_active ? 'users.activated' : 'users.deactivated',
            $user,
            ['is_active' => $user->is_active],
            $request
        );

        return redirect()->route('admin.users.index')->with('success', 'User status updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($user->isRegularUser(), 404);

        if (! $request->user()?->isSuperAdministrator()) {
            abort(403, 'Only Super Administrators can delete users.');
        }

        $user->update(['is_active' => false]);

        app(AuditLogService::class)->logWithSubject('users.deactivated', $user, ['is_active' => false], $request);

        return redirect()->route('admin.users.index')->with('success', 'User deactivated successfully.');
    }
}
