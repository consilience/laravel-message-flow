<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Given the message name, route the message to the queue defined in config.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;

class RouteFromConfig implements RoutingPipe
{
    public function handle(MessageFlowOut $messageFlowOut, Closure $next)
    {
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

        return  $next($messageFlowOut);
    }
}
