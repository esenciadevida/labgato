<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterpretationResource;
use App\Models\KnowledgeResource;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $newState = ! $user->is_active;
        $user->update(['is_active' => $newState]);

        // Instant session kill: delete all sessions for this user
        $this->invalidateUserSessions($user);

        app(AuditLogService::class)->logWithSubject(
            $newState ? 'users.activated' : 'users.deactivated',
            $user,
            ['is_active' => $newState],
            $request
        );

        $message = $newState ? 'User activated successfully.' : 'User deactivated and logged out immediately.';

        return redirect()->route('admin.users.index')->with('success', $message);
    }

    public function destroy(Request $request, User $user)
    {
        abort_unless($user->isRegularUser(), 404);

        if (! $request->user()?->isSuperAdministrator()) {
            abort(403, 'Only Super Administrators can delete users.');
        }

        // Prevent self-deletion
        if ($request->user()->id === $user->id) {
            return redirect()->route('admin.users.index')->with('error', 'You cannot delete your own account.');
        }

        // Instant session kill before deletion
        $this->invalidateUserSessions($user);

        // Nullify foreign keys that reference this user
        DB::table('sops')->where('contributed_by', $user->id)->update(['contributed_by' => null]);
        DB::table('knowledge_resources')->where('contributed_by', $user->id)->update(['contributed_by' => null]);
        DB::table('interpretation_resources')->where('contributed_by', $user->id)->update(['contributed_by' => null]);

        // Hard delete
        $userName = $user->name;
        $userEmail = $user->email;
        $user->delete();

        app(AuditLogService::class)->log(
            action: 'users.deleted',
            resourceType: User::class,
            resourceId: null,
            changes: ['name' => $userName, 'email' => $userEmail],
            userId: $request->user()->id,
        );

        return redirect()->route('admin.users.index')->with('success', "User \"{$userName}\" has been permanently deleted.");
    }

    protected function invalidateUserSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)
                ->delete();
        }
    }
}
