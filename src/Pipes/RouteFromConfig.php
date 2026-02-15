<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Given the message name, route the message to the queue defined in config.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class RouteFromConfig implements RoutingPipe
{
    public function handle(MessageFlowOut $messageFlowOut, Closure $next): mixed
    {
        if ($messageFlowOut->queue_connection !== null && $messageFlowOut->queue_name !== null) {
            // If the queue and connection are already chosen, then skip this pipe.

            return $next($messageFlowOut);
        }

        $name = $messageFlowOut->name;

        $queueConnection = config(
            'message-flow.out.name-mappings.' . $name . '.queue-connection',
            config('message-flow.out.fallback-mapping.queue-connection')
        );

        $queueName = config(
            'message-flow.out.name-mappings.' . $name . '.queue-name',
            config('message-flow.out.fallback-mapping.queue-name')
        );

        $messageFlowOut->queue_connection = $queueConnection;
        $messageFlowOut->queue_name = $queueName;

        $messageFlowOut->save();

        return $next($messageFlowOut);
    }
}
