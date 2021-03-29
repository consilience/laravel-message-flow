<?php

namespace Consilience\Laravel\MessageFlow\Console\Commands;

use Consilience\Laravel\MessageFlow\Jobs\RoutingPipeline;
use Consilience\Laravel\MessageFlow\Models\MessageFlowIn;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class ListMessages extends Command
{
    const DIRECTION_INBOUND = 'inbound';
    const DIRECTION_OUTBOUND = 'outbound';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'message-flow:list-messages
        {--direction=outbound : The direction of the flow [inbound|outbound]}
        {--status=* : The statuses to view}
        {--process : Process any records taht are still waiting to be handled}
        {--uuid=* : Match a single message}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List messages currently stored';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $direction = $this->option('direction');
        $statuses = $this->option('status');
        $process = $this->option('process');
        $uuids = $this->option('uuid');

        // Validate direction, accepting any abbreviation.

        if (strpos(static::DIRECTION_INBOUND, strtolower($direction)) === 0) {
            $direction = static::DIRECTION_INBOUND;
            $query = MessageFlowIn::query();
        } elseif (strpos(static::DIRECTION_OUTBOUND, strtolower($direction)) === 0) {
            $direction = static::DIRECTION_OUTBOUND;
            $query = MessageFlowOut::query();
        } else {
            $this->error(sprintf('Invalid direction "%s"', $direction));

            return 1;
        }

        // TODO: Allow columns to be specified when running.

        $headers = [
            'UUID',
            'Status',
            'Name',
            'Created',
        ];

        $columns = [
            'uuid',
            'status',
            'name',
            'created_at',
        ];

        if ($this->getOutput()->isVerbose()) {
            $headers[] = 'Payload';
            $columns[] = 'payload';
        }

        if ($process) {
            $headers[] = 'Processed';
            $columns[] = DB::raw("'---' as processed");
        }

        $rows = $query->select($columns)
            ->orderBy('created_at')
            ->when($statuses, function ($query, $statuses) {
                return $query->whereIn('status', $statuses);
            })
            ->when($uuids, function ($query, $uuids) {
                return $query->whereIn('uuid', $uuids);
            })
            ->get()
            ->map(function ($item) {
                if ($item->payload !== null) {
                    $item->payload = json_encode($item->payload, JSON_PRETTY_PRINT);
                }

                return $item;
            })
            ->map(function ($item) use ($process) {
                if ($process && $item instanceof MessageFlowOut && $item->status === MessageFlowOut::STATUS_NEW) {
                    dispatch(new RoutingPipeline($item));

                    $item->processed = 'dispatched';
                }

                return $item;
            });

        $this->table($headers, $rows);

        return 0;
    }
}
