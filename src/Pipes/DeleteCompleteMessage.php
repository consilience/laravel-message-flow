<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Delete a message that is marked as complete.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;
use Consilience\Laravel\MessageFlow\Jobs\ReceiveMessage;
use Consilience\Laravel\MessageFlow\Jobs\SendMessage;

class DeleteCompleteMessage implements RoutingPipe
{
    public function handle(MessageFlowOut $messageFlowOut, Closure $next)
    {
        if ($messageFlowOut->status === MessageFlowOut::STATUS_COMPLETE) {
            $messageFlowOut->delete();
        }

        return  $next($messageFlowOut);
    }
}
