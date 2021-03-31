<?php

namespace Consilience\Laravel\MessageFlow\Pipes;

/**
 * Send the message to the queue, setting the status as per result.
 */

use Closure;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;
use Consilience\Laravel\MessageFlow\Contracts\RoutingPipe;
use Consilience\Laravel\MessageFlow\Jobs\ReceiveMessage;
use Consilience\Laravel\MessageFlow\Jobs\SendMessage;
use Illuminate\Support\Facades\Log;
use Throwable;

class QueueMessage implements RoutingPipe
{
    /**
     * @inheritDoc
     */
    public function handle(MessageFlowOut $messageFlowOut, Closure $next)
    {
        // Skip if not in a state for dispatching to the queue.

        if ($messageFlowOut->status !== MessageFlowOut::STATUS_NEW && $messageFlowOut->status !== MessageFlowOut::STATUS_FAILED) {
            return $next($messageFlowOut);
        }

        // This is the job to pick the message up at the other end.
        // We handle the payload in its raw JSON encoded form until we
        // get it to the other side.

        $pendingJob = dispatch(new ReceiveMessage(
            $messageFlowOut->jsonPayload,
            $messageFlowOut->uuid,
            $messageFlowOut->name
        ));

        if ($messageFlowOut->queue_connection) {
            $pendingJob->onConnection($messageFlowOut->queue_connection);
        }

        if ($messageFlowOut->queue_name) {
            $pendingJob->onQueue($messageFlowOut->queue_name);
        }

        // Here force the job to dispatch immediately by destroying it.
        // If there are any problems due to config, then mark it as a fail.

        try {
            unset($pendingJob);

        } catch (Throwable $exception) {
            $messageFlowOut->status = MessageFlowOut::STATUS_FAILED;
            $messageFlowOut->save();

            Log::error('Failed to dispatch MessageFlow message to queue', [
                'uuid' => $messageFlowOut->uuid,
                'queueConnection' => $messageFlowOut->queue_connection,
                'queueName' => $messageFlowOut->queue_name,
                'errorMessage' => $exception->getMessage(),
            ]);

            throw($exception);
        }

        $messageFlowOut->status = MessageFlowOut::STATUS_COMPLETE;

        $messageFlowOut->save();

        return $next($messageFlowOut);
    }
}
