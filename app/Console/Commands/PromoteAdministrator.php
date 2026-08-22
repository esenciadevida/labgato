<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;

class PromoteAdministrator extends Command
{
    protected $signature = 'admin:promote
                            {email : The email address of the administrator to promote}
                            {--force : Promote without asking for confirmation}';

    protected $description = 'Promote an administrator to Super Administrator';

    public function handle(): int
    {
        $email = strtolower(trim((string) $this->argument('email')));

        $administrator = User::administrators()->where('email', $email)->first();

        if (! $administrator) {
            $this->error("No administrator found with the email [{$email}].");

            return self::FAILURE;
        }

        $superRole = Role::where('slug', 'super-administrator')->first();

        if (! $superRole) {
            $this->error('The [super-administrator] role does not exist. Seed roles first.');

            return self::FAILURE;
        }

        if ($administrator->isSuperAdministrator()) {
            $this->info("[{$email}] is already a Super Administrator. Nothing to do.");

            return self::SUCCESS;
        }

        if (! $administrator->is_active) {
            $administrator->update(['is_active' => true]);
            $this->line("Reactivated account [{$email}].");
        }

        if (! $this->option('force')) {
            if (! $this->confirm("Promote [{$email}] to Super Administrator?")) {
                $this->info('Aborted.');

                return self::SUCCESS;
            }
        }

        $administrator->user_type = 'admin';
        $administrator->save();
        $administrator->roles()->syncWithoutDetaching([$superRole->id]);

        $this->info("[{$email}] is now a Super Administrator.");

        return self::SUCCESS;
    }
}
