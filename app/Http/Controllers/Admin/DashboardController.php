<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $adminCount = User::whereHas('roles', function ($query) {
            $query->where('is_system_role', true);
        })->count();

        $roleCount = Role::count();
        $permissionCount = Permission::count();
        $recentActivity = AuditLog::latest()->take(5)->get();

        return view('admin.dashboard', compact('adminCount', 'roleCount', 'permissionCount', 'recentActivity'));
    }
}
