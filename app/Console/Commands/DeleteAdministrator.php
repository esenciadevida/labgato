<?php

namespace App\Console\Commands;

use App\Models\AdminInvitation;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteAdministrator extends Command
{
    protected $signature = 'admin:delete
                            {email : The email address of the administrator to delete}
                            {--force : Delete without asking for confirmation}';

    protected $description = 'Permanently delete an administrator account by email (cannot delete yourself or the last active Super Administrator)';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        $administrator = User::administrators()->where('email', $email)->first();

        if (! $administrator) {
            $this->error("No administrator found with the email [{$email}].");

            return self::FAILURE;
        }

        $activeSuperAdmins = User::administrators()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'super-administrator'))
            ->where('is_active', true)
            ->count();

        if ($administrator->isSuperAdministrator() && $administrator->is_active && $activeSuperAdmins <= 1) {
            $this->error('Cannot delete the last active Super Administrator. Promote another Super Administrator first.');

            return self::FAILURE;
        }

        if (! $this->option('force')) {
            $this->warn('You are about to permanently delete this administrator:');
            $this->line("  Name:  {$administrator->name}");
            $this->line("  Email: {$administrator->email}");
            $this->line('  Roles: '.$administrator->roles->pluck('name')->join(', '));

            if (! $this->confirm('Type "yes" to confirm deletion. This cannot be undone.')) {
                $this->info('Aborted. Nothing was deleted.');

                return self::SUCCESS;
            }
        }

        DB::transaction(function () use ($administrator) {
            if (config('session.driver') === 'database') {
                DB::table(config('session.table', 'sessions'))
                    ->where('user_id', $administrator->id)
                    ->delete();
            }

            $administrator->roles()->detach();
            AdminInvitation::where('email', $administrator->email)->delete();
            $administrator->delete();
        });

        $remaining = User::administrators()
            ->whereHas('roles', fn ($query) => $query->where('slug', 'super-administrator'))
            ->pluck('email');

        $this->info("Deleted administrator [{$email}]. Their sessions were revoked.");

        if ($remaining->isNotEmpty()) {
            $this->line('Remaining Super Administrators: '.$remaining->implode(', '));
        } else {
            $this->warn('There is no Super Administrator left! Promote one with: php artisan admin:promote {email}');
        }

        return self::SUCCESS;
    }
}
