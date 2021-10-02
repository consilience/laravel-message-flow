<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Delete a message that is marked as complete.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;

class DeleteCompleteMessage implements RoutingPipe
{
    /**
     * @inheritDoc
     */
    public function handle(MessageFlowOut $messageFlowOut, Closure $next)
    {
        if ($messageFlowOut->status === MessageFlowOut::STATUS_COMPLETE) {
            $messageFlowOut->delete();
        }

        return  $next($messageFlowOut);
    }
}
