<?php

namespace Consilience\Laravel\MessageFlow\Observers;

/**
 * This observer watches for "new" inbound messages in the MessageFlowIn
 * model, and dispatches messages to acknowledge the inbound message.
 */

use Consilience\Laravel\MessageFlow\Messages\AckMessage;
use Consilience\Laravel\MessageFlow\Models\MessageFlowIn;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class AckResponseObserver
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
        if ($messageFlowIn->isDirty('status') && $messageFlowIn->isNew()) {
            // Becomes "new" status from any other status.

            $this->handle($messageFlowIn);
        }
    }

    protected function handle(MessageFlowIn $messageFlowIn): void
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
