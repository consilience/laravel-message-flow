<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Delete a message that is marked as complete.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class DeleteCompleteMessage implements RoutingPipe
{
    public function handle(MessageFlowOut $messageFlowOut, Closure $next): mixed
    {
        if ($messageFlowOut->status === MessageFlowOut::STATUS_COMPLETE) {
            $messageFlowOut->delete();
        }

        return $next($messageFlowOut);
    }
}
