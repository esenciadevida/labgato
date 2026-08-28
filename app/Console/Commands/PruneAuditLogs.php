<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneAuditLogs extends Command
{
    protected $signature = 'audit-logs:prune {--days= : Delete audit logs older than this many days.}';

    protected $description = 'Delete audit log entries older than the configured retention period';

    public function handle(): int
    {
        $days = (int) ($this->option('days')
            ?: config('labgoto.security.audit_retention_days', 365));

        if ($days < 7) {
            $this->error('Retention must be at least 7 days.');

            return Command::FAILURE;
        }

        $cutoff = now()->subDays($days);

        $query = AuditLog::query()->where('created_at', '<', $cutoff);

        $count = (clone $query)->count();

        $query->orderBy('id')->chunkById(1000, function ($logs) {
            $logs->each->delete();
        });

        $this->info("Pruned {$count} audit log entr".($count === 1 ? 'y' : 'ies')." older than {$days} days.");

        if ($count > 0) {
            Log::info('Audit log prune completed', [
                'pruned' => $count,
                'retention_days' => $days,
            ]);
        }

        return Command::SUCCESS;
    }
}