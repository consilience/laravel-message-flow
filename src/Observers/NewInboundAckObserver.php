<?php

namespace Consilience\Laravel\MessageFlow\Observers;

/**
 * Handle an inbound ack message, returned buy the receiver.
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
    /**
     * Handle the MessageFlowIn "created" event.
     *
     * @param  MessageFlowIn  $messageFlowIn
     * @return void
     */
    public function created(MessageFlowIn $messageFlowIn)
    {
        if ($messageFlowIn->isNew()) {
            // Landed with the "new" status.

            $this->handle($messageFlowIn);
        }
    }

    /**
     * Handle the MessageFlowIn "updated" event.
     *
     * @param  MessageFlowIn  $messageFlowIn
     * @return void
     */
    public function updated(MessageFlowIn $messageFlowIn)
    {
        if ($messageFlowIn->status !== $messageFlowIn->getOriginal('status')
            && $messageFlowIn->isNew()) {
            // Becomes "new" status from ny other status.

            $this->handle($messageFlowIn);
        }
    }

    /**
     * @param MessageFlowIn $messageFlowIn
     * @return void
     */
    protected function handle(MessageFlowIn $messageFlowIn)
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
