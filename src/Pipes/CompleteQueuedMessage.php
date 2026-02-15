<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * If a message has been successfully queued, then mark it as
 * complete, not waiting for any acknowledgements.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class CompleteQueuedMessage implements RoutingPipe
{
    public function handle(MessageFlowOut $messageFlowOut, Closure $next): mixed
    {
        // FIXME:check config to see which which message names need this action.
        // Messages requiring an ack should remained in the `queued` state.
        // Messages that *are* acks, should move to `complete` immediately.
        // Maybe everything goes to `complete` unless in an exception list?

        if ($messageFlowOut->status === MessageFlowOut::STATUS_QUEUED) {
            $messageFlowOut->status = MessageFlowOut::STATUS_COMPLETE;
            $messageFlowOut->save();
        }

        return $next($messageFlowOut);
    }
}
