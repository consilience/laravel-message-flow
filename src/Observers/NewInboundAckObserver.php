<?php

namespace Consilience\Laravel\MessageFlow\Observers;

/**
 * Handle an inbound ack message, returned by the receiver.
 *
 * The ack message is tied up to the original outbound message,
 * which is marked as `complete`.
 *
 * The outbound pipeline job is then dispatched to perform any
 * tidy-up needed, such as deleting the `complete` outbound
 * message.
 *
 * The inbound ack message is deleted once it has been processed.
 */

use Consilience\Laravel\MessageFlow\Jobs\RoutingPipeline;
use Consilience\Laravel\MessageFlow\Models\MessageFlowIn;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class NewInboundAckObserver
{
    public function created(MessageFlowIn $messageFlowIn): void
    {
        if ($messageFlowIn->isNew()) {
            // Landed with the "new" status.

            $this->handle($messageFlowIn);
        }
    }

    public function updated(MessageFlowIn $messageFlowIn): void
    {
        if ($messageFlowIn->status !== $messageFlowIn->getOriginal('status')
            && $messageFlowIn->isNew()) {
            // Becomes "new" status from any other status.

            $this->handle($messageFlowIn);
        }
    }

    protected function handle(MessageFlowIn $messageFlowIn): void
    {
        // TODO: Only match a known ack message name, so we know this
        // is an expected ack message.

        $originalMessageUuid = $messageFlowIn->getPayloadPath('originalMessageUuid');

        if (! $originalMessageUuid) {
            return;
        }

        // Only process a queued but incomplete outbound message.

        $originalMessage = MessageFlowOut::isQueued()->find($originalMessageUuid);

        if ($originalMessage) {
            // Mark as complete.

            $originalMessage->status = MessageFlowOut::STATUS_COMPLETE;
            $originalMessage->save();

            // Dispatch the routing pipeline to complete any stages for the
            // sent message.

            dispatch(new RoutingPipeline($originalMessage));
        }
    }
}
