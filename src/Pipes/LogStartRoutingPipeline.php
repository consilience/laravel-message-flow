<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Log that the routing pipeline has started.
 */

use Closure;
use Illuminate\Support\Facades\Log;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class LogStartRoutingPipeline implements RoutingPipe
{
    /**
     * @inheritDoc
     */
    public function handle(MessageFlowOut $messageFlowOut, Closure $next)
    {
        Log::info('Running routing pipeline', [
            'messageFlowOutUuid' => $messageFlowOut->uuid,
            'status' => $messageFlowOut->status,
        ]);

        return  $next($messageFlowOut);
    }
}
