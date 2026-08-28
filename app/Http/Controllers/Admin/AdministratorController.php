<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InviteAdministratorRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Mail\AdminInvitationEmail;
use App\Models\AdminInvitation;
use App\Models\Role;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\PermissionCacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class AdministratorController extends Controller
{
    private function authorizeSuperAdmin(Request $request): void
    {
        if (! $request->user()?->isSuperAdministrator()) {
            abort(403, 'Only Super Administrators can manage administrators.');
        }
    }

    public function index(Request $request)
    {
        $this->authorizeSuperAdmin($request);

        $search = trim((string) $request->query('search'));

        $administrators = User::administrators()
            ->with('roles')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(config('labgoto.pagination.per_page'))
            ->withQueryString();

        $pendingInvitations = AdminInvitation::with('role')
            ->whereNull('accepted_at')
            ->orderBy('created_at')
            ->get();

        return view('admin.administrators.index', compact('administrators', 'search', 'pendingInvitations'));
    }

    public function create(Request $request)
    {
        $this->authorizeSuperAdmin($request);

        $roles = Role::orderBy('name')->get();

        return view('admin.administrators.create', compact('roles'));
    }

    public function store(InviteAdministratorRequest $request)
    {
        $this->authorizeSuperAdmin($request);

        $data = $request->validated();

        [$invitation, $plainToken] = AdminInvitation::issue(
            $data['name'],
            $data['email'],
            (int) $data['role_id'],
            $request->user()->id
        );

        Mail::to($invitation->email)->queue(new AdminInvitationEmail(
            $invitation,
            route('admin.invitation.show', ['token' => $plainToken])
        ));

        app(AuditLogService::class)->logWithSubject('administrators.invited', $invitation, [
            'name' => $invitation->name,
            'email' => $invitation->email,
            'role_id' => $invitation->role_id,
        ], $request);

        return redirect()->route('admin.administrators.index')
            ->with('success', "Invitation sent to {$invitation->email}. They will set up their own password via the email link.");
    }

    public function resend(Request $request, AdminInvitation $invitation)
    {
        $this->authorizeSuperAdmin($request);

        if ($invitation->accepted_at) {
            return redirect()->route('admin.administrators.index')->with('error', 'This invitation was already accepted.');
        }

        [$invitation, $plainToken] = AdminInvitation::issue(
            $invitation->name,
            $invitation->email,
            $invitation->role_id,
            $request->user()->id
        );

        Mail::to($invitation->email)->queue(new AdminInvitationEmail(
            $invitation,
            route('admin.invitation.show', ['token' => $plainToken]),
            isResend: true
        ));

        app(AuditLogService::class)->logWithSubject('administrators.invitation_resent', $invitation, [
            'email' => $invitation->email,
        ], $request);

        return redirect()->route('admin.administrators.index')
            ->with('success', "Invitation resent to {$invitation->email}.");
    }

    public function revoke(Request $request, AdminInvitation $invitation)
    {
        $this->authorizeSuperAdmin($request);

        app(AuditLogService::class)->logWithSubject('administrators.invitation_revoked', $invitation, [
            'email' => $invitation->email,
        ], $request);

        $invitation->delete();

        return redirect()->route('admin.administrators.index')
            ->with('success', 'Invitation revoked.');
    }

    public function edit(Request $request, User $administrator)
    {
        $this->authorizeSuperAdmin($request);

        $roles = Role::orderBy('name')->get();

        return view('admin.administrators.edit', compact('administrator', 'roles'));
    }

    public function update(UpdateUserRequest $request, User $administrator)
    {
        $this->authorizeSuperAdmin($request);

        $data = $request->validated();
        $roleId = $data['role_id'] ?? null;
        unset($data['role_id']);

        if (empty($data['password'])) {
            unset($data['password'], $data['password_confirmation']);
        } else {
            $data['password_set_at'] = now();
        }

        $data['user_type'] = 'admin';

        $administrator->update($data);

        if ($roleId) {
            $administrator->roles()->sync([$roleId]);
            app(PermissionCacheService::class)->invalidateUser($administrator->id);
        }

        app(AuditLogService::class)->logWithSubject('administrators.updated', $administrator, $administrator->getChanges(), $request);

        return redirect()->route('admin.administrators.index')->with('success', 'Administrator updated successfully.');
    }

    public function toggleActive(Request $request, User $administrator)
    {
        $this->authorizeSuperAdmin($request);

        $current = $request->user();

        if ($administrator->id === $current->id) {
            return redirect()->route('admin.administrators.index')->with('error', 'You cannot change your own status.');
        }

        if ($administrator->isSuperAdministrator() && $administrator->is_active) {
            $activeSuperAdmins = User::administrators()
                ->whereHas('roles', function ($query) {
                    $query->where('slug', 'super-administrator');
                })
                ->where('is_active', true)
                ->count();

            if ($activeSuperAdmins <= 1) {
                return redirect()->route('admin.administrators.index')->with('error', 'Cannot deactivate the last active Super Administrator.');
            }
        }

        $newState = ! $administrator->is_active;
        $administrator->update(['is_active' => $newState]);
        app(PermissionCacheService::class)->invalidateUser($administrator->id);

        if (! $newState) {
            $this->invalidateUserSessions($administrator);
        }

        app(AuditLogService::class)->logWithSubject(
            $newState ? 'administrators.activated' : 'administrators.deactivated',
            $administrator,
            ['is_active' => $newState],
            $request
        );

        $message = $newState ? 'Administrator activated successfully.' : 'Administrator deactivated and logged out immediately.';

        return redirect()->route('admin.administrators.index')->with('success', $message);
    }

    public function destroy(Request $request, User $administrator)
    {
        $this->authorizeSuperAdmin($request);

        $current = $request->user();

        if ($administrator->id === $current->id) {
            return redirect()->route('admin.administrators.index')->with('error', 'You cannot delete yourself.');
        }

        if ($administrator->isSuperAdministrator() && $administrator->is_active) {
            $activeSuperAdmins = User::administrators()
                ->whereHas('roles', function ($query) {
                    $query->where('slug', 'super-administrator');
                })
                ->where('is_active', true)
                ->count();

            if ($activeSuperAdmins <= 1) {
                return redirect()->route('admin.administrators.index')->with('error', 'Cannot delete the last active Super Administrator.');
            }
        }

        app(AuditLogService::class)->logWithSubject('administrators.deleted', $administrator, [
            'name' => $administrator->name,
            'email' => $administrator->email,
            'roles' => $administrator->roles->pluck('name')->all(),
        ], $request);

        $this->invalidateUserSessions($administrator);
        $administrator->roles()->detach();
        $administrator->delete();

        return redirect()->route('admin.administrators.index')
            ->with('success', "{$administrator->email} has been permanently deleted.");
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
