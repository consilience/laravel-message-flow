<?php

namespace Consilience\Laravel\MessageFlow\Console\Commands;

use Consilience\Laravel\MessageFlow\Models\MessageFlowIn;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class PurgeMessages extends Command
{
    public const DIRECTION_INBOUND = 'inbound';
    public const DIRECTION_OUTBOUND = 'outbound';
    public const DIRECTION_BOTH = 'both';

    protected $signature = 'message-flow:purge
        {--days=30 : Minimum age in days (combined with --hours)}
        {--hours=0 : Additional hours to add to the age threshold}
        {--direction=both : Which tables to purge [inbound|outbound|both]}
        {--status=* : Status(es) to purge (defaults to complete)}
        {--dry-run : Show what would be deleted without deleting}
    ';

    protected $description = 'Purge old completed messages from the cache tables';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $hours = (int) $this->option('hours');
        $direction = $this->option('direction');
        $statuses = $this->option('status');
        $dryRun = $this->option('dry-run');

        // Default to 'complete' if no statuses specified.

        if (empty($statuses)) {
            $statuses = [MessageFlowIn::STATUS_COMPLETE];
        }

        if ($days === 0 && $hours === 0) {
            $this->error('You must specify at least --days or --hours greater than zero.');

            return 1;
        }

        $cutoff = Carbon::now()->subDays($days)->subHours($hours);

        // Resolve direction, accepting abbreviations.

        $purgeInbound = false;
        $purgeOutbound = false;

        if (str_starts_with(static::DIRECTION_BOTH, strtolower($direction))) {
            $purgeInbound = true;
            $purgeOutbound = true;
        } elseif (str_starts_with(static::DIRECTION_INBOUND, strtolower($direction))) {
            $purgeInbound = true;
        } elseif (str_starts_with(static::DIRECTION_OUTBOUND, strtolower($direction))) {
            $purgeOutbound = true;
        } else {
            $this->error(sprintf('Invalid direction "%s"', $direction));

            return 1;
        }

        $this->info(sprintf(
            '%s messages updated before %s with status: %s',
            $dryRun ? 'Counting' : 'Purging',
            $cutoff->toDateTimeString(),
            implode(', ', $statuses),
        ));

        $totalDeleted = 0;

        if ($purgeInbound) {
            $totalDeleted += $this->purgeModel(MessageFlowIn::class, 'inbound', $statuses, $cutoff, $dryRun);
        }

        if ($purgeOutbound) {
            $totalDeleted += $this->purgeModel(MessageFlowOut::class, 'outbound', $statuses, $cutoff, $dryRun);
        }

        if ($dryRun) {
            $this->info(sprintf('Dry run complete. %d record(s) would be purged.', $totalDeleted));
        } else {
            $this->info(sprintf('Purge complete. %d record(s) deleted.', $totalDeleted));
        }

        return 0;
    }

    /**
     * @param class-string<\Illuminate\Database\Eloquent\Model> $modelClass
     * @param list<string> $statuses
     */
    protected function purgeModel(string $modelClass, string $label, array $statuses, Carbon $cutoff, bool $dryRun): int
    {
        $query = $modelClass::whereIn('status', $statuses)
            ->where('updated_at', '<=', $cutoff);

        $count = $query->count();

        $this->line(sprintf('  %s: %d record(s) %s', $label, $count, $dryRun ? 'found' : 'deleted'));

        if (! $dryRun && $count > 0) {
            $query->delete();
        }

        return $count;
    }
}
