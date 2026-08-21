<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->paginate(20)
            ->withQueryString();

        return view('admin.administrators.index', compact('administrators', 'search'));
    }

    public function create(Request $request)
    {
        $this->authorizeSuperAdmin($request);

        $roles = \App\Models\Role::orderBy('name')->get();

        return view('admin.administrators.create', compact('roles'));
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorizeSuperAdmin($request);

        $data = $request->validated();
        $roleId = $data['role_id'];
        unset($data['role_id']);

        $data['user_type'] = 'admin';

        $administrator = User::create($data);
        $administrator->roles()->attach($roleId);

        app(AuditLogService::class)->logWithSubject('administrators.created', $administrator, $administrator->toArray(), $request);

        return redirect()->route('admin.administrators.index')->with('success', 'Administrator created successfully.');
    }

    public function edit(Request $request, User $administrator)
    {
        $this->authorizeSuperAdmin($request);

        $roles = \App\Models\Role::orderBy('name')->get();

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
        }

        $data['user_type'] = 'admin';

        $administrator->update($data);

        if ($roleId) {
            $administrator->roles()->sync([$roleId]);
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
            return redirect()->route('admin.administrators.index')->with('error', 'You cannot deactivate yourself.');
        }

        if ($administrator->isSuperAdministrator()) {
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

        $this->invalidateUserSessions($administrator);
        $administrator->update(['is_active' => false]);

        app(AuditLogService::class)->logWithSubject('administrators.deactivated', $administrator, ['is_active' => false], $request);

        return redirect()->route('admin.administrators.index')->with('success', 'Administrator deactivated and logged out immediately.');
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
