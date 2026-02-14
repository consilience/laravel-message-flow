<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Log the final state of the outbound message at the end of the routing pipeline.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Illuminate\Support\Facades\Log;

class LogFinishRoutingPipeline implements RoutingPipe
{
    public function handle(MessageFlowOut $messageFlowOut, Closure $next): mixed
    {
        Log::debug('Routing pipeline complete for MessageFlowOut', [
            'uuid' => $messageFlowOut->uuid,
            'status' => $messageFlowOut->status,
            'isDeleted' => ! $messageFlowOut->exists,
        ]);

        return $next($messageFlowOut);
    }
}
