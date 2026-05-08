<?php

namespace App\Console\Commands;

use App\Models\BoostSubmission;
use App\Services\BoostService;
use Illuminate\Console\Command;

/**
 * Boost follow-up tracker. Runs hourly via scheduler.
 * Re-checks indexation status at 24h / 72h / 7d after each boost.
 */
class BoostFollowUp extends Command
{
    protected $signature = 'boost:followup {--limit=50}';
    protected $description = 'Check Google index status for recent BoostSubmissions at 24h/72h/7d marks.';

    public function handle(BoostService $boost): int
    {
        $limit = (int)$this->option('limit');

        // 24h slot — for boosts created 22-30h ago without inspection_24h yet.
        $this->runSlot($boost, 'inspection_24h', now()->subHours(30), now()->subHours(22), $limit);
        $this->runSlot($boost, 'inspection_72h', now()->subHours(80), now()->subHours(66), $limit);
        $this->runSlot($boost, 'inspection_7d', now()->subDays(8), now()->subDays(6), $limit);

        return 0;
    }

    protected function runSlot(BoostService $boost, string $slot, $createdAfter, $createdBefore, int $limit): void
    {
        $rows = BoostSubmission::whereNull($slot)
            ->whereBetween('created_at', [$createdAfter, $createdBefore])
            ->whereNotNull('connection_id')
            ->limit($limit)
            ->get();

        foreach ($rows as $sub) {
            try {
                $boost->followUpCheck($sub, $slot);
                $this->line("[{$slot}] {$sub->url} — done");
            } catch (\Throwable $e) {
                $this->error("[{$slot}] {$sub->url} — {$e->getMessage()}");
            }
        }
    }
}
