<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Log the final state of the outbound message at the end of the routing pipeline.
 */

use Closure;
use Illuminate\Support\Facades\Log;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class LogFinishRoutingPipeline implements RoutingPipe
{
    /**
     * @inheritDoc
     */
    public function handle(MessageFlowOut $messageFlowOut, Closure $next)
    {
        Log::debug('Routing pipeline complete for MessageFlowOut', [
            'uuid' => $messageFlowOut->uuid,
            'status' => $messageFlowOut->status,
            'isDeleted' => ! $messageFlowOut->exists,
        ]);

        return  $next($messageFlowOut);
    }
}
