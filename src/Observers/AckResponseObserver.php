<?php

namespace Consilience\Laravel\MessageFlow\Observers;

/**
 * This observer watches for "new" inbound messages in the MessageFlowIn
 * model, and dispatches messages to acknowledge the inbound message.
 */

use Consilience\Laravel\MessageFlow\Models\MessageFlowIn;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class AckResponseObserver
{
    /**
     * Handle the MessageFlowIn "created" event.
     *
     * @param  \App\Models\MessageFlowIn  $messageFlowIn
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
     * @param  \App\Models\MessageFlowIn  $messageFlowIn
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
        // TODO: Check config - does this message name need an ack?
        // TODO: Check config - what is the ack message name?

        $ackName = sprintf('%s-ack', $messageFlowIn->name); // Fallback

        $ackMessage = new AckMessage($messageFlowIn->uuid, $messageFlowIn->name);

        MessageFlowOut::create([
            'name' => $ackName,
            'payload' => $ackMessage,
        ]);
    }
}
