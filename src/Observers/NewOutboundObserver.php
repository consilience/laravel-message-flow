<?php

namespace Consilience\Laravel\MessageFlow\Observers;

/**
 * This observer watches for "new" outbound messages in the MessageFlowOut
 * model, and dispatches jobs to push each through the routing pipeline.
 * 
 * The observer events must not change the message model, for risk
 * of an endless loop. Instead, event actions are all dispatched
 * to a separate job.
 */

use Consilience\Laravel\MessageFlow\Jobs\RoutingPipeline;
use Consilience\Laravel\MessageFlow\Models\MessageFlowOut;

class NewOutboundObserver
{
    /**
     * Handle the MessageFlowOut "created" event.
     *
     * @param  MessageFlowOut  $messageFlowOut
     * @return void
     */
    public function created(MessageFlowOut $messageFlowOut)
    {
        if ($messageFlowOut->isNew()) {
            // Landed with the "new" status, so is ready to be pushed on.

            dispatch(new RoutingPipeline($messageFlowOut));
        }
    }

    /**
     * Handle the MessageFlowOut "updated" event.
     *
     * @param  MessageFlowOut  $messageFlowOut
     * @return void
     */
    public function updated(MessageFlowOut $messageFlowOut)
    {
        // FIXME: Don't dispatch a job if no routing pipeline is defined in config.

        if ($messageFlowOut->isDirty('status') && $messageFlowOut->isNew()) {
                // Becomes "new" status from ny other status.

                dispatch(new RoutingPipeline($messageFlowOut));
            }
    }
}
