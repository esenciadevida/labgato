<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class AdminNotifier
{
    /**
     * Notify all active admin accounts that have the given permission.
     */
    public static function notifyAdminsWithPermission(string $permission, $notification): void
    {
        self::activeAdmins()
            ->filter(fn (User $user) => $user->hasPermission($permission))
            ->each->notify($notification);
    }

    /**
     * Notify all active Super Admin accounts.
     */
    public static function notifySuperAdmins($notification): void
    {
        self::activeAdmins()
            ->filter(fn (User $user) => $user->isSuperAdministrator())
            ->each->notify($notification);
    }

    /**
     * Query only active admin accounts.
     */
    private static function activeAdmins(): Collection
    {
        return User::query()
            ->where('user_type', 'admin')
            ->where('is_active', true)
            ->get();
    }
}
