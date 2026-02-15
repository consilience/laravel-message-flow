<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Log that the routing pipeline has started.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Illuminate\Support\Facades\Log;

class LogStartRoutingPipeline implements RoutingPipe
{
    public function handle(MessageFlowOut $messageFlowOut, Closure $next): mixed
    {
        Log::info('Running routing pipeline', [
            'messageFlowOutUuid' => $messageFlowOut->uuid,
            'status' => $messageFlowOut->status,
        ]);

        return $next($messageFlowOut);
    }
}
